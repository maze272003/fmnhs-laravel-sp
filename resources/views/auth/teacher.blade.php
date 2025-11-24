<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center border-t-8 border-emerald-600">

    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-emerald-700">Teacher Portal</h2>
            <p class="text-gray-500">Faculty Access Only</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-600 p-3 rounded mb-4 text-sm border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('teacher.login.submit') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email Credentials</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
            </div>

            <button type="submit" 
                class="w-full bg-emerald-600 text-white font-bold py-2 px-4 rounded hover:bg-emerald-700 shadow-md transition duration-300">
                Login to Faculty Dashboard
            </button>
        </form>
    </div>

</body>
</html>