<!DOCTYPE html>
<html>
<head>
    <title>Account Created</title>
</head>
<body>
    <h1>Welcome, {{ $student->first_name }}!</h1>
    <p>Your account has been successfully created.</p>
    <p><strong>Login Details:</strong></p>
    <ul>
        <li><strong>Email:</strong> {{ $student->email }}</li>
        <li><strong>Password:</strong> Your default password is your LRN. Please contact your administrator if you need assistance.</li>
    </ul>
    <p>Please log in and change your password immediately.</p>
</body>
</html>