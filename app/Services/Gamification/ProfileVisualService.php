<?php

namespace App\Services\Gamification;

use App\Models\User;

class ProfileVisualService
{
    /**
     * Do'konda mavjud bo'lgan profil vizual fonlari (Tangaga sotib olinadi).
     */
    public const THEMES = [
        'aurora' => [
            'id' => 'aurora',
            'name' => 'Qutb yog‘dusi (Aurora Neon)',
            'icon' => '✨',
            'price' => 120,
            'description' => 'Feruza va zumrad jilosi. Profilingizda sehrli shimol yog‘dusi va jonli neon aurasini taratadi.',
            'banner_gradient' => 'bg-gradient-to-r from-teal-50/90 via-cyan-100/70 to-emerald-50/90 dark:from-ink-950 dark:via-teal-950/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(0,168,181,0.25) 0%, rgba(16,185,129,0.15) 45%, transparent 70%)',
            'card_border' => 'border-teal-500/30 dark:border-teal-500/20',
            'card_glow' => 'shadow-xl shadow-teal-500/10 dark:shadow-teal-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-teal-500 via-emerald-400 to-transparent',
            'badge_text' => '✨ Aurora',
            'badge_class' => 'bg-teal-500/15 dark:bg-ink-950/80 border border-teal-500/40 text-teal-800 dark:text-teal-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-teal-500/40',
            'avatar_ring' => 'ring-2 ring-teal-500 dark:ring-teal-400/80 shadow-[0_0_14px_rgba(0,168,181,0.3)]',
            'accent_color' => '#00A8B5',
            'pattern' => 'aurora',
        ],
        'cyber' => [
            'id' => 'cyber',
            'name' => 'Kiber Neon (Cyberpunk)',
            'icon' => '🔥',
            'price' => 150,
            'description' => 'Alvon-pushti kiberpank chaqnashi. Profilingizda futuristik neon panjara va xaker aurasini namoyon etadi.',
            'banner_gradient' => 'bg-gradient-to-r from-rose-50/90 via-pink-100/70 to-purple-50/90 dark:from-ink-950 dark:via-rose-950/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(244,63,94,0.22) 0%, rgba(168,85,247,0.15) 45%, transparent 70%)',
            'card_border' => 'border-rose-500/30 dark:border-rose-500/20',
            'card_glow' => 'shadow-xl shadow-rose-500/10 dark:shadow-rose-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-rose-500 via-purple-500 to-transparent',
            'badge_text' => '🔥 Cyberpunk',
            'badge_class' => 'bg-rose-500/15 dark:bg-ink-950/80 border border-rose-500/40 text-rose-800 dark:text-rose-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-rose-500/40',
            'avatar_ring' => 'ring-2 ring-rose-500 dark:ring-rose-500/80 shadow-[0_0_14px_rgba(244,63,94,0.3)]',
            'accent_color' => '#F43F5E',
            'pattern' => 'cyber',
        ],
        'gold' => [
            'id' => 'gold',
            'name' => 'Qirollik Oltini (Royal Gold)',
            'icon' => '👑',
            'price' => 100,
            'description' => 'Aristokrat oltin nurlari. Profilingizda shohona oltin jilva, toj yulduzlari va hashamatli fon bag‘ishlaydi.',
            'banner_gradient' => 'bg-gradient-to-r from-amber-50 via-yellow-100/70 to-amber-50 dark:from-ink-950 dark:via-amber-950/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(245,158,11,0.25) 0%, rgba(217,119,6,0.12) 45%, transparent 70%)',
            'card_border' => 'border-amber-500/30 dark:border-amber-500/25',
            'card_glow' => 'shadow-xl shadow-amber-500/10 dark:shadow-amber-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-amber-500 via-yellow-400 to-transparent',
            'badge_text' => '👑 Royal Gold',
            'badge_class' => 'bg-amber-500/15 dark:bg-ink-950/80 border border-amber-500/40 text-amber-800 dark:text-amber-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-amber-500/40',
            'avatar_ring' => 'ring-2 ring-amber-500 dark:ring-amber-400/80 shadow-[0_0_14px_rgba(245,158,11,0.3)]',
            'accent_color' => '#F59E0B',
            'pattern' => 'gold',
        ],
        'emerald' => [
            'id' => 'emerald',
            'name' => 'Zumrad Nuri (Emerald Realm)',
            'icon' => '💎',
            'price' => 90,
            'description' => 'Qimmatbaho zumrad kristalli. Profilingizda tabiat jozibasi va nafis zumrad yashilligi nurlanadi.',
            'banner_gradient' => 'bg-gradient-to-r from-emerald-50 via-teal-100/60 to-emerald-50 dark:from-ink-950 dark:via-emerald-950/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(16,185,129,0.22) 0%, rgba(5,150,105,0.12) 45%, transparent 70%)',
            'card_border' => 'border-emerald-500/30 dark:border-emerald-500/20',
            'card_glow' => 'shadow-xl shadow-emerald-500/10 dark:shadow-emerald-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-emerald-500 via-teal-400 to-transparent',
            'badge_text' => '💎 Emerald',
            'badge_class' => 'bg-emerald-500/15 dark:bg-ink-950/80 border border-emerald-500/40 text-emerald-800 dark:text-emerald-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-emerald-500/40',
            'avatar_ring' => 'ring-2 ring-emerald-500 dark:ring-emerald-400/80 shadow-[0_0_14px_rgba(16,185,129,0.3)]',
            'accent_color' => '#10B981',
            'pattern' => 'emerald',
        ],
        'obsidian' => [
            'id' => 'obsidian',
            'name' => 'Kosmik Bo‘shliq (Obsidian Void)',
            'icon' => '🌌',
            'price' => 80,
            'description' => 'Sirli galaktik kosmos jilosi. Cheksiz koinot, to‘q binafsha tumanlik va yulduzlar galaktikasi.',
            'banner_gradient' => 'bg-gradient-to-r from-purple-50 via-indigo-100/60 to-violet-50 dark:from-ink-950 dark:via-purple-950/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(168,85,247,0.22) 0%, rgba(99,102,241,0.12) 45%, transparent 70%)',
            'card_border' => 'border-purple-500/30 dark:border-purple-500/20',
            'card_glow' => 'shadow-xl shadow-purple-500/10 dark:shadow-purple-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-purple-500 via-indigo-400 to-transparent',
            'badge_text' => '🌌 Obsidian',
            'badge_class' => 'bg-purple-500/15 dark:bg-ink-950/80 border border-purple-500/40 text-purple-800 dark:text-purple-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-purple-500/40',
            'avatar_ring' => 'ring-2 ring-purple-500 dark:ring-purple-500/80 shadow-[0_0_14px_rgba(168,85,247,0.3)]',
            'accent_color' => '#A855F7',
            'pattern' => 'cosmic',
        ],
        'library' => [
            'id' => 'library',
            'name' => 'Antik Kutubxona (Vintage Library)',
            'icon' => '📜',
            'price' => 110,
            'description' => 'Qadimiy charm muqovalar, iliq kahrabo yog‘och va sokin mutolaa aurasini ifodalovchi asil kitobxon foni.',
            'banner_gradient' => 'bg-gradient-to-r from-stone-100 via-amber-100/50 to-orange-50 dark:from-ink-950 dark:via-amber-950/30 dark:to-stone-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(217,119,6,0.2) 0%, transparent 65%)',
            'card_border' => 'border-amber-700/30 dark:border-amber-700/25',
            'card_glow' => 'shadow-xl shadow-amber-700/10 dark:shadow-amber-950/15',
            'card_accent_line' => 'bg-gradient-to-r from-amber-600 via-yellow-600 to-transparent',
            'badge_text' => '📜 Vintage Library',
            'badge_class' => 'bg-amber-700/15 dark:bg-ink-950/80 border border-amber-700/40 text-amber-900 dark:text-amber-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-amber-700/40',
            'avatar_ring' => 'ring-2 ring-amber-600 dark:ring-amber-600/80 shadow-[0_0_12px_rgba(217,119,6,0.3)]',
            'accent_color' => '#D97706',
            'pattern' => 'vintage',
        ],
        'sunset' => [
            'id' => 'sunset',
            'name' => 'Shafaq Yog‘dusi (Sunset Solitude)',
            'icon' => '🌅',
            'price' => 95,
            'description' => 'Olovrang-binafsha shafaq nuri. Romantik sokin oqshom mutolaasini eslatuvchi sokin ranglar jilosi.',
            'banner_gradient' => 'bg-gradient-to-r from-orange-50 via-rose-100/60 to-pink-50 dark:from-ink-950 dark:via-orange-950/30 dark:to-pink-950/25',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(249,115,22,0.22) 0%, rgba(236,72,153,0.12) 45%, transparent 70%)',
            'card_border' => 'border-orange-500/30 dark:border-orange-500/20',
            'card_glow' => 'shadow-xl shadow-orange-500/10 dark:shadow-orange-950/15',
            'card_accent_line' => 'bg-gradient-to-r from-orange-500 via-pink-400 to-transparent',
            'badge_text' => '🌅 Sunset Solitude',
            'badge_class' => 'bg-orange-500/15 dark:bg-ink-950/80 border border-orange-500/40 text-orange-800 dark:text-orange-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-orange-500/40',
            'avatar_ring' => 'ring-2 ring-orange-500 dark:ring-orange-400/80 shadow-[0_0_12px_rgba(249,115,22,0.3)]',
            'accent_color' => '#F97316',
            'pattern' => 'sunset',
        ],
    ];

    /**
     * Reyting o'rinlari bo'yicha maxsus nufuzli fonlar (Leaderboard Rank Themes).
     */
    public const RANKS = [
        1 => [
            'id' => 'rank_1',
            'name' => '🥇 #1 Chempion Oltin Aurası',
            'icon' => '👑',
            'banner_gradient' => 'bg-gradient-to-r from-amber-50 via-yellow-100/80 to-amber-50 dark:from-ink-950 dark:via-amber-950/45 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(245,158,11,0.28) 0%, rgba(217,119,6,0.12) 45%, transparent 70%)',
            'card_border' => 'border-amber-400/50 dark:border-amber-400/30',
            'card_glow' => 'shadow-xl shadow-amber-500/15 dark:shadow-amber-950/25',
            'card_accent_line' => 'bg-gradient-to-r from-amber-500 via-yellow-400 to-transparent',
            'badge_text' => '🥇 Reyting Chempioni',
            'badge_class' => 'bg-amber-500/15 dark:bg-amber-500/20 border border-amber-500/40 dark:border-amber-400/40 text-amber-800 dark:text-amber-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-amber-500/40',
            'avatar_ring' => 'ring-2 ring-amber-500 dark:ring-amber-400 shadow-[0_0_14px_rgba(245,158,11,0.35)]',
            'accent_color' => '#F59E0B',
            'pattern' => 'rank-gold',
        ],
        2 => [
            'id' => 'rank_2',
            'name' => '🥈 #2 Kumush Sovrindor Aurası',
            'icon' => '🥈',
            'banner_gradient' => 'bg-gradient-to-r from-slate-100 via-slate-200/80 to-slate-100 dark:from-ink-950 dark:via-slate-900/40 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(226,232,240,0.25) 0%, rgba(148,163,184,0.15) 45%, transparent 70%)',
            'card_border' => 'border-slate-400/40 dark:border-slate-400/25',
            'card_glow' => 'shadow-xl shadow-slate-500/10 dark:shadow-slate-950/20',
            'card_accent_line' => 'bg-gradient-to-r from-slate-400 via-slate-300 to-transparent',
            'badge_text' => '🥈 Kumush Sovrindor',
            'badge_class' => 'bg-slate-200/90 dark:bg-slate-300/15 border border-slate-400/50 dark:border-slate-300/30 text-slate-700 dark:text-slate-200 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-slate-400/40',
            'avatar_ring' => 'ring-2 ring-slate-400 dark:ring-slate-300 shadow-[0_0_14px_rgba(226,232,240,0.35)]',
            'accent_color' => '#94A3B8',
            'pattern' => 'rank-silver',
        ],
        3 => [
            'id' => 'rank_3',
            'name' => '🥉 #3 Bronza Sovrindor Aurası',
            'icon' => '🥉',
            'banner_gradient' => 'bg-gradient-to-r from-amber-100/70 via-orange-100/60 to-amber-50 dark:from-ink-950 dark:via-amber-950/35 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(217,119,6,0.22) 0%, rgba(180,83,9,0.12) 45%, transparent 70%)',
            'card_border' => 'border-amber-700/40 dark:border-amber-700/25',
            'card_glow' => 'shadow-xl shadow-amber-700/10 dark:shadow-amber-950/15',
            'card_accent_line' => 'bg-gradient-to-r from-amber-600 via-amber-700 to-transparent',
            'badge_text' => '🥉 Bronza Sovrindor',
            'badge_class' => 'bg-amber-700/20 dark:bg-amber-700/20 border border-amber-700/40 dark:border-amber-600/30 text-amber-900 dark:text-amber-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-amber-700/40',
            'avatar_ring' => 'ring-2 ring-amber-700 dark:ring-amber-700 shadow-[0_0_12px_rgba(217,119,6,0.35)]',
            'accent_color' => '#D97706',
            'pattern' => 'rank-bronze',
        ],
        4 => [
            'id' => 'rank_4',
            'name' => '⭐ #4 Top 5 Elita Aurası',
            'icon' => '⭐',
            'banner_gradient' => 'bg-gradient-to-r from-indigo-50 via-indigo-100/60 to-purple-50 dark:from-ink-950 dark:via-indigo-950/35 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(99,102,241,0.22) 0%, rgba(79,70,229,0.12) 45%, transparent 70%)',
            'card_border' => 'border-indigo-500/30 dark:border-indigo-500/20',
            'card_glow' => 'shadow-xl shadow-indigo-500/10 dark:shadow-indigo-950/15',
            'card_accent_line' => 'bg-gradient-to-r from-indigo-500 via-purple-500 to-transparent',
            'badge_text' => '⭐ Top 5 Elita (#4)',
            'badge_class' => 'bg-indigo-500/15 dark:bg-indigo-500/20 border border-indigo-500/40 dark:border-indigo-400/30 text-indigo-800 dark:text-indigo-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-indigo-500/40',
            'avatar_ring' => 'ring-2 ring-indigo-500 dark:ring-indigo-500 shadow-[0_0_12px_rgba(99,102,241,0.35)]',
            'accent_color' => '#6366F1',
            'pattern' => 'rank-indigo',
        ],
        5 => [
            'id' => 'rank_5',
            'name' => '⭐ #5 Top 5 Elita Aurası',
            'icon' => '⭐',
            'banner_gradient' => 'bg-gradient-to-r from-indigo-50 via-indigo-100/60 to-purple-50 dark:from-ink-950 dark:via-indigo-950/35 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(circle at 85% 25%, rgba(99,102,241,0.22) 0%, rgba(79,70,229,0.12) 45%, transparent 70%)',
            'card_border' => 'border-indigo-500/30 dark:border-indigo-500/20',
            'card_glow' => 'shadow-xl shadow-indigo-500/10 dark:shadow-indigo-950/15',
            'card_accent_line' => 'bg-gradient-to-r from-indigo-500 via-purple-500 to-transparent',
            'badge_text' => '⭐ Top 5 Elita (#5)',
            'badge_class' => 'bg-indigo-500/15 dark:bg-indigo-500/20 border border-indigo-500/40 dark:border-indigo-400/30 text-indigo-800 dark:text-indigo-300 font-mono font-bold text-[10px] rounded-full px-3 py-1 backdrop-blur-md shadow-sm',
            'stat_card_class' => 'bg-white dark:bg-ink-950/40 border-slate-200 dark:border-ink-border/60 hover:border-indigo-500/40',
            'avatar_ring' => 'ring-2 ring-indigo-500 dark:ring-indigo-500 shadow-[0_0_12px_rgba(99,102,241,0.35)]',
            'accent_color' => '#6366F1',
            'pattern' => 'rank-indigo',
        ],
    ];

    /**
     * Foydalanuvchining tashqi profil sahifasida ko'rinadigan vizual fonini aniqlash.
     */
    public static function resolveVisual(?User $user, ?int $rank = null): array
    {
        if (!$user) {
            return self::defaultVisual();
        }

        $profile = $user->profile;
        $preference = $profile?->chat_style_preference ?? 'shop';
        $themeKey = $profile?->profile_theme ?? $profile?->avatar_frame ?? $profile?->chat_bubble_theme;

        // 1. Agar foydalanuvchi "Standart" ko'rinishni tanlagan bo'lsa:
        if ($preference === 'default') {
            return self::defaultVisual();
        }

        // 2. Agar foydalanuvchi "Reyting" (Rank) uslubini tanlagan bo'lsa:
        if ($preference === 'rank') {
            if ($rank && isset(self::RANKS[$rank])) {
                $res = self::RANKS[$rank];
                $res['mode'] = 'rank';
                $res['rank'] = $rank;
                return $res;
            }
            // Agar foydalanuvchi reytingda top-5 da bo'lmasa, lekin rank tanlagan bo'lsa:
            // Ballarga qarab shinam reyting aurasini beramiz
            return self::rankPointsVisual($user);
        }

        // 3. Agar foydalanuvchi "Do'kon" (Shop) uslubini tanlagan bo'lsa va mavzusi bo'lsa:
        if ($preference === 'shop' && $themeKey && isset(self::THEMES[$themeKey])) {
            $res = self::THEMES[$themeKey];
            $res['mode'] = 'shop';
            $res['theme_key'] = $themeKey;
            $res['rank'] = $rank;
            return $res;
        }

        // 4. Agar shop tanlangan bo'lsa, lekin hali hech narsa sotib olinmagan bo'lsa,
        // agar u reytingda bo'lsa, reyting jilosini ko'rsatamiz:
        if ($rank && isset(self::RANKS[$rank])) {
            $res = self::RANKS[$rank];
            $res['mode'] = 'rank';
            $res['rank'] = $rank;
            return $res;
        }

        // 5. Standart ko'rinish
        return self::defaultVisual();
    }

    /**
     * Reytingda top-5 da bo'lmagan, lekin Reyting uslubini tanlagan foydalanuvchilar uchun umumiy reyting aurası.
     */
    public static function rankPointsVisual(User $user): array
    {
        $points = (int) $user->total_points;
        $level = match(true) {
            $points >= 1000 => 'Olmos Kitobxon',
            $points >= 500  => 'Zukko Kitobxon',
            $points >= 200  => 'Faol Kitobxon',
            default         => 'Boshlang‘ich Kitobxon',
        };

        return [
            'id' => 'rank_active',
            'name' => "🏆 Reyting: {$level}",
            'icon' => '🌟',
            'mode' => 'rank',
            'rank' => null,
            'banner_gradient' => 'bg-gradient-to-r from-amber-50 via-slate-50 to-amber-50/70 dark:from-amber-950/60 dark:via-ink-900 dark:to-ink-950',
            'banner_ambient' => 'radial-gradient(ellipse at 80% 20%, rgba(245,158,11,0.2) 0%, transparent 65%)',
            'card_border' => 'border-amber-500/40 dark:border-amber-500/30',
            'card_glow' => 'shadow-xl shadow-amber-500/10 dark:shadow-[0_0_25px_rgba(245,158,11,0.12)]',
            'card_accent_line' => 'bg-gradient-to-r from-amber-500 via-amber-600 to-transparent',
            'badge_text' => "🌟 {$level}",
            'badge_class' => 'bg-amber-500/15 border border-amber-500/30 text-amber-800 dark:text-amber-300 font-mono font-bold text-[10px] rounded-pill px-2.5 py-0.5',
            'stat_card_class' => 'bg-white dark:bg-amber-950/15 border-slate-200 dark:border-amber-500/20 hover:border-amber-500/35',
            'avatar_ring' => 'ring-2 ring-amber-500/60 shadow-[0_0_10px_rgba(245,158,11,0.3)]',
            'accent_color' => '#F59E0B',
            'pattern' => 'rank-gold',
        ];
    }

    /**
     * Standart oddiy klassik profil ko'rinishi.
     */
    public static function defaultVisual(): array
    {
        return [
            'id' => 'default',
            'name' => 'Standart Ko‘rinish',
            'icon' => '👤',
            'mode' => 'default',
            'rank' => null,
            'banner_gradient' => 'bg-gradient-to-r from-slate-100 via-slate-50 to-slate-100 dark:from-ink-900 dark:via-ink-950 dark:to-ink-950',
            'banner_ambient' => 'none',
            'card_border' => 'border-slate-200 dark:border-ink-border',
            'card_glow' => 'shadow-sm',
            'card_accent_line' => 'bg-slate-200 dark:bg-ink-border',
            'badge_text' => null,
            'badge_class' => null,
            'stat_card_class' => 'bg-white dark:bg-ink-800/60 border-slate-200 dark:border-ink-border hover:border-slate-300 dark:hover:border-ink-border/80',
            'avatar_ring' => 'border border-slate-300 dark:border-ink-border',
            'accent_color' => '#00A8B5',
            'pattern' => 'none',
        ];
    }
}
