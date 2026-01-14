<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Flight Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f8f9fa; font-family: Arial, sans-serif;">
    <div style="background-color:#ffffff; margin:30px auto; padding:30px; border-radius:10px; width:90%; max-width:600px; box-shadow:0 2px 10px rgba(14,102,122,0.92);">

        <h2 style="margin-top:0;">Hello {{ $username }},</h2>

        <p style="font-size:16px; color:#333333; line-height:1.5;">
            Thank you for booking your flight with us!<br>
            Your booking has been confirmed. Here are the details:
        </p>

        <p style="font-size:16px; margin: 20px 0 5px 0;"><strong>Booking ID:</strong></p>
        <p style="background-color:#e9ecef; padding:10px 15px; border-radius:5px; font-weight:bold; font-size:18px; display:inline-block;">
            {{ $bookingRefID }}
        </p>

        <p style="margin-top:20px; font-size:16px;"><strong>{{ $ticketMsg }}</strong></p>

        <p style="font-size:16px; color:#333;">
            You can manage your booking or check-in through our website:
            <a href="https://travelandtours.pk/" style="color:#127f9f; text-decoration:none;">https://travelandtours.pk</a>
        </p>

        <p style="font-size:16px; color:#333;">
            If you have any questions, feel free to contact our support team.
        </p>

        <h5 style="font-size:15px; margin-top:30px;">
            <a href="tel:+{{ config('variables.contact.phone') }}" style="color:#127f9f; text-decoration:none;">
                +{{ config('variables.contact.phone') }}
            </a>
            &nbsp;|&nbsp;
            {{ config('variables.contact.name') }}
        </h5>

        <p style="text-align:center; font-size:13px; color:#999999; margin-top:40px;">
            &copy; {{ date('Y') }} travelandtour Inc. All rights reserved.
        </p>
    </div>
</body>
</html>
