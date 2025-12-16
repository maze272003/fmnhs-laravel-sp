<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | FMNHS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .login-bg {
            background-image: url('{{ asset("images/bg.jpg") }}'); 
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Mas maliwanag at malinis na overlay */
        .overlay {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(240, 253, 244, 0.7));
        }

        /* Glassmorphism effect para sa login box */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="login-bg h-screen flex items-center justify-center p-4">

    <div class="overlay absolute inset-0"></div>

    <div class="glass-card p-8 md:p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-white relative z-10 transition-all duration-500">
        
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 bg-emerald-500 rounded-full animate-ping opacity-20"></div>
            <img src="{{ asset('images/fmnhs.png') }}" alt="School Logo" 
                 class="relative w-full h-full object-cover rounded-full shadow-xl border-4 border-white z-10">
        </div>

        <div class="text-center mb-10">
            <h2 class="text-3xl font-black text-slate-800 tracking-tight leading-none mb-2 uppercase">Admin Portal</h2>
            <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.3em]">Management System</p>
            <div class="h-1.5 w-12 bg-emerald-500 mx-auto mt-4 rounded-full"></div>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-2xl text-xs font-bold mb-6 flex items-center gap-3 animate-shake">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-slate-400 text-[10px] uppercase font-black tracking-widest mb-2 ml-1">Administrator Email</label>
                <div class="relative group">
                    <input type="email" name="email" required 
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-semibold text-sm"
                        value="{{ old('email') }}" placeholder="admin@fmnhs.edu.ph">
                    <i class="fa-solid fa-envelope absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
                </div>
            </div>

            <div>
                <label class="block text-slate-400 text-[10px] uppercase font-black tracking-widest mb-2 ml-1">Secure Password</label>
                <div class="relative group">
                    <input type="password" name="password" required 
                        class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-semibold text-sm"
                        placeholder="••••••••">
                    <i class="fa-solid fa-lock absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors"></i>
                </div>
            </div>

            <button type="submit" 
                class="w-full bg-slate-900 text-white font-black py-4 px-4 rounded-2xl hover:bg-emerald-600 transition-all duration-300 shadow-xl shadow-slate-200 hover:shadow-emerald-100 active:scale-95 flex items-center justify-center gap-2 mt-8 group">
                <i class="fa-solid fa-shield-halved text-xs group-hover:rotate-12 transition-transform"></i>
                <span class="tracking-tight">AUTHENTICATE & ENTER</span>
            </button>
        </form>
        
        <div class="text-center mt-10">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-emerald-600 text-[11px] font-black uppercase tracking-widest transition-all group">
                <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                Return to Portal Hub
            </a>
        </div>
    </div>

</body>
</html>