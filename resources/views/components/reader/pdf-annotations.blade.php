@props(['book', 'annotations' => []])

<section x-data="pdfAnnotationApp(@js($annotations), @js(route('books.pdf.annotations.store', $book)), @js(route('books.pdf.annotations.destroy', ['pdfAnnotation' => '__ANNOTATION_ID__'])))"
         class="rounded-card border border-ink-border bg-ink-900 p-4 sm:p-5"
         aria-labelledby="pdf-annotations-title">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 id="pdf-annotations-title" class="font-serif text-base font-bold text-paper">PDF bet belgilari</h2>
            <p class="mt-1 text-xs text-mist">Betni xatcho‘p qiling yoki shu betdagi voqea haqida shaxsiy qayd yozing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <label for="pdf-annotation-page" class="text-xs text-mist">Bet</label>
            <select id="pdf-annotation-page" x-model.number="annotationPage"
                    class="min-h-11 rounded-btn border border-ink-border bg-ink-950 px-3 text-sm text-paper focus:border-amber-400 focus:outline-none"
                    aria-label="Belgilash uchun PDF betini tanlang">
                <template x-for="page in visiblePages" :key="page">
                    <option :value="page" x-text="page + '-bet'"></option>
                </template>
            </select>
            <span class="text-xs text-mist" x-show="totalPages" x-text="'/ ' + totalPages"></span>
            <button type="button" @click="openForm('bookmark')"
                    class="min-h-11 rounded-btn border border-amber-500/30 bg-amber-500/10 px-3.5 text-xs font-semibold text-amber-400 transition hover:bg-amber-500/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400">
                Betni saqlash
            </button>
            <button type="button" @click="openForm('note')"
                    class="min-h-11 rounded-btn bg-amber-500 px-3.5 text-xs font-bold text-ink-950 transition hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400">
                Shu betga qayd
            </button>
        </div>
    </div>

    <div x-show="formOpen" x-cloak class="mt-4 space-y-3 rounded-btn border border-ink-border bg-ink-950/70 p-3 sm:p-4">
        <p class="text-xs font-semibold text-amber-400" x-text="formKind === 'bookmark' ? 'Xatcho‘p — ' + annotationPage + '-bet' : 'Shaxsiy qayd — ' + annotationPage + '-bet'"></p>
        <label class="block space-y-1.5">
            <span class="text-xs font-medium text-paper" x-text="formKind === 'bookmark' ? 'Xatcho‘p nomi (ixtiyoriy)' : 'Qayd sarlavhasi (ixtiyoriy)'"></span>
            <input type="text" x-model="labelDraft" maxlength="120"
                   class="ks-input w-full" placeholder="Masalan: Muhim voqea">
        </label>
        <label x-show="formKind === 'note'" class="block space-y-1.5">
            <span class="text-xs font-medium text-paper">Shu betdagi voqea yoki fikr</span>
            <textarea x-model="noteDraft" rows="3" maxlength="5000"
                      class="ks-input w-full resize-y" placeholder="Bu betda nima bo‘ldi? O‘zingiz uchun izoh yozing…"></textarea>
        </label>
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" @click="formOpen = false" class="min-h-11 rounded-btn px-4 text-xs font-medium text-mist hover:text-paper">Bekor qilish</button>
            <button type="button" @click="saveAnnotation()" :disabled="saving || (formKind === 'note' && !noteDraft.trim())"
                    class="min-h-11 rounded-btn bg-amber-500 px-4 text-xs font-bold text-ink-950 transition hover:bg-amber-400 disabled:cursor-not-allowed disabled:opacity-50"
                    x-text="saving ? 'Saqlanmoqda…' : 'Saqlash'"></button>
        </div>
    </div>

    <div class="mt-4 border-t border-ink-border pt-3">
        <div class="mb-2 flex items-center justify-between gap-2">
            <h3 class="text-xs font-bold uppercase tracking-wide text-mist">Sizning belgilaringiz</h3>
            <span class="text-[11px] text-mist" x-text="annotations.length + ' ta'"></span>
        </div>
        <p x-show="annotations.length === 0" class="py-3 text-xs text-mist">Hali bet belgisi yoki qayd yo‘q.</p>
        <ul x-show="annotations.length > 0" class="grid max-h-64 gap-2 overflow-y-auto sm:grid-cols-2">
            <template x-for="annotation in annotations" :key="annotation.id">
                <li class="flex min-w-0 items-stretch gap-2 rounded-btn border border-ink-border/80 bg-ink-950/50 p-2">
                    <button type="button" @click="jumpToAnnotation(annotation)"
                            class="min-h-11 min-w-0 flex-1 rounded-btn px-2 text-left transition hover:bg-amber-500/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-amber-400"
                            :aria-label="'PDF ' + annotation.page_number + '-betga qaytish'">
                        <span class="block text-xs font-semibold text-amber-400" x-text="(annotation.kind === 'bookmark' ? 'Xatcho‘p' : 'Qayd') + ' · ' + annotation.page_number + '-bet'"></span>
                        <span x-show="annotation.label" class="mt-1 block truncate text-xs font-medium text-paper" x-text="annotation.label"></span>
                        <span x-show="annotation.note_text" class="mt-1 block whitespace-pre-wrap break-words text-xs text-mist" x-text="annotation.note_text"></span>
                    </button>
                    <button type="button" @click="removeAnnotation(annotation)" aria-label="Belgini o‘chirish"
                            class="min-h-11 min-w-11 shrink-0 rounded-btn text-sm text-rose-400 transition hover:bg-rose-500/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-rose-400">×</button>
                </li>
            </template>
        </ul>
    </div>
    <p x-show="message" x-text="message" role="status" aria-live="polite" class="mt-3 text-xs text-emerald-400"></p>
