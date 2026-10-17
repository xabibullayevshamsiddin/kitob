@extends('admin.layouts.app')
@section('title', 'Yangi FAQ savol-javob qo‘shish')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-mist mb-1">
                <a href="{{ route('admin.faqs.index') }}" class="hover:text-paper transition-colors">FAQ Savol-Javoblar</a>
                <span>/</span>
                <span class="text-amber-400">Yangi qo‘shish</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">Yangi savol va javob kiritish</h1>
            <p class="text-xs text-mist mt-0.5">Foydalanuvchilar FAQ bo‘limida ko‘radigan yangi ma'lumot</p>
        </div>
        <a href="{{ route('admin.faqs.index') }}"
           class="px-3 py-1.5 bg-ink-950 hover:bg-ink-800 text-mist hover:text-paper border border-ink-border rounded-btn text-xs font-mono transition-colors flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            <span>Orqaga</span>
        </a>
    </div>

    {{-- Form Card --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-5">
            @csrf

            {{-- Question --}}
            <div class="space-y-1.5">
                <label for="question" class="block text-xs font-mono font-medium text-paper">
                    Savol matni <span class="text-amber-400">*</span>
                </label>
                <input type="text" id="question" name="question" value="{{ old('question') }}" required
                       placeholder="Masalan: Kitobxon platformasida qanday qilib tangalar ishlanadi?"
                       class="w-full px-3.5 py-2 bg-ink-950 border @error('question') border-rose-500 @else border-ink-border @enderror focus:border-amber-400 rounded-btn text-xs text-paper placeholder-mist/50 outline-none transition-colors">
                @error('question')
                    <p class="text-xs text-rose-400 font-mono mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category & Sort order row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="category" class="block text-xs font-mono font-medium text-paper">
                        Kategoriya <span class="text-amber-400">*</span>
                    </label>
                    <select id="category" name="category" required
                            class="w-full px-3 py-2 bg-ink-950 border @error('category') border-rose-500 @else border-ink-border @enderror focus:border-amber-400 rounded-btn text-xs text-paper outline-none transition-colors">
                        <option value="books" {{ old('category') === 'books' ? 'selected' : '' }}>Kitoblar va Mutolaa</option>
                        <option value="points" {{ old('category') === 'points' ? 'selected' : '' }}>Ballar va Tangalar</option>
                        <option value="groups" {{ old('category') === 'groups' ? 'selected' : '' }}>Guruhlar</option>
                        <option value="ai" {{ old('category') === 'ai' ? 'selected' : '' }}>AI Suhbatdosh</option>
                        <option value="general" {{ old('category', 'general') === 'general' ? 'selected' : '' }}>Umumiy savollar</option>
                    </select>
                    @error('category')
                        <p class="text-xs text-rose-400 font-mono mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="sort_order" class="block text-xs font-mono font-medium text-paper">
                        Ko‘rsatish tartibi (Tartib raqami)
                    </label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999"
                           class="w-full px-3 py-2 bg-ink-950 border @error('sort_order') border-rose-500 @else border-ink-border @enderror focus:border-amber-400 rounded-btn text-xs text-paper outline-none transition-colors">
                    <span class="text-[11px] text-mist block">Kichik raqamlar ro‘yxatda oldin chiqadi (masalan: 1, 2, 3...)</span>
                    @error('sort_order')
                        <p class="text-xs text-rose-400 font-mono mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Answer --}}
            <div class="space-y-1.5">
                <label for="answer" class="block text-xs font-mono font-medium text-paper">
                    Batafsil javob matni <span class="text-amber-400">*</span>
                </label>
                <textarea id="answer" name="answer" rows="5" required
                          placeholder="Savolning batafsil va tushunarli javobini yozing..."
                          class="w-full px-3.5 py-2.5 bg-ink-950 border @error('answer') border-rose-500 @else border-ink-border @enderror focus:border-amber-400 rounded-btn text-xs text-paper placeholder-mist/50 outline-none transition-colors resize-y leading-relaxed">{{ old('answer') }}</textarea>
                @error('answer')
                    <p class="text-xs text-rose-400 font-mono mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active status checkbox --}}
            <div class="p-3.5 rounded-btn bg-ink-950/60 border border-ink-border/80 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-paper font-mono">Darhol saytda faollashtirish</p>
                    <p class="text-[11px] text-mist">Belgilansa, savol-javob darhol foydalanuvchilar uchun ko‘rinadi</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5 bg-ink-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            {{-- Submit buttons --}}
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-ink-border">
                <a href="{{ route('admin.faqs.index') }}"
                   class="px-3.5 py-1.5 rounded-btn border border-ink-border hover:bg-ink-800 text-xs font-mono text-mist hover:text-paper transition-colors">
                    Bekor qilish
                </a>
                <button type="submit"
                        class="ks-btn-primary py-1.5 px-4 text-xs font-mono inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Saqlash va E'lon qilish</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
