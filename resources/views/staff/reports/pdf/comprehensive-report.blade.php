<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprehensive Analytics Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #333;
            line-height: 1.6;
        }
        
        .page {
            page-break-after: always;
            padding: 60px;
            min-height: 800px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 60px;
            padding-bottom: 40px;
            border-bottom: 4px solid #667eea;
        }
        
        .header h1 {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 30px;
        }
        
        .header-info {
            font-size: 32px;
            color: #666;
            margin: 15px 0;
        }
        
        h2 {
            font-size: 72px;
            font-weight: bold;
            color: #667eea;
            margin-top: 50px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        
        h3 {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin-top: 40px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            display: inline-block;
            width: 45%;
            background: #f5f5f5;
            padding: 50px;
            margin: 20px 2%;
            text-align: center;
            border-left: 8px solid #667eea;
            vertical-align: top;
        }
        
        .stat-label {
            font-size: 30px;
            color: #666;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .stat-value {
            font-size: 84px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-box.success .stat-value { color: #10b981; }
        .stat-box.success { border-left-color: #10b981; }
        
        .stat-box.warning .stat-value { color: #f59e0b; }
        .stat-box.warning { border-left-color: #f59e0b; }
        
        .stat-box.danger .stat-value { color: #ef4444; }
        .stat-box.danger { border-left-color: #ef4444; }
        
        .stat-box.info .stat-value { color: #3b82f6; }
        .stat-box.info { border-left-color: #3b82f6; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            font-size: 28px;
        }
        
        table thead tr {
            background: #667eea;
            color: white;
        }
        
        table th {
            padding: 30px;
            text-align: left;
            font-weight: bold;
            font-size: 30px;
        }
        
        table td {
            padding: 26px 30px;
            border-bottom: 1px solid #ddd;
        }
        
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .total-box {
            background: #f0f8f4;
            border-left: 8px solid #10b981;
            padding: 50px;
            margin: 40px 0;
            font-size: 36px;
        }
        
        .total-box .label {
            color: #666;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 32px;
        }
        
        .total-box .value {
            font-size: 76px;
            font-weight: bold;
            color: #10b981;
        }
        
        .note {
            font-size: 26px;
            color: #999;
            margin-top: 20px;
            font-style: italic;
        }
        
        .page-break {
            page-break-after: always;
            margin: 80px 0;
        }
    </style>
</head>
<body>

<!-- PAGE 1: HEADER & APPOINTMENT ANALYSIS -->
<div class="page">
    <div class="header">
        <h1>COMPREHENSIVE ANALYTICS REPORT</h1>
        <div class="header-info">{{ $generatedAt }}</div>
        <div class="header-info">{{ $periodLabel }} Report | {{ $dateFrom }} to {{ $dateTo }}</div>
    </div>
    
    <h2>APPOINTMENT ANALYSIS</h2>
    
    <div style="margin: 40px 0;">
        <div class="stat-box info">
            <div class="stat-label">Total Appointments</div>
            <div class="stat-value">{{ $appointmentStats['total'] }}</div>
        </div>
        <div class="stat-box success">
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ $appointmentStats['completed'] }}</div>
        </div>
    </div>
    
    <div style="margin: 40px 0;">
        <div class="stat-box warning">
            <div class="stat-label">Cancelled</div>
            <div class="stat-value">{{ $appointmentStats['cancelled'] }}</div>
        </div>
        <div class="stat-box danger">
            <div class="stat-label">No Shows</div>
            <div class="stat-value">{{ $appointmentStats['noShow'] }}</div>
        </div>
    </div>
    
    <div style="clear: both;"></div>
    
    <div class="total-box">
        <div class="label">COMPLETION RATE</div>
        <div class="value">{{ $appointmentStats['total'] > 0 ? round(($appointmentStats['completed'] / $appointmentStats['total']) * 100, 1) : 0 }}%</div>
    </div>
</div>

<!-- PAGE 2: REVENUE REPORT -->
<div class="page">
    <h2>REVENUE ANALYSIS</h2>
    
    <h3>Revenue by Service</h3>
    
    @if ($revenueByService->count() > 0)
    <table>
        <thead>
            <tr>
                <th>SERVICE</th>
                <th style="text-align: center;">COUNT</th>
                <th style="text-align: right;">PRICE</th>
                <th style="text-align: right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($revenueByService->take(8) as $service)
            <tr>
                <td>{{ $service->name }}</td>
                <td style="text-align: center;">{{ $service->count }}</td>
                <td style="text-align: right;">{{ number_format($service->price, 2) }}</td>
                <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($service->total_revenue, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="note">No revenue data available</p>
    @endif
    
    <h3>Revenue by Dentist</h3>
    
    @if ($revenueByDentist->count() > 0)
    <table>
        <thead>
            <tr>
                <th>DENTIST</th>
                <th style="text-align: center;">APPTS</th>
                <th style="text-align: right;">REVENUE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($revenueByDentist as $dentist)
            <tr>
                <td>{{ $dentist->name }}</td>
                <td style="text-align: center;">{{ $dentist->completed_appointments }}</td>
                <td style="text-align: right; font-weight: bold; color: #10b981;">{{ number_format($dentist->total_revenue ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="note">No revenue data available</p>
    @endif
    
    <div class="total-box">
        <div class="label">TOTAL REVENUE ({{ $periodLabel }})</div>
        <div class="value">{{ number_format($totalRevenue, 2) }}</div>
    </div>
    
    @if ($appointmentStats['completed'] > 0)
    <div class="total-box" style="border-left-color: #3b82f6; background: #f0f7ff;">
        <div class="label" style="color: #333;">Average per Appointment</div>
        <div class="value" style="color: #3b82f6;">{{ number_format($totalRevenue / $appointmentStats['completed'], 2) }}</div>
    </div>
    @endif
</div>

<!-- PAGE 3: PATIENT RETENTION -->
<div class="page">
    <h2>PATIENT RETENTION</h2>
    
    <div style="margin: 40px 0;">
        <div class="stat-box danger">
            <div class="stat-label">At-Risk Patients</div>
            <div class="stat-value">{{ count($atRiskPatients) }}</div>
        </div>
        <div class="stat-box success">
            <div class="stat-label">Loyal Patients</div>
            <div class="stat-value">{{ count($loyalPatients) }}</div>
        </div>
    </div>
    
    <div style="clear: both;"></div>
    
    <div class="total-box" style="border-left-color: #667eea; background: #f5f5f5;">
        <div class="label">Total Patients</div>
        <div class="value" style="color: #667eea;">{{ $totalPatients }}</div>
    </div>
    
    @if (count($atRiskPatients) > 0)
    <h3>At-Risk Patients (No Recent Visits)</h3>
    
    <table>
        <thead>
            <tr>
                <th>PATIENT NAME</th>
                <th style="text-align: center;">LAST VISIT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($atRiskPatients as $patient)
            <tr>
                <td>{{ $patient['name'] }}</td>
                <td style="text-align: center;">{{ $patient['last_appointment'] ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="note" style="font-size: 24px; margin-top: 40px;">[EXCELLENT] No at-risk patients detected - Excellent retention!</p>
    @endif
    
    <h3>Top Patients</h3>
    
    @if (count($loyalPatients) > 0)
    <table>
        <thead>
            <tr>
                <th>PATIENT NAME</th>
                <th style="text-align: center;">VISITS</th>
            </tr>
        </thead>
        <tbody>
            @foreach (collect($loyalPatients)->take(10) as $patient)
            <tr>
                <td>{{ $patient['name'] }}</td>
                <td style="text-align: center; font-weight: bold; color: #10b981;">{{ $patient['totalAppointments'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @if (count($loyalPatients) > 10)
    <p class="note">... and {{ count($loyalPatients) - 10 }} more loyal patients</p>
    @endif
    @else
    <p class="note">No patient data available</p>
    @endif
</div>

<!-- PAGE 4: SCHEDULING & UTILIZATION -->
<div class="page">
    <h2>SCHEDULING ANALYSIS</h2>
    
    <h3>Hourly Distribution</h3>
    
    @if (count($hourlyDistribution) > 0)
    <table>
        <thead>
            <tr>
                <th>TIME</th>
                <th style="text-align: center;">APPOINTMENTS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hourlyDistribution as $data)
            <tr>
                <td>{{ $data['hour'] }}</td>
                <td style="text-align: center; font-weight: bold;">{{ $data['count'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="note">No hourly distribution data available</p>
    @endif
    
    <h3>Dentist Utilization</h3>
    
    @if (count($dentistUtilization) > 0)
    <table>
        <thead>
            <tr>
                <th>DENTIST</th>
                <th style="text-align: right;">UTILIZATION</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dentistUtilization as $dentist)
            <tr>
                <td>{{ $dentist->name }}</td>
                <td style="text-align: right;">
                    <span style="font-weight: bold; color: #667eea;">{{ $dentist->utilization_percentage }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="note">No utilization data available</p>
    @endif
    
    <h3>Summary Metrics</h3>
    
    <div style="margin: 40px 0;">
        <div class="stat-box info">
            <div class="stat-label">Total Completed</div>
            <div class="stat-value">{{ $appointmentStats['completed'] }}</div>
        </div>
        <div class="stat-box info">
            <div class="stat-label">Active Dentists</div>
            <div class="stat-value">{{ count($dentistUtilization) }}</div>
        </div>
    </div>
    
    <div style="clear: both;"></div>
    
    <div class="total-box">
        <div class="label">Average Utilization</div>
        <div class="value">{{ count($dentistUtilization) > 0 ? round(collect($dentistUtilization)->avg('utilization_percentage'), 1) : 0 }}%</div>
    </div>
    
    <div style="margin-top: 80px; padding-top: 40px; border-top: 2px solid #ddd; text-align: center; font-size: 20px; color: #999;">
        <p>This is an automated comprehensive analytics report</p>
        <p>Generated by Dental Clinic Management System</p>
    </div>
</div>

</body>
</html>
