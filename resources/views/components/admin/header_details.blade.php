<div class="flex items-center gap-3">
    <div class="text-right hidden sm:block">
        <p class="text-xs font-black text-slate-800 uppercase leading-none">{{ Auth::guard('admin')->user()->name }}</p>
        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-widest mt-1">Administrator</p>
    </div>
    <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold shadow-lg shadow-slate-200">
        {{ substr(Auth::guard('admin')->user()->name, 0, 1) }}
    </div>
</div>