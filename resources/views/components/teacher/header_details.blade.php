<div class="flex items-center gap-4">
    <div class="text-right hidden sm:block">
        <p class="text-sm font-black text-slate-900 leading-none mb-1">
            {{ Auth::guard('teacher')->user()->first_name }} {{ Auth::guard('teacher')->user()->last_name }}
        </p>
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
            {{ Auth::guard('teacher')->user()->department ?? 'Academic Faculty' }}
        </p>
    </div>
    <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold shadow-lg shadow-emerald-100 border-2 border-white">
        {{ substr(Auth::guard('teacher')->user()->first_name, 0, 1) }}
    </div>
</div>