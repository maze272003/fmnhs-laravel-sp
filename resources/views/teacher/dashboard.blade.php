<h1>Welcome to the teacher Dashboard</h1>
<p>You are logged in as {{ auth()->user()->name }}</p>
<form action="{{ route('teacher.logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>