<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Revenue Report</title>
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
            background-color: #2e7d32;
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
            color: #2e7d32;
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
        
        .card.revenue {
            background-color: #e8f5e9;
            border-left: 4px solid #2e7d32;
        }
        
        .card-value {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            color: #2e7d32;
        }
        
        .section-title {
            background-color: #2e7d32;
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
            background-color: #2e7d32;
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
        
        .number {
            text-align: right;
        }
        
        .total-row {
            font-weight: 600;
            background-color: #f5f5f5;
        }
        
        .total-row td {
            border-top: 2px solid #2e7d32;
            border-bottom: 2px solid #2e7d32;
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Revenue Report</h1>
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
        <div class="card revenue">
            <div>Total Revenue</div>
            <div class="card-value">AED {{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card revenue">
            <div>Total Appointments</div>
            <div class="card-value">{{ $totalAppointments }}</div>
        </div>
        <div class="card revenue">
            <div>Average per Appointment</div>
            <div class="card-value">AED {{ number_format($averagePerAppointment, 2) }}</div>
        </div>
    </div>
    
    <!-- Revenue by Service -->
    <div class="section-title">Revenue by Service</div>
    <table>
        <thead>
            <tr>
                <th>Service Name</th>
                <th class="number">Unit Price</th>
                <th class="number">Count</th>
                <th class="number">Total Revenue</th>
                <th class="number">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenueByService as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td class="number">AED {{ number_format($service->price, 2) }}</td>
                    <td class="number">{{ $service->count }}</td>
                    <td class="number">AED {{ number_format($service->total_revenue, 2) }}</td>
                    <td class="number">{{ $totalRevenue > 0 ? number_format(($service->total_revenue / $totalRevenue) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">No data available</td>
                </tr>
            @endforelse
            
            @if($revenueByService->count() > 0)
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">TOTAL:</td>
                    <td class="number">AED {{ number_format($totalRevenue, 2) }}</td>
                    <td class="number">100%</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <!-- Revenue by Dentist -->
    <div class="section-title">Revenue Contribution by Dentist</div>
    <table>
        <thead>
            <tr>
                <th>Dentist Name</th>
                <th class="number">Completed Appointments</th>
                <th class="number">Total Revenue</th>
                <th class="number">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($revenueByDentist as $dentist)
                <tr>
                    <td>{{ $dentist->name }}</td>
                    <td class="number">{{ $dentist->completed_appointments }}</td>
                    <td class="number">AED {{ number_format($dentist->total_revenue ?? 0, 2) }}</td>
                    <td class="number">{{ $totalRevenue > 0 ? number_format((($dentist->total_revenue ?? 0) / $totalRevenue) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">No data available</td>
                </tr>
            @endforelse
            
            @if($revenueByDentist->count() > 0)
                <tr class="total-row">
                    <td style="text-align: right;">TOTAL:</td>
                    <td class="number">{{ $totalAppointments }}</td>
                    <td class="number">AED {{ number_format($totalRevenue, 2) }}</td>
                    <td class="number">100%</td>
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
