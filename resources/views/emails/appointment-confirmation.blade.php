<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Appointment Confirmed - Klinik Pergigian Helmy</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            color: #2c3e50;
            line-height: 1.6;
        }
        .wrapper { width: 100%; background-color: #f5f5f5; padding: 20px 0; }
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #06A3DA 0%, #0582b8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .header-subtext {
            font-size: 14px;
            opacity: 0.95;
            font-weight: 300;
        }
        .content { 
            padding: 40px 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 24px;
            color: #2c3e50;
        }
        .greeting strong {
            color: #06A3DA;
        }
        .primary-action {
            background-color: #f0f8ff;
            border-left: 4px solid #06A3DA;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            text-align: center;
        }
        .primary-action p {
            font-size: 14px;
            color: #555;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            padding: 14px 40px;
            background: #06A3DA;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #0582b8;
            text-decoration: none;
        }
        .appointment-details {
            background-color: #fafbfc;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 24px;
            margin: 30px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e1e4e8;
            font-size: 14px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #06A3DA;
            min-width: 100px;
        }
        .detail-value {
            color: #2c3e50;
            text-align: right;
        }
        .important-note {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 13px;
            color: #856404;
            display: flex;
            gap: 10px;
        }
        .important-note::before {
            content: "⏰";
            font-size: 16px;
            flex-shrink: 0;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e1e4e8;
        }
        .footer-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .footer-contact {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }
        .footer-contact a {
            color: #06A3DA;
            text-decoration: none;
        }
        .footer-contact a:hover {
            text-decoration: underline;
        }
        .clinic-name {
            font-size: 14px;
            font-weight: 700;
            color: #06A3DA;
            margin-top: 16px;
            letter-spacing: 0.3px;
        }
        .divider {
            height: 1px;
            background-color: #e1e4e8;
            margin: 24px 0;
        }
        @media (max-width: 600px) {
            .container { margin: 0; border-radius: 0; }
            .content { padding: 24px 20px; }
            .header { padding: 30px 20px; }
            .header h1 { font-size: 24px; }
            .detail-row { flex-direction: column; }
            .detail-label { min-width: 0; }
            .detail-value { text-align: left; margin-top: 4px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>✓ Appointment Confirmed</h1>
                <div class="header-subtext">Your booking is secured</div>
            </div>

            <!-- Main Content -->
            <div class="content">
                <!-- Greeting -->
                <div class="greeting">
                    Hello <strong>{{ $name }}</strong>,<br>
                    Thank you for booking with us! Your appointment is confirmed.
                </div>

                <!-- Appointment Details -->
                <div class="appointment-details">
                    <div class="detail-row">
                        <span class="detail-label">📅 Date</span>
                        <span class="detail-value"><strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">⏰ Time</span>
                        <span class="detail-value"><strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">🦷 Service</span>
                        <span class="detail-value">{{ $appointment->service?->name ?? 'To be confirmed' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">👨‍⚕️ Dentist</span>
                        <span class="detail-value">{{ $appointment->dentist?->name ?? 'Will be assigned' }}</span>
                    </div>
                </div>

                <!-- Important Reminder -->
                <div class="important-note">
                    Arrive 5-10 minutes early for a smooth check-in process.
                </div>

                <!-- Primary Call-to-Action -->
                <div class="primary-action">
                    <p style="font-weight: 600; color: #2c3e50; margin-bottom: 12px;">Ready to track your appointment?</p>
                    <a href="{{ $trackingUrl }}" class="button">→ Track Your Visit</a>
                    <p style="font-size: 12px; margin-top: 12px; color: #999;">Save this link - no login required</p>
                </div>

                <div class="divider"></div>

                <!-- Support Section -->
                <p style="font-size: 14px; color: #2c3e50; margin-bottom: 16px;">
                    <strong>Questions?</strong> We're here to help. You can:
                </p>
                <ul style="font-size: 13px; color: #666; padding-left: 20px; margin-bottom: 20px;">
                    <li style="margin-bottom: 8px;">Call us: <strong>06-677 1940</strong></li>
                    <li style="margin-bottom: 8px;">Message us on <a href="https://wa.me/message/PZ6KMZFQVZ22I1" style="color: #06A3DA; text-decoration: none;"><strong>WhatsApp</strong></a></li>
                    <li>Reply to this email with your concerns</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div class="clinic-name">KLINIK PERGIGIAN HELMY</div>
                <div style="font-size: 12px; color: #999; margin-top: 12px;">
                    Professional Dental Care
                </div>
                <div style="font-size: 11px; color: #bbb; margin-top: 16px;">
                    This is an automated message. Please do not reply with sensitive information.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
