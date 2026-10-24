<?php

namespace App\Services\Gamification;

use App\Models\User;

class ChatStyleService
{
    public const THEMES = [
        'aurora' => [
            'id' => 'aurora',
            'name' => 'Qutb yog‘dusi (Aurora Neon)',
            'icon' => '✨',
            'price' => 120,
            'description' => 'Feruza va zumrad jilosi. Chatda jozibali neon gradient va maxsus VIP nishon.',
            'bubble_class' => 'border-teal-500/50 dark:border-teal-400/50 bg-gradient-to-br from-teal-500/15 via-teal-500/5 to-ink-950 dark:from-teal-500/20 dark:via-emerald-950/40 dark:to-ink-950 shadow-[0_0_15px_rgba(20,184,166,0.18)] text-paper dark:text-teal-100',
            'badge_text' => '✨ Aurora',
            'badge_class' => 'bg-teal-500/15 border border-teal-500/30 text-teal-700 dark:text-teal-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-teal-700 dark:text-teal-300 hover:text-teal-600 dark:hover:text-teal-200',
            'ring_class' => 'ring-2 ring-teal-400 shadow-[0_0_12px_rgba(45,212,191,0.45)]',
        ],
        'cyber' => [
            'id' => 'cyber',
            'name' => 'Kiber Neon (Cyberpunk)',
            'icon' => '🔥',
            'price' => 150,
            'description' => 'Alvon kiberpank chaqnashi. Chatda olovrang-pushti neon porlashi va xaker nishoni.',
            'bubble_class' => 'border-rose-500/50 dark:border-rose-400/50 bg-gradient-to-br from-rose-500/15 via-purple-500/5 to-ink-950 dark:from-rose-500/20 dark:via-purple-950/40 dark:to-ink-950 shadow-[0_0_15px_rgba(244,63,94,0.18)] text-paper dark:text-rose-100',
            'badge_text' => '🔥 Cyberpunk',
            'badge_class' => 'bg-rose-500/15 border border-rose-500/30 text-rose-700 dark:text-rose-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-rose-600 dark:text-rose-400 hover:text-rose-500 dark:hover:text-rose-300',
            'ring_class' => 'ring-2 ring-rose-500 shadow-[0_0_12px_rgba(244,63,94,0.45)]',
        ],
        'gold' => [
            'id' => 'gold',
            'name' => 'Oltin Toj (Royal Gold)',
            'icon' => '👑',
            'price' => 100,
            'description' => 'Aristokrat oltin nurlari. Chatda qirollik oltin gradienti va jilolangan nishon.',
            'bubble_class' => 'border-amber-500/60 dark:border-amber-400/60 bg-gradient-to-br from-amber-500/20 via-amber-500/5 to-ink-950 dark:from-amber-500/20 dark:via-amber-950/30 dark:to-ink-950 shadow-[0_0_15px_rgba(245,158,11,0.22)] text-paper dark:text-amber-100',
            'badge_text' => '👑 Royal',
            'badge_class' => 'bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-amber-700 dark:text-amber-300 hover:text-amber-600 dark:hover:text-amber-200',
            'ring_class' => 'ring-2 ring-amber-400 shadow-[0_0_12px_rgba(245,158,11,0.45)]',
        ],
        'emerald' => [
            'id' => 'emerald',
            'name' => 'Zumrad Nuri (Emerald)',
            'icon' => '💎',
            'price' => 90,
            'description' => 'Qimmatbaho zumrad jilosi. Chatda nafis yashil gradient va kristall nishon.',
            'bubble_class' => 'border-emerald-500/50 dark:border-emerald-400/50 bg-gradient-to-br from-emerald-500/15 via-emerald-500/5 to-ink-950 dark:from-emerald-500/20 dark:via-emerald-950/40 dark:to-ink-950 shadow-[0_0_15px_rgba(16,185,129,0.18)] text-paper dark:text-emerald-100',
            'badge_text' => '💎 Emerald',
            'badge_class' => 'bg-emerald-500/15 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-emerald-700 dark:text-emerald-300 hover:text-emerald-600 dark:hover:text-emerald-200',
            'ring_class' => 'ring-2 ring-emerald-400 shadow-[0_0_12px_rgba(52,211,153,0.45)]',
        ],
        'obsidian' => [
            'id' => 'obsidian',
            'name' => 'Kosmik Bo‘shliq (Obsidian Void)',
            'icon' => '🌌',
            'price' => 80,
            'description' => 'Sirli galaktik kosmos jilosi. To‘q binafsha-indigo chaqnashi va kosmik nishon.',
            'bubble_class' => 'border-purple-500/50 dark:border-purple-400/50 bg-gradient-to-br from-purple-500/15 via-indigo-500/5 to-ink-950 dark:from-purple-500/20 dark:via-indigo-950/40 dark:to-ink-950 shadow-[0_0_15px_rgba(168,85,247,0.18)] text-paper dark:text-purple-100',
            'badge_text' => '🌌 Obsidian',
            'badge_class' => 'bg-purple-500/15 border border-purple-500/30 text-purple-700 dark:text-purple-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-purple-700 dark:text-purple-300 hover:text-purple-600 dark:hover:text-purple-200',
            'ring_class' => 'ring-2 ring-purple-500 shadow-[0_0_12px_rgba(168,85,247,0.45)]',
        ],
        'library' => [
            'id' => 'library',
            'name' => 'Antik Kutubxona (Vintage Library)',
            'icon' => '📜',
            'price' => 110,
            'description' => 'Qadimiy charm muqovalar va sokin mutolaa aurasini ifodalovchi asil kitobxon uslubi.',
            'bubble_class' => 'border-amber-700/50 dark:border-amber-600/50 bg-gradient-to-br from-amber-700/15 via-stone-500/5 to-ink-950 dark:from-amber-950/30 dark:via-stone-900/40 dark:to-ink-950 shadow-[0_0_15px_rgba(217,119,6,0.18)] text-paper dark:text-amber-100',
            'badge_text' => '📜 Kutubxona',
            'badge_class' => 'bg-amber-700/15 border border-amber-700/30 text-amber-800 dark:text-amber-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-amber-800 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300',
            'ring_class' => 'ring-2 ring-amber-600 shadow-[0_0_12px_rgba(217,119,6,0.4)]',
        ],
        'sunset' => [
            'id' => 'sunset',
            'name' => 'Shafaq Yog‘dusi (Sunset Solitude)',
            'icon' => '🌅',
            'price' => 95,
            'description' => 'Olovrang-binafsha shafaq nuri. Romantik sokin oqshom mutolaasi jilosi.',
            'bubble_class' => 'border-orange-500/50 dark:border-orange-400/50 bg-gradient-to-br from-orange-500/15 via-pink-500/5 to-ink-950 dark:from-orange-500/20 dark:via-pink-950/40 dark:to-ink-950 shadow-[0_0_15px_rgba(249,115,22,0.18)] text-paper dark:text-orange-100',
            'badge_text' => '🌅 Sunset',
            'badge_class' => 'bg-orange-500/15 border border-orange-500/30 text-orange-700 dark:text-orange-300 font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5',
            'name_class' => 'text-orange-700 dark:text-orange-300 hover:text-orange-600 dark:hover:text-orange-200',
            'ring_class' => 'ring-2 ring-orange-400 shadow-[0_0_12px_rgba(249,115,22,0.4)]',
        ],
    ];

