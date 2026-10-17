<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs with filtering and search.
     */
    public function index(Request $request): View
    {
        $query = Faq::query()->with('creator');

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            if ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $faqs = $query->orderBy('sort_order')
                      ->latest('id')
                      ->paginate(15)
                      ->withQueryString();

        $stats = [
            'total'    => Faq::count(),
            'active'   => Faq::where('is_active', true)->count(),
            'inactive' => Faq::where('is_active', false)->count(),
        ];

        return view('admin.faqs.index', compact('faqs', 'stats'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create(): View
    {
        return view('admin.faqs.create');
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'question'   => 'required|string|min:4|max:500',
            'answer'     => 'required|string|min:4',
            'category'   => 'required|string|in:books,points,groups,ai,general',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active'  => 'nullable|boolean',
        ], [
            'question.required' => 'Savol matnini kiriting.',
            'question.min'      => 'Savol kamida 4 ta belgidan iborat bo‘lsin.',
            'answer.required'   => 'Javob matnini kiriting.',
            'answer.min'        => 'Javob kamida 4 ta belgidan iborat bo‘lsin.',
            'category.required' => 'Kategoriyani tanlang.',
        ]);

        Faq::create([
            'question'   => trim($validated['question']),
            'answer'     => trim($validated['answer']),
            'category'   => $validated['category'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.faqs.index')
                         ->with('success', 'Yangi savol-javob muvaffaqiyatli qo‘shildi! ✨');
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $validated = $request->validate([
            'question'   => 'required|string|min:4|max:500',
            'answer'     => 'required|string|min:4',
            'category'   => 'required|string|in:books,points,groups,ai,general',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_active'  => 'nullable|boolean',
        ], [
            'question.required' => 'Savol matnini kiriting.',
            'question.min'      => 'Savol kamida 4 ta belgidan iborat bo‘lsin.',
            'answer.required'   => 'Javob matnini kiriting.',
            'answer.min'        => 'Javob kamida 4 ta belgidan iborat bo‘lsin.',
            'category.required' => 'Kategoriyani tanlang.',
        ]);

        $faq->update([
            'question'   => trim($validated['question']),
            'answer'     => trim($validated['answer']),
            'category'   => $validated['category'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.faqs.index')
                         ->with('success', 'Savol-javob muvaffaqiyatli yangilandi! ✏️');
    }

    /**
     * Toggle active/inactive status of the FAQ.
     */
    public function toggle(Faq $faq): RedirectResponse
    {
        $faq->update(['is_active' => !$faq->is_active]);

        $msg = $faq->is_active ? 'Savol-javob faollashtirildi! 🟢' : 'Savol-javob nofaol holatga o‘tkazildi. ⚪';
        return back()->with('success', $msg);
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')
                         ->with('success', 'Savol-javob muvaffaqiyatli o‘chirildi! 🗑️');
    }
}
