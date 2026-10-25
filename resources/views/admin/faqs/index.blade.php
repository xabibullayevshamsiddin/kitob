@extends('admin.layouts.app')
@section('title', 'FAQ Savol-Javoblarni boshqarish')

@section('content')
<div class="space-y-5">

    {{-- Top header bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <path d="M12 17h.01"/>
                    </svg>
                </div>
                <span>FAQ Savol-Javoblar</span>
            </h1>
            <p class="text-xs text-mist mt-0.5">Foydalanuvchilar uchun tez-tez beriladigan savollar va javoblar ro'yxati</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('faq') }}" target="_blank"
               class="px-3 py-1.5 bg-ink-950 hover:bg-ink-800 text-mist hover:text-paper border border-ink-border rounded-btn text-xs font-mono transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Saytda ko'rish</span>
            </a>
            <a href="{{ route('admin.faqs.create') }}"
               class="ks-btn-primary py-1.5 px-3.5 text-xs font-mono inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Yangi savol</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-mono text-xs">
        <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-mist uppercase">Jami savollar</p>
                <p class="text-lg font-bold text-paper">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-emerald-400 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-mist uppercase">Faol savollar</p>
                <p class="text-lg font-bold text-emerald-400">{{ $stats['active'] }}</p>
            </div>
        </div>

        <div class="bg-ink-900 border border-ink-border rounded-panel px-3.5 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-slate-400 shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div>
                <p class="text-[11px] text-mist uppercase">Nofaol (yashirin)</p>
                <p class="text-lg font-bold text-slate-300">{{ $stats['inactive'] }}</p>
            </div>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.faqs.index') }}" class="bg-ink-900 border border-ink-border rounded-panel p-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
        <div class="flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Savol yoki javob bo‘yicha qidirish..."
                   class="w-full pl-8 pr-3 py-1.5 bg-ink-950 border border-ink-border focus:border-amber-400 rounded-btn text-xs text-paper placeholder-mist/60 outline-none transition-colors">
        </div>

        <div class="w-full sm:w-44">
            <select name="category" onchange="this.form.submit()"
                    class="w-full px-2.5 py-1.5 bg-ink-950 border border-ink-border focus:border-amber-400 rounded-btn text-xs text-paper outline-none transition-colors">
                <option value="all">Barcha kategoriyalar</option>
                <option value="books" {{ request('category') === 'books' ? 'selected' : '' }}>Kitoblar</option>
                <option value="points" {{ request('category') === 'points' ? 'selected' : '' }}>Ballar va Tangalar</option>
                <option value="groups" {{ request('category') === 'groups' ? 'selected' : '' }}>Guruhlar</option>
                <option value="ai" {{ request('category') === 'ai' ? 'selected' : '' }}>AI Suhbatdosh</option>
                <option value="general" {{ request('category') === 'general' ? 'selected' : '' }}>Umumiy savollar</option>
            </select>
        </div>

        <div class="w-full sm:w-36">
            <select name="status" onchange="this.form.submit()"
                    class="w-full px-2.5 py-1.5 bg-ink-950 border border-ink-border focus:border-amber-400 rounded-btn text-xs text-paper outline-none transition-colors">
                <option value="all">Barcha holat</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Faqat faollar</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Faqat nofaollar</option>
            </select>
        </div>

        <button type="submit" class="px-3.5 py-1.5 bg-ink-800 hover:bg-ink-700 text-paper text-xs font-mono font-medium rounded-btn transition-colors shrink-0">
            Filtrlash
        </button>

        @if(request()->hasAny(['search', 'category', 'status']) && (request('search') || request('category') !== 'all' || request('status') !== 'all'))
            <a href="{{ route('admin.faqs.index') }}" class="px-2.5 py-1.5 text-xs font-mono text-mist hover:text-paper transition-colors shrink-0 text-center">
                Tozalash
            </a>
        @endif
    </form>

    {{-- FAQs List Table --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel overflow-hidden shadow-sm">
        <div class="admin-table-scroll overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-ink-950/60 border-b border-ink-border text-[11px] font-mono uppercase text-mist">
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-center w-20"># Tartib</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap">Savol va Javob</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap w-44">Kategoriya</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-center w-28">Holat</th>
                        <th class="py-2.5 px-3.5 whitespace-nowrap text-right w-28">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/50 text-xs">
                    @forelse($faqs as $faq)
                        <tr class="hover:bg-ink-800/40 transition-colors {{ !$faq->is_active ? 'opacity-60' : '' }}">
                            {{-- Sort order --}}
                            <td class="py-3 px-3.5 text-center font-mono text-xs whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded bg-ink-950 border border-ink-border text-amber-400 font-bold">
                                    {{ $faq->sort_order }}
                                </span>
                            </td>

                            {{-- Question & Answer Preview --}}
                            <td class="py-3 px-3.5">
                                <div class="space-y-1 max-w-xl">
                                    <div class="flex items-center gap-2">
                                        <span class="w-4 h-4 rounded-full bg-amber-500/10 text-amber-400 font-mono text-[9px] flex items-center justify-center shrink-0 font-bold">Q</span>
                                        <p class="text-sm font-semibold text-paper">{{ $faq->question }}</p>
                                    </div>
                                    <div class="flex items-start gap-2 pl-0.5">
                                        <span class="w-4 h-4 rounded-full bg-ink-950 text-mist font-mono text-[9px] flex items-center justify-center shrink-0 mt-0.5">A</span>
                                        <p class="text-xs text-mist line-clamp-2 leading-relaxed">{{ $faq->answer }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Category badge --}}
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono border {{ $faq->category_badge_classes }}">
                                    {{ $faq->category_label }}
                                </span>
                            </td>

                            {{-- Active Toggle Status --}}
                            <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.faqs.toggle', $faq) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-medium transition-colors cursor-pointer {{ $faq->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-500/15 text-slate-400 border border-slate-500/30 hover:bg-slate-500/25' }}"
                                            title="Holatni almashtirish uchun bosing">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $faq->is_active ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                                        <span>{{ $faq->is_active ? 'Faol' : 'Nofaol' }}</span>
                                    </button>
                                </form>
                            </td>

                            {{-- Action buttons --}}
                            <td class="py-3 px-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.faqs.edit', $faq) }}"
                                       class="p-1.5 rounded bg-ink-950 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors"
                                       title="Tahrirlash">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}"
                                          data-confirm="Rostdan ham ushbu savol-javobni o'chirmoqchimisiz?"
                                          data-confirm-title="Savol-javobni o'chirish"
                                          data-confirm-type="danger"
                                          data-confirm-btn="Ha, o'chirish"
                                          class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded bg-ink-950 border border-ink-border text-mist hover:text-rose-400 hover:border-rose-500/40 hover:bg-rose-950/20 transition-colors"
                                                title="O‘chirish">
                                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="max-w-md mx-auto flex flex-col items-center justify-center text-center">
                                    <div class="w-12 h-12 rounded-full bg-ink-950 border border-ink-border text-amber-400/80 flex items-center justify-center mb-3 shadow-inner">
                                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                            <path d="M12 17h.01"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold font-serif text-paper">Hozircha qo‘shilgan savol-javoblar yo‘q</h3>
                                    <p class="text-xs text-mist mt-1 mb-4 max-w-sm">Saytda foydalanuvchilar o‘qishi uchun yangi savol va batafsil javob qo‘shing.</p>
                                    <a href="{{ route('admin.faqs.create') }}" class="ks-btn-primary py-1.5 px-3.5 text-xs font-mono inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Yangi savol qo‘shish</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($faqs->hasPages())
            <div class="p-3 border-t border-ink-border">
                {{ $faqs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
