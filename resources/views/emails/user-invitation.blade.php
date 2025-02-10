<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited to {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .content {
            margin-top: 20px;
            font-size: 16px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">You're Invited to {{ config('app.name') }}</div>
    <div class="content">
        <p>Hello,</p>
        <p>You have been invited to join <strong>{{ config('app.name') }}</strong> as a <strong>{{ $role }}</strong>.</p>
        <p>Your temporary password: <strong>{{ $password }}</strong></p>
        <p>Please click the button below to log in and change your password.</p>
        <p style="text-align: center;"><a href="{{ url('/login') }}" class="btn">Login Here</a></p>
    </div>
    <div class="footer">
        If you did not expect this invitation, you can ignore this email.
    </div>
</div>
</body>
</html>
