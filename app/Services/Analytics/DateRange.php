<?php

namespace App\Services\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DateRange
{
    public const TIMEZONE = 'Asia/Tashkent';
    public const MAX_DAYS = 365;

    public Carbon $start;
    public Carbon $end;
    public string $preset;

    public function __construct(Carbon $start, Carbon $end, string $preset = '30')
    {
        $tz = config('analytics.timezone', self::TIMEZONE);
        $this->start = $start->copy()->setTimezone($tz)->startOfDay();
        $this->end = $end->copy()->setTimezone($tz)->endOfDay();
        $this->preset = $preset;

        if ($this->end->lt($this->start)) {
            throw new InvalidArgumentException("Tugash sanasi boshlanish sanasidan oldin bo'lishi mumkin emas.");
        }

        if ($this->days() > self::MAX_DAYS) {
            throw new InvalidArgumentException('Maksimal tahlil davri ' . self::MAX_DAYS . ' kundan oshmasligi kerak.');
        }
    }

    /**
     * Preset bo'yicha yaratish: 7, 30, 90
     */
    public static function fromPreset(string $preset = '30', ?Carbon $referenceNow = null): self
    {
        $tz = config('analytics.timezone', self::TIMEZONE);
        $now = ($referenceNow ? $referenceNow->copy() : Carbon::now($tz))->setTimezone($tz);

        $days = in_array((int) $preset, [7, 30, 90], true) ? (int) $preset : 30;
        $normalizedPreset = (string) $days;

        $end = $now->copy()->endOfDay();
        $start = $now->copy()->subDays($days - 1)->startOfDay();

        return new self($start, $end, $normalizedPreset);
    }

    /**
     * Aniq sanalar bo'yicha yaratish (YYYY-MM-DD)
     */
    public static function fromDates(string $from, string $to, string $preset = 'custom'): self
    {
        $tz = config('analytics.timezone', self::TIMEZONE);
        $start = Carbon::createFromFormat('Y-m-d', $from, $tz)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $to, $tz)->endOfDay();

        return new self($start, $end, $preset);
    }

    /**
     * HTTP Request'dan validatsiya qilib DateRange ob'ektini olish
     */
    public static function fromRequest(Request $request): self
    {
        $period = (string) $request->query('period', '30');
        $from = $request->query('from');
        $to = $request->query('to');

        if (($period === 'custom' || ($from && $to)) && is_string($from) && is_string($to)) {
            $validated = $request->validate([
                'from' => ['required', 'date_format:Y-m-d'],
                'to'   => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            ]);

            $tz = config('analytics.timezone', self::TIMEZONE);
            $start = Carbon::createFromFormat('Y-m-d', $validated['from'], $tz)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $validated['to'], $tz)->endOfDay();

            if ($start->diffInDays($end) + 1 > self::MAX_DAYS) {
                $start = $end->copy()->subDays(self::MAX_DAYS - 1)->startOfDay();
            }

            return new self($start, $end, 'custom');
        }

        if (!in_array($period, ['7', '30', '90'], true)) {
            $period = '30';
        }

        return self::fromPreset($period);
    }

    /**
     * Davr ichidagi kunlar soni (inclusive, kamida 1 kun)
     */
    public function days(): int
    {
        return (int) $this->start->copy()->startOfDay()->diffInDays($this->end->copy()->startOfDay()) + 1;
    }

    /**
     * Oldingi teng uzunlikdagi davr (delta hisoblash uchun)
     */
    public function previousPeriod(): self
    {
        $days = $this->days();
        $prevEnd = $this->start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();

        return new self($prevStart, $prevEnd, 'prev_' . $this->preset);
    }

    public function startDate(): string
    {
        return $this->start->toDateString();
    }

    public function endDate(): string
    {
        return $this->end->toDateString();
    }

    /**
     * Davrdagi barcha sanalar ro'yxati ('Y-m-d')
     *
     * @return array<int, string>
     */
    public function dateStrings(): array
    {
        $dates = [];
        $cursor = $this->start->copy()->startOfDay();
        $endDay = $this->end->copy()->startOfDay();

        while ($cursor->lte($endDay)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $dates;
    }

    /**
     * Kesh kalitini davr bilan shakllantirish
     */
    public function cacheKey(string $metric): string
    {
        return sprintf(
            'analytics:%s:%s:%s:%s',
            $metric,
            $this->preset,
            $this->startDate(),
            $this->endDate()
        );
    }

    /**
     * Oldingi davrga nisbatan delta hisoblash.
     * Oldingi qiymat 0 va joriy > 0 bo'lsa: percent = null, label = "yangi" (cheksiz foiz emas!).
     *
     * @param  float|int  $current
     * @param  float|int  $previous
     * @param  bool       $invertColors  "Yomon bo'lib o'sadigan" metrikalar (shikoyat, xato) uchun true
     * @return array{current: float|int, previous: float|int, diff: float|int, percent: ?float, label: string, direction: string, is_positive: bool, inverted: bool}
     */
    public static function formatDelta($current, $previous, bool $invertColors = false): array
    {
        $diff = $current - $previous;

        if ((float) $previous == 0.0) {
            if ((float) $current > 0.0) {
                return [
                    'current'     => $current,
                    'previous'    => $previous,
                    'diff'        => $diff,
                    'percent'     => null,
                    'label'       => 'yangi',
                    'direction'   => 'new',
                    'is_positive' => !$invertColors,
                    'inverted'    => $invertColors,
                ];
            }

            return [
                'current'     => $current,
                'previous'    => $previous,
                'diff'        => 0,
                'percent'     => 0.0,
                'label'       => '0%',
                'direction'   => 'flat',
                'is_positive' => true,
                'inverted'    => $invertColors,
            ];
        }

        $percent = round((($current - $previous) / $previous) * 100, 1);
        $direction = $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'flat');
        $sign = $percent > 0 ? '+' : '';

        if ($direction === 'flat') {
            $isPositive = true;
        } else {
            $isPositive = $invertColors ? ($percent < 0) : ($percent > 0);
        }

        return [
            'current'     => $current,
            'previous'    => $previous,
            'diff'        => $diff,
            'percent'     => $percent,
            'label'       => $sign . $percent . '%',
            'direction'   => $direction,
            'is_positive' => $isPositive,
            'inverted'    => $invertColors,
        ];
    }

    public function toArray(): array
    {
        return [
            'preset'     => $this->preset,
            'from'       => $this->startDate(),
            'to'         => $this->endDate(),
            'days'       => $this->days(),
            'timezone'   => config('analytics.timezone', self::TIMEZONE),
        ];
    }
}