    public const RANKS = [
        1 => [
            'bubble_class' => 'border-amber-400/70 dark:border-amber-400/70 bg-gradient-to-br from-amber-500/25 via-ink-950 to-ink-950 dark:from-amber-500/20 dark:via-ink-950 dark:to-ink-950 shadow-[0_0_18px_rgba(245,158,11,0.3)] text-paper dark:text-amber-100',
            'badge_text' => '🥇 Chempion',
            'badge_class' => 'bg-amber-500 text-slate-950 font-mono font-black text-[9px] rounded-pill px-1.5 py-0.5 ring-1 ring-amber-400 shadow-sm',
            'name_class' => 'text-amber-600 dark:text-amber-400 font-bold hover:text-amber-500 dark:hover:text-amber-300',
            'ring_class' => 'ring-2 ring-amber-500 shadow-[0_0_14px_rgba(245,158,11,0.5)]',
        ],
        2 => [
            'bubble_class' => 'border-slate-300/70 dark:border-slate-300/60 bg-gradient-to-br from-slate-200/20 via-ink-950 to-ink-950 dark:from-slate-200/15 dark:via-ink-950 dark:to-ink-950 shadow-[0_0_14px_rgba(226,232,240,0.2)] text-paper dark:text-slate-100',
            'badge_text' => '🥈 Kumush',
            'badge_class' => 'bg-slate-300 text-slate-950 font-mono font-black text-[9px] rounded-pill px-1.5 py-0.5 ring-1 ring-slate-200 shadow-sm',
            'name_class' => 'text-slate-700 dark:text-slate-200 font-bold hover:text-paper dark:hover:text-white',
            'ring_class' => 'ring-2 ring-slate-400 shadow-[0_0_10px_rgba(226,232,240,0.35)]',
        ],
        3 => [
            'bubble_class' => 'border-amber-700/60 dark:border-amber-600/60 bg-gradient-to-br from-amber-700/20 via-ink-950 to-ink-950 dark:from-amber-700/15 dark:via-ink-950 dark:to-ink-950 shadow-[0_0_12px_rgba(217,119,6,0.2)] text-paper dark:text-amber-100',
            'badge_text' => '🥉 Bronza',
            'badge_class' => 'bg-amber-700 text-white font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5 ring-1 ring-amber-600 shadow-sm',
            'name_class' => 'text-amber-700 dark:text-amber-500 font-bold hover:text-amber-600 dark:hover:text-amber-400',
            'ring_class' => 'ring-2 ring-amber-700 shadow-[0_0_10px_rgba(217,119,6,0.35)]',
        ],
        4 => [
            'bubble_class' => 'border-indigo-400/50 dark:border-indigo-400/50 bg-gradient-to-br from-indigo-500/15 via-ink-950 to-ink-950 dark:from-indigo-500/15 dark:via-ink-950 dark:to-ink-950 shadow-[0_0_10px_rgba(99,102,241,0.18)] text-paper dark:text-indigo-100',
            'badge_text' => '⭐ Top 5',
            'badge_class' => 'bg-indigo-600 text-white font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5 ring-1 ring-indigo-400 shadow-sm',
            'name_class' => 'text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-500 dark:hover:text-indigo-300',
            'ring_class' => 'ring-2 ring-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.35)]',
        ],
        5 => [
            'bubble_class' => 'border-indigo-400/50 dark:border-indigo-400/50 bg-gradient-to-br from-indigo-500/15 via-ink-950 to-ink-950 dark:from-indigo-500/15 dark:via-ink-950 dark:to-ink-950 shadow-[0_0_10px_rgba(99,102,241,0.18)] text-paper dark:text-indigo-100',
            'badge_text' => '⭐ Top 5',
            'badge_class' => 'bg-indigo-600 text-white font-mono font-bold text-[9px] rounded-pill px-1.5 py-0.5 ring-1 ring-indigo-400 shadow-sm',
            'name_class' => 'text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-500 dark:hover:text-indigo-300',
            'ring_class' => 'ring-2 ring-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.35)]',
        ],
    ];

