<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-indigo-600">Bulletin Board</h2>
        </header>

        <main class="flex-1 p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-1">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 sticky top-24">
                        <h3 class="font-bold text-lg mb-4">Post New Update</h3>
                        <form action="{{ route('teacher.announcements.store') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-bold mb-2">Title</label>
                                <input type="text" name="title" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600" placeholder="e.g. No Classes on Monday">
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-bold mb-2">Content</label>
                                <textarea name="content" rows="4" required class="w-full p-2 border rounded dark:bg-slate-700 dark:border-slate-600" placeholder="Write details here..."></textarea>
                            </div>
                            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded hover:bg-indigo-700">Post Now</button>
                        </form>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <h3 class="font-bold text-lg mb-4">Recent Announcements</h3>
                    <div class="space-y-4">
                        @foreach($announcements as $post)
                            <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 relative group">
                                <div class="flex justify-between">
                                    <h4 class="font-bold text-xl">{{ $post->title }}</h4>
                                    <form action="{{ route('admin.announcements.destroy', $post->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-400 hover:text-red-500"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                                <p class="text-sm text-gray-500 mb-2">By: {{ $post->author_name }} • {{ $post->created_at->format('M d, Y h:i A') }}</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ $post->content }}</p>
                            </div>
                        @endforeach
                        {{ $announcements->links() }}
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>