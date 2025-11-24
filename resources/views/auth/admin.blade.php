<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center">

    <div class="bg-slate-800 p-8 rounded-lg shadow-2xl w-full max-w-sm border border-slate-700">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white uppercase tracking-wider">Admin Control</h2>
            <div class="h-1 w-16 bg-indigo-500 mx-auto mt-2"></div>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500 text-red-400 px-4 py-2 rounded text-sm mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-slate-300 text-xs uppercase font-bold mb-2">Administrator Email</label>
                <input type="email" name="email" required 
                    class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div class="mb-8">
                <label class="block text-slate-300 text-xs uppercase font-bold mb-2">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded text-white focus:outline-none focus:border-indigo-500">
            </div>

            <button type="submit" 
                class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700 transition duration-300">
                ACCESS SYSTEM
            </button>
        </form>
    </div>

</body>
</html>