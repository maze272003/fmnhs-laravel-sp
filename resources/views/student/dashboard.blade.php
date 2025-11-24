<h1>Welcome to the Student Dashboard</h1>
<p>You are logged in as {{ auth()->user()->name }}</p>
{{-- add logout button end session --}}
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button type="submit">Logout</button>
</form>