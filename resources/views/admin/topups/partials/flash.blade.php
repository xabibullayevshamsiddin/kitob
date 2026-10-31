@if(session('success'))
    <div role="status" class="rounded-panel border border-emerald-500/25 bg-emerald-500/10 p-3 text-sm text-emerald-300">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div role="alert" class="rounded-panel border border-rose-500/25 bg-rose-500/10 p-3 text-sm text-rose-300">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div role="alert" class="rounded-panel border border-rose-500/25 bg-rose-500/10 p-3 text-sm text-rose-300">{{ $errors->first() }}</div>
@endif
