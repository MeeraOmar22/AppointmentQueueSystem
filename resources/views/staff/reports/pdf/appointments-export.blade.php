<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointments Report</title>
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
            background-color: #0066cc;
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
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        
        .info-item {
            font-size: 12px;
        }
        
        .info-item strong {
            color: #0066cc;
        }
        
        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .card {
            flex: 1;
            min-width: 150px;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
        }
        
        .card.total {
            background-color: #e3f2fd;
            border-left: 4px solid #0066cc;
        }
        
        .card.completed {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
        }
        
        .card.cancelled {
            background-color: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        
        .card-value {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        thead {
            background-color: #0066cc;
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
        
        .status-no-show {
            background-color: #ff9800;
            color: white;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }
        
        .total-row {
            font-weight: 600;
            background-color: #f5f5f5;
        }
        
        .total-row td {
            border-top: 2px solid #0066cc;
            border-bottom: 2px solid #0066cc;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Appointments Report</h1>
        <p>Dental Clinic Management System</p>
    </div>
    
    <!-- Report Info -->
    <div class="info-section">
        <div class="info-item">
            <strong>Date Range:</strong> {{ $dateFrom }} to {{ $dateTo }}
        </div>
        <div class="info-item">
            <strong>Generated:</strong> {{ $generatedAt }}
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card total">
            <div>Total Appointments</div>
            <div class="card-value">{{ $totalAppointments }}</div>
        </div>
        <div class="card completed">
            <div>Completed</div>
            <div class="card-value">{{ $completedAppointments }}</div>
        </div>
        <div class="card cancelled">
            <div>Cancelled</div>
            <div class="card-value">{{ $cancelledAppointments }}</div>
        </div>
        <div class="card total">
            <div>Total Revenue</div>
            <div class="card-value">AED {{ number_format($totalRevenue, 2) }}</div>
        </div>
    </div>
    
    <!-- Appointments Table -->
    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Service</th>
                <th>Dentist</th>
                <th>Patient</th>
                <th>Status</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments as $apt)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') }}</td>
                    <td>{{ $apt->service->name ?? 'N/A' }}</td>
                    <td>{{ $apt->dentist->name ?? 'N/A' }}</td>
                    <td>{{ $apt->patient_name ?? 'N/A' }}</td>
                    <td>
                        <span class="status-badge status-{{ str_replace('_', '-', strtolower($apt->status->value ?? $apt->status)) }}">
                            {{ ucfirst(str_replace('_', ' ', $apt->status->value ?? $apt->status)) }}
                        </span>
                    </td>
                    <td style="text-align: right;">AED {{ number_format($apt->service->price ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">No appointments found for the selected period</td>
                </tr>
            @endforelse
            
            @if($appointments->count() > 0)
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">TOTAL:</td>
                    <td style="text-align: right;">AED {{ number_format($totalRevenue, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <!-- Footer -->
    <div class="footer">
        <div>
            <strong>Clinic Management System</strong>
            <p>This document is confidential and generated for administrative purposes only.</p>
        </div>
        <div style="text-align: right;">
            <p>Page 1</p>
        </div>
    </div>
</body>
</html>
