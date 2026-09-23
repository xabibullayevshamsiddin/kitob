@extends('teacher.layouts.app')
@section('title', 'O\'quvchilar')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-800 dark:text-white">🎓 O'quvchilar</h2>
            <p class="text-sm text-slate-500">{{ $students->total() }} ta o'quvchi ro'yxatda</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">O'quvchi</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ballar</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ketma-ketlik</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Qo'shilgan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($students as $student)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $student->avatar_url }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $student->name }}</p>
                                        <p class="text-xs text-slate-400">@{{ $student->username }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($student->total_points) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-amber-500">🔥 {{ $student->current_streak }} kun</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $student->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">Hali o'quvchilar yo'q</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-700">
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
