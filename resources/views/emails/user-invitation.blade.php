<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited to {{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f3f4f6; color: #1f2937;">
<div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); text-align: center;">
    <!-- Header -->
    <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin-bottom: 16px;">You're Invited to {{ config('app.name') }}</h1>

    <!-- Content -->
    <p style="font-size: 16px; line-height: 1.6; color: #374151;">Hello,</p>
    <p style="font-size: 16px; line-height: 1.6; color: #374151;">
        You have been invited to join <strong>{{ config('app.name') }}</strong> as a <strong>{{ $role }}</strong>.
    </p>
    <p style="font-size: 16px; line-height: 1.6; color: #374151;">
        Please click the button below to finish the registration:
    </p>

    <!-- Button -->
    <p>
        <a href="{{ $url }}"
           style="display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; font-size: 16px; padding: 12px 24px; border-radius: 6px; font-weight: bold;">
            Complete Registration
        </a>
    </p>

    <!-- Wrapped Long URL -->
    <p style="font-size: 14px; color: #6b7280; word-break: break-all; overflow-wrap: break-word; text-align: center; margin-top: 16px;">
        {{ $url }}
    </p>

    <!-- Footer -->
    <p style="font-size: 12px; color: #6b7280; margin-top: 24px;">
        If you did not expect this invitation, you can ignore this email.
    </p>
</div>
</body>
</html>
