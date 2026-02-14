<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Custom CSS to apply the background image to the body */
        .login-bg {
            /* Use Laravel's asset() helper to get the correct path to the image */
            background-image: url('{{ asset("images/bg.jpg") }}'); 
            background-size: cover; /* Ensures the image covers the whole area */
            background-position: center; /* Centers the image */
            background-repeat: no-repeat;
        }
        /* Optional: Add a dark semi-transparent overlay to help the card stand out */
        .overlay {
            background-color: rgba(0, 0, 0, 0.4); 
        }
    </style>
</head>
<body class="login-bg h-screen flex items-center justify-center">

    <div class="overlay absolute inset-0"></div>

    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md border border-gray-200 relative z-10">
        
        <div class="w-20 h-20 mx-auto mb-4">
            <img src="{{ asset('images/fmnhs.png') }}" alt="School Logo" class="w-full h-full object-cover rounded-full shadow-lg border-2 border-blue-500 p-0.5">
        </div>
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-800">Student Portal</h2>
            <p class="text-gray-500 mt-1 text-sm font-medium">Please sign in to view your records</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                <ul class="list-disc list-inside inline">
                    @foreach ($errors->all() as $error)
                        <li class="inline">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="{{ old('email') }}">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2 uppercase tracking-wider">Password</label>
                <input type="password" name="password" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" 
                class="w-full bg-blue-600 text-white font-bold py-2.5 px-4 rounded-lg hover:bg-blue-700 transition duration-300 shadow-md">
                <i class="fa-solid fa-arrow-right-to-bracket mr-2"></i>
                LOGIN AS STUDENT
            </button>
        </form>

        @include('partials.recaptcha')
        
        <div class="text-center mt-6">
            <a href="{{ url('/') }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium transition-colors">
                &larr; Return to Portal Hub
            </a>
        </div>
    </div>

</body>
</html>