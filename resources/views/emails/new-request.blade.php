<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dataset Request Notification</title>
</head>
<body style="margin: 0; padding: 20px; font-family: Arial, sans-serif; background-color: #f3f4f6; color: #1f2937;">
<div style="max-width: 600px; margin: auto; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); text-align: center;">
    <!-- Header -->
    <h1 style="font-size: 24px; font-weight: bold; color: #111827; margin-bottom: 16px;">New Pending Request</h1>

    <!-- Content -->
    <p style="font-size: 16px; line-height: 1.6; color: #374151;">
        A new dataset request has been created with the following details:
    </p>

    <!-- Request Details Box -->
    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 16px; margin: 20px 0; text-align: left;">
        <p style="margin: 8px 0; font-size: 16px; color: #374151;">
            <strong>Request Type:</strong>
            @php
                $typeStyles = [
                    'new' => 'background-color: #d1fae5; color: #065f46;',
                    'reduce' => 'background-color: #fef3c7; color: #92400e;',
                    'delete' => 'background-color: #fee2e2; color: #991b1b;',
                    'extend' => 'background-color: #dbeafe; color: #1e40af;',
                    'edit' => 'background-color: #ede9fe; color: #6b21a8;',
                ];

                $typeStyle = $typeStyles[$requestType] ?? 'background-color: #e5e7eb; color: #374151;';
            @endphp

            <span style="display: inline-block; {{ $typeStyle }} padding: 4px 10px; border-radius: 4px; font-size: 14px; font-weight: 500;">
                {{ ucfirst($requestType) }}
            </span>
        </p>
        <p style="margin: 8px 0; font-size: 16px; color: #374151;">
            <strong>Dataset:</strong> {{ $datasetName }}
        </p>
        <p style="margin: 8px 0; font-size: 16px; color: #374151;">
            <strong>Requested By:</strong> {{ $requestedBy }}
        </p>
    </div>

    <!-- Button -->
    <p>
        <a href="{{ $url }}"
           style="display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; font-size: 16px; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin-top: 16px;">
            Review Request
        </a>
    </p>

    <!-- Wrapped Long URL -->
    <p style="font-size: 14px; color: #6b7280; word-break: break-all; overflow-wrap: break-word; text-align: center; margin-top: 16px;">
        {{ $url }}
    </p>

    <!-- Footer -->
    <p style="font-size: 12px; color: #6b7280; margin-top: 24px;">
        This is an automated notification from the dataset management system.
    </p>
</div>
</body>
</html>