</section>

@once
    <script>
        window.pdfAnnotationApp = function (initialAnnotations, storeUrl, destroyUrlTemplate) {
            return {
                annotations: initialAnnotations || [],
                currentPage: 1,
                totalPages: 0,
                visiblePages: [1],
                annotationPage: 1,
                formOpen: false,
                formKind: 'bookmark',
                labelDraft: '',
                noteDraft: '',
                saving: false,
                message: '',

                init() {
                    window.addEventListener('flipbook:pagechange', (event) => {
                        this.currentPage = Number(event.detail.page) || 1;
                        this.totalPages = Number(event.detail.totalPages) || 0;
                        this.visiblePages = Array.isArray(event.detail.visiblePages) && event.detail.visiblePages.length
                            ? event.detail.visiblePages
                            : [this.currentPage];
                        if (!this.visiblePages.includes(this.annotationPage)) this.annotationPage = this.currentPage;
                    });
                },

                openForm(kind) {
                    this.formKind = kind;
                    this.formOpen = true;
                    this.labelDraft = '';
                    this.noteDraft = '';
                },

                async request(url, method, payload = null) {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: payload ? JSON.stringify(payload) : null,
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || 'Amalni bajarib bo‘lmadi.');
                    return data;
                },

                async saveAnnotation() {
                    if (this.saving || (this.formKind === 'note' && !this.noteDraft.trim())) return;
                    this.saving = true;
                    try {
                        const data = await this.request(storeUrl, 'POST', {
                            page_number: Number(this.annotationPage),
                            kind: this.formKind,
                            label: this.labelDraft.trim(),
                            note_text: this.formKind === 'note' ? this.noteDraft.trim() : null,
                        });
                        this.annotations.unshift(data.annotation);
                        if (window.KitobSound) {
                            window.KitobSound.play('bookmark');
                        }
                        this.formOpen = false;
                        this.message = this.formKind === 'bookmark' ? 'PDF beti belgilandi.' : 'Betga shaxsiy qayd saqlandi.';
                        this.noteDraft = '';
                        this.labelDraft = '';
                    } catch (error) {
                        this.message = error.message || 'Saqlashda xatolik yuz berdi.';
                    } finally {
                        this.saving = false;
                    }
                },

                jumpToAnnotation(annotation) {
                    if (typeof window.fbGoToPage === 'function') {
                        window.fbGoToPage(Number(annotation.page_number));
                    } else {
                        this.message = 'Avval PDF kitobning 3D ko‘rinishini oching.';
                    }
                },

                async removeAnnotation(annotation) {
                    try {
                        await this.request(destroyUrlTemplate.replace('__ANNOTATION_ID__', encodeURIComponent(annotation.id)), 'DELETE');
                        this.annotations = this.annotations.filter((item) => item.id !== annotation.id);
                        this.message = 'Belgi o‘chirildi.';
                    } catch (error) {
                        this.message = error.message || 'O‘chirishda xatolik yuz berdi.';
                    }
                },
            };
        };
    </script>
@endonce