    /**
     * Foydalanuvchining chatdagi vizual uslubini hal qilish.
     */
    public static function resolveStyle(?User $user, ?int $rank = null, bool $isMe = false): array
    {
        if (!$user) {
            return self::defaultStyle($isMe);
        }

        $profile = $user->profile;
        $preference = $profile?->chat_style_preference ?? 'shop';
        $shopThemeKey = $profile?->profile_theme ?? $profile?->chat_bubble_theme ?? $profile?->avatar_frame;

        // 0. Agar foydalanuvchi "default" (Standart) ko'rinishni tanlagan bo'lsa:
        if ($preference === 'default') {
            return self::defaultStyle($isMe);
        }

        // 1. Agar foydalanuvchi "rank" tanlagan bo'lsa va top-5 o'rinda bo'lsa:
        if ($preference === 'rank' && $rank && isset(self::RANKS[$rank])) {
            $res = self::RANKS[$rank];
            $res['mode'] = 'rank';
            $res['rank'] = $rank;
            return $res;
        }

        // 2. Agar foydalanuvchi "shop" tanlagan bo'lsa va do'kondan olgan narsasi bo'lsa:
        if ($preference === 'shop' && $shopThemeKey && isset(self::THEMES[$shopThemeKey])) {
            $res = self::THEMES[$shopThemeKey];
            $res['mode'] = 'shop';
            $res['theme_key'] = $shopThemeKey;
            $res['rank'] = $rank;
            return $res;
        }

        // 3. Agar "rank" tanlangan bo'lsa, lekin foydalanuvchi top-5 da bo'lmasa:
        if ($preference === 'rank') {
            return self::defaultStyle($isMe);
        }

        // 4. Standart holat
        return self::defaultStyle($isMe);
    }

    public static function defaultStyle(bool $isMe = false): array
    {
        return [
            'mode' => 'default',
            'bubble_class' => $isMe ? 'bg-ink-800 border-amber-500/40 text-paper' : 'bg-ink-950 border-ink-border text-paper',
            'badge_text' => null,
            'badge_class' => null,
            'name_class' => 'text-paper',
            'ring_class' => 'border border-ink-border',
            'rank' => null,
        ];
    }
}
