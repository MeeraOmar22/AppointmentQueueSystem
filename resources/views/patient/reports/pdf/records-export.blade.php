<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Medical Records</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background-color: #06a3da;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        
        .patient-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        
        .info-item {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .info-item strong {
            color: #06a3da;
            display: block;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .card {
            flex: 1;
            min-width: 140px;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
        }
        
        .card.primary {
            background-color: #e3f2fd;
            border-left: 4px solid #06a3da;
        }
        
        .card-value {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            color: #06a3da;
        }
        
        .section-title {
            background-color: #06a3da;
            color: white;
            padding: 10px 15px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        thead {
            background-color: #06a3da;
            color: white;
        }
        
        th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .status-completed {
            background-color: #4caf50;
            color: white;
        }
        
        .status-cancelled {
            background-color: #f44336;
            color: white;
        }
        
        .number {
            text-align: right;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
        
        .disclaimer {
            background-color: #fff3e0;
            padding: 10px;
            border-left: 4px solid #ff9800;
            margin-top: 15px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Medical Records</h1>
        <p>Personal Appointment & Treatment History</p>
    </div>
    
    <!-- Patient Information -->
    <div class="patient-info">
        <div class="info-item">
            <strong>Patient Name</strong>
            {{ $user->name }}
        </div>
        <div class="info-item">
            <strong>Email</strong>
            {{ $user->email }}
        </div>
        <div class="info-item">
            <strong>Phone</strong>
            {{ $user->phone ?? 'N/A' }}
        </div>
        <div class="info-item">
            <strong>Generated</strong>
            {{ $generatedAt }}
        </div>
    </div>
    
    <!-- Summary -->
    <div class="summary-cards">
        <div class="card primary">
            <div>Total Appointments</div>
            <div class="card-value">{{ $totalAppointments }}</div>
        </div>
        <div class="card primary">
            <div>Completed</div>
            <div class="card-value">{{ $completedAppointments }}</div>
        </div>
        <div class="card primary">
            <div>Cancelled</div>
            <div class="card-value">{{ $cancelledAppointments }}</div>
        </div>
        <div class="card primary">
            <div>Total Cost</div>
            <div class="card-value">AED {{ number_format($totalCost, 2) }}</div>
        </div>
    </div>
    
    <!-- All Appointments -->
    <div class="section-title">All Appointments</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Service</th>
                <th>Dentist</th>
                <th>Status</th>
                <th class="number">Cost</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $apt)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') }}</td>
                    <td>{{ $apt->service->name ?? 'N/A' }}</td>
                    <td>{{ $apt->dentist->name ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge status-{{ str_replace('_', '-', strtolower($apt->status->value ?? $apt->status)) }}">
                            {{ ucfirst(str_replace('_', ' ', $apt->status->value ?? $apt->status)) }}
                        </span>
                    </td>
                    <td class="number">AED {{ number_format($apt->service->price ?? 0, 2) }}</td>
                    <td>{{ Str::limit($apt->notes ?? 'N/A', 20) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">No appointments found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Completed Treatments -->
    @if($treatments->count() > 0)
        <div class="section-title">Treatment History (Completed)</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Dentist</th>
                    <th class="number">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($treatments as $treatment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($treatment->appointment_date)->format('M d, Y') }}</td>
                        <td>{{ $treatment->service->name ?? 'N/A' }}</td>
                        <td>{{ $treatment->dentist->name ?? 'N/A' }}</td>
                        <td class="number">AED {{ number_format($treatment->service->price ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>Clinic Management System</strong></p>
        <p>This document is your personal medical record and contains confidential information.</p>
        <p>Please keep it secure and do not share with unauthorized parties.</p>
    </div>
    
    <div class="disclaimer">
        <strong>Important Notice:</strong> This document is intended for your personal records and reference purposes only. For any medical concerns, please consult with your dentist or healthcare provider.
    </div>
</body>
</html>
