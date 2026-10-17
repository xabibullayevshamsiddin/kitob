<?php

namespace App\Services\Analytics;

use App\Models\GlobalChatMessage;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CommunityMetrics
{
    protected string $timezone;

    public function __construct()
    {
        $this->timezone = config('analytics.timezone', DateRange::TIMEZONE);
    }

    /**
     * Jamiyat ko'rsatkichlari:
     * - Chat va guruh xabarlari soni
     * - Eng faol guruhlar
     * - Shikoyatlar navbati (ochiq soni, eng eskisining yoshi)
     * - Ban statistikasi
     * - Jonli efirlar statistikasi
     */
    public function getSummary(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('community_summary');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');
            $prevRange = $range->previousPeriod();
            $prevStartStr = $prevRange->start->format('Y-m-d H:i:s');
            $prevEndStr = $prevRange->end->format('Y-m-d H:i:s');

            // 1. Chat xabarlari
            $globalCount = (int) GlobalChatMessage::whereBetween('created_at', [$startStr, $endStr])->count();
            $groupCount = (int) GroupMessage::whereBetween('created_at', [$startStr, $endStr])->count();
            $totalChat = $globalCount + $groupCount;

            $prevGlobalCount = (int) GlobalChatMessage::whereBetween('created_at', [$prevStartStr, $prevEndStr])->count();
            $prevGroupCount = (int) GroupMessage::whereBetween('created_at', [$prevStartStr, $prevEndStr])->count();
            $prevTotalChat = $prevGlobalCount + $prevGroupCount;

            // 2. Eng faol guruhlar
            $groupActivity = GroupMessage::select([
                'group_id',
                DB::raw('COUNT(*) as messages_count'),
                DB::raw('COUNT(DISTINCT user_id) as active_posters'),
            ])
                ->whereBetween('created_at', [$startStr, $endStr])
                ->groupBy('group_id')
                ->orderByDesc('messages_count')
                ->limit(10)
                ->get()
                ->keyBy('group_id');

            $groups = Group::withCount('members')
                ->get(['id', 'name', 'is_private']);

            $topGroups = [];
            foreach ($groups as $g) {
                $gStat = $groupActivity->get($g->id);
                $msgCount = $gStat ? (int) $gStat->messages_count : 0;
                $posters = $gStat ? (int) $gStat->active_posters : 0;

                $topGroups[] = [
                    'group_id'       => (int) $g->id,
                    'name'           => $g->name,
                    'is_private'     => (bool) $g->is_private,
                    'members_count'  => (int) $g->members_count,
                    'messages_count' => $msgCount,
                    'active_posters' => $posters,
                ];
            }

            usort($topGroups, function ($a, $b) {
                if ($b['messages_count'] !== $a['messages_count']) {
                    return $b['messages_count'] <=> $a['messages_count'];
                }
                return $b['members_count'] <=> $a['members_count'];
            });

            // 3. Shikoyatlar navbati (ochiq soni, eng eskisining yoshi)
            $openReportsCount = (int) Report::where('status', 'pending')->count();
            $oldestOpenReport = Report::where('status', 'pending')
                ->oldest('created_at')
                ->first(['id', 'created_at', 'source']);

            $oldestAgeHours = null;
            $oldestCreatedAt = null;
            if ($oldestOpenReport && $oldestOpenReport->created_at) {
                $oldestCreatedAt = Carbon::parse($oldestOpenReport->created_at)->setTimezone($this->timezone)->format('Y-m-d H:i');
                $oldestAgeHours = (int) Carbon::parse($oldestOpenReport->created_at)->diffInHours(Carbon::now($this->timezone));
            }

            $periodReports = (int) Report::whereBetween('created_at', [$startStr, $endStr])->count();
            $prevPeriodReports = (int) Report::whereBetween('created_at', [$prevStartStr, $prevEndStr])->count();

            // 4. Ban statistikasi
            $currentlyBanned = (int) User::where('is_banned', true)->count();
            $permanentBans = (int) User::where('is_banned', true)->whereNull('banned_until')->count();
            $tempBans = max(0, $currentlyBanned - $permanentBans);

            // 5. Jonli efirlar statistikasi
            $liveEvents = $this->getLiveEventsStats($range);

            return [
                'chat' => [
                    'total_messages'  => $totalChat,
                    'global_messages' => $globalCount,
                    'group_messages'  => $groupCount,
                    'delta'           => DateRange::formatDelta($totalChat, $prevTotalChat),
                ],
                'top_groups' => array_slice($topGroups, 0, 10),
                'reports'    => [
                    'open_count'       => $openReportsCount,
                    'oldest_age_hours' => $oldestAgeHours,
                    'oldest_created_at'=> $oldestCreatedAt,
                    'period_count'     => $periodReports,
                    // Shikoyat o'sishi yomon ko'rsatkich -> invertColors = true
                    'delta'            => DateRange::formatDelta($periodReports, $prevPeriodReports, true),
                ],
                'bans' => [
                    'currently_banned' => $currentlyBanned,
                    'permanent_bans'   => $permanentBans,
                    'temporary_bans'   => $tempBans,
                ],
                'live_events' => $liveEvents,
            ];
        });
    }

    /**
     * Jonli efirlar ro'yxati va har efir uchun davomiylik, like, chat xabarlari
     * va (5-bosqichdan keyin) peak_viewers / unique_viewers.
     */
    public function getLiveEventsStats(DateRange $range, int $limit = 15): array
    {
        $hasAttendancesTable = Schema::hasTable('live_event_attendances');
        $hasPeakColumn = Schema::hasColumn('live_events', 'peak_viewers');

        $events = LiveEvent::withCount('questions')
            ->latest('scheduled_at')
            ->limit($limit)
            ->get();

        $items = [];
        foreach ($events as $event) {
            $durationMinutes = null;
            if ($event->started_at) {
                $endRef = $event->status === LiveEvent::STATUS_LIVE
                    ? Carbon::now($this->timezone)
                    : ($event->updated_at ?? $event->started_at);
                $durationMinutes = max(1, (int) Carbon::parse($event->started_at)->diffInMinutes($endRef));
            }

            $uniqueViewers = null;
            if ($hasAttendancesTable) {
                $uniqueViewers = (int) DB::table('live_event_attendances')
                    ->where('live_event_id', $event->id)
                    ->count();
            }

            $peakViewers = $hasPeakColumn ? (int) ($event->peak_viewers ?? 0) : null;

            $items[] = [
                'event_id'            => (int) $event->id,
                'title'               => $event->title,
                'status'              => $event->status,
                'started_at'          => $event->started_at
                    ? Carbon::parse($event->started_at)->setTimezone($this->timezone)->format('Y-m-d H:i')
                    : null,
                'duration_minutes'    => $durationMinutes,
                'likes_count'         => (int) ($event->likes_count ?? 0),
                'chat_messages_count' => (int) ($event->questions_count ?? 0),
                'unique_viewers'      => $uniqueViewers,
                'peak_viewers'        => $peakViewers,
            ];
        }

        return [
            'tracking_enabled' => $hasAttendancesTable && $hasPeakColumn,
            'items'            => $items,
        ];
    }
}
