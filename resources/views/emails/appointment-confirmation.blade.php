<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #06A3DA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
        .details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .detail-row { padding: 10px 0; border-bottom: 1px solid #eee; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: bold; color: #06A3DA; }
        .button { display: inline-block; padding: 12px 30px; margin: 10px 5px; background: #06A3DA; color: white; text-decoration: none; border-radius: 5px; }
        .button-success { background: #28a745; }
        .visit-code { font-size: 24px; font-weight: bold; color: #06A3DA; text-align: center; padding: 15px; background: #e3f2fd; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Appointment Confirmation</h1>
    </div>
    
    <div class="content">
        <p>Dear {{ $name }},</p>
        <p>Thank you for booking an appointment with <strong>Klinik Pergigian Helmy</strong>!</p>
        
        <div class="visit-code">
            Visit Code: {{ $appointment->visit_code }}
        </div>
        
        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Service:</span>
                {{ $appointment->service?->service_name ?? 'N/A' }}
            </div>
            <div class="detail-row">
                <span class="detail-label">Dentist:</span>
                {{ $appointment->dentist?->dentist_name ?? 'Available' }}
            </div>
        </div>
        
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $trackingUrl }}" class="button">Track Your Visit</a>
            <a href="{{ $checkinUrl }}" class="button button-success">Check In Now</a>
        </p>
        
        <p><strong>Keep this code safe.</strong> You can use it to track your appointment anytime.</p>
    </div>
    
    <div class="footer">
        <p>If you have any questions or need to reschedule, please contact us:</p>
        <p><strong>Phone:</strong> 06-677 1940 | <strong>WhatsApp:</strong> <a href="https://wa.me/message/PZ6KMZFQVZ22I1">Message us</a></p>
        <p><strong>Klinik Pergigian Helmy</strong></p>
    </div>
</body>
</html>
