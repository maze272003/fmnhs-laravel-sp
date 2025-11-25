<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Class Schedules</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-200">

    @include('components.admin.sidebar')

    <div id="content-wrapper" class="min-h-screen flex flex-col transition-all duration-300 md:ml-20 lg:ml-64">
        <header class="bg-white dark:bg-slate-800 shadow-sm sticky top-0 z-30 px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-bold text-indigo-600">Class Scheduling</h2>
        </header>

        <main class="flex-1 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-lg shadow-sm border border-gray-200 sticky top-24">
                        <h3 class="font-bold text-lg mb-4 border-b pb-2">Assign Class</h3>
                        
                        <form action="{{ route('admin.schedules.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Section</label>
                                <select name="section" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                    @foreach($sections as $sec)
                                        <option value="{{ $sec }}">{{ $sec }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Subject</label>
                                <select name="subject_id" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                    @foreach($subjects as $sub)
                                        <option value="{{ $sub->id }}">{{ $sub->code }} - {{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Assigned Teacher</label>
                                <select name="teacher_id" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                    @foreach($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->last_name }}, {{ $t->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Day</label>
                                    <select name="day" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                        <option>Monday</option><option>Tuesday</option><option>Wednesday</option>
                                        <option>Thursday</option><option>Friday</option><option>MWF</option><option>TTH</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Room</label>
                                    <input type="text" name="room" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Start</label>
                                    <input type="time" name="start_time" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase text-gray-500 mb-1">End</label>
                                    <input type="time" name="end_time" class="w-full p-2 border rounded bg-gray-50 dark:bg-slate-700">
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded hover:bg-indigo-700">Assign Schedule</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-200 uppercase font-bold">
                                <tr>
                                    <th class="p-4">Class Info</th>
                                    <th class="p-4">Schedule</th>
                                    <th class="p-4">Assigned Teacher</th>
                                    <th class="p-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                                @foreach($schedules as $sched)
                                <tr>
                                    <td class="p-4">
                                        <div class="font-bold text-indigo-600">{{ $sched->subject->code }}</div>
                                        <div class="text-xs">{{ $sched->section }}</div>
                                    </td>
                                    <td class="p-4">
                                        <div class="font-bold">{{ $sched->day }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($sched->start_time)->format('g:i A') }} - 
                                            {{ \Carbon\Carbon::parse($sched->end_time)->format('g:i A') }}
                                        </div>
                                    </td>
                                    <td class="p-4 font-medium text-emerald-600">
                                        {{ $sched->teacher->last_name }}, {{ $sched->teacher->first_name }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <form action="{{ route('admin.schedules.destroy', $sched->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button class="text-red-400 hover:text-red-600"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="p-4">{{ $schedules->links() }}</div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="{{ asset('js/sidebar.js') }}"></script>
</body>
</html>