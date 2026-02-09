<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Queue Analytics Report</title>
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
        
        .card.analytics {
            background-color: #e0efff;
            border-left: 4px solid #0066cc;
        }
        
        .card-value {
            font-size: 20px;
            font-weight: bold;
            margin: 5px 0;
            color: #0066cc;
        }
        
        .section-title {
            background-color: #0066cc;
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
        
        .number {
            text-align: right;
        }
        
        .total-row {
            font-weight: 600;
            background-color: #f5f5f5;
        }
        
        .total-row td {
            border-top: 2px solid #0066cc;
            border-bottom: 2px solid #0066cc;
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
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Queue Analytics Report</h1>
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
    
    <!-- Wait Time Analysis Summary -->
    <div class="section-title">Wait Time Analysis</div>
    <div class="summary-cards">
        <div class="card analytics">
            <div>Average Wait Time</div>
            <div class="card-value">{{ $waitTimeAnalysis['average_wait_time'] }} min</div>
        </div>
        <div class="card analytics">
            <div>Median Wait Time</div>
            <div class="card-value">{{ $waitTimeAnalysis['median_wait_time'] }} min</div>
        </div>
        <div class="card analytics">
            <div>Max Wait Time</div>
            <div class="card-value">{{ $waitTimeAnalysis['max_wait_time'] }} min</div>
        </div>
        <div class="card analytics">
            <div>Total Appointments</div>
            <div class="card-value">{{ $waitTimeAnalysis['total_appointments'] }}</div>
        </div>
    </div>
    
    <!-- Wait Time by Service -->
    <div class="section-title">Wait Time by Service</div>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="number">Count</th>
                <th class="number">Average Wait</th>
                <th class="number">Min Wait</th>
                <th class="number">Max Wait</th>
            </tr>
        </thead>
        <tbody>
            @forelse($waitTimeAnalysis['appointments_by_service'] as $service)
                <tr>
                    <td>{{ $service['service_name'] }}</td>
                    <td class="number">{{ $service['count'] }}</td>
                    <td class="number">{{ $service['avg_wait'] }} min</td>
                    <td class="number">{{ $service['min_wait'] }} min</td>
                    <td class="number">{{ $service['max_wait'] }} min</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Treatment Duration Analysis Summary -->
    <div class="section-title">Treatment Duration Analysis</div>
    @if($treatmentAnalysis['total_completed'] > 0)
        <div class="summary-cards">
            <div class="card analytics">
                <div>Average Actual Duration</div>
                <div class="card-value">{{ $treatmentAnalysis['average_actual'] }} min</div>
            </div>
            <div class="card analytics">
                <div>Average Estimated Duration</div>
                <div class="card-value">{{ $treatmentAnalysis['average_estimated'] }} min</div>
            </div>
            <div class="card analytics">
                <div>Accurate Estimates</div>
                <div class="card-value">{{ $treatmentAnalysis['accuracy_metrics']['accurate_percent'] }}%</div>
            </div>
            <div class="card analytics">
                <div>Total Completed</div>
                <div class="card-value">{{ $treatmentAnalysis['total_completed'] }}</div>
            </div>
        </div>
        
        <!-- Treatment by Service -->
        <div class="section-title">Treatment Duration by Service</div>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="number">Count</th>
                    <th class="number">Estimated Avg</th>
                    <th class="number">Actual Avg</th>
                    <th class="number">Variance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($treatmentAnalysis['durations_by_service'] as $service)
                    <tr>
                        <td>{{ $service['service_name'] }}</td>
                        <td class="number">{{ $service['count'] }}</td>
                        <td class="number">{{ $service['estimated_avg'] }} min</td>
                        <td class="number">{{ $service['actual_avg'] }} min</td>
                        <td class="number">
                            @if($service['variance_avg'] > 0)
                                <span class="badge badge-warning">+{{ $service['variance_avg'] }} min</span>
                            @else
                                <span class="badge badge-success">{{ $service['variance_avg'] }} min</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <p style="color: #999; text-align: center;">No completed treatments in the selected period.</p>
    @endif
    
    <!-- Queue Efficiency Summary -->
    <div class="section-title">Queue Efficiency</div>
    <div class="summary-cards">
        <div class="card analytics">
            <div>On-Time Completion</div>
            <div class="card-value">{{ $queueEfficiency['on_time_percent'] }}%</div>
        </div>
        <div class="card analytics">
            <div>Early Completion</div>
            <div class="card-value">{{ $queueEfficiency['early_percent'] }}%</div>
        </div>
        <div class="card analytics">
            <div>Late Completion</div>
            <div class="card-value">{{ $queueEfficiency['late_percent'] }}%</div>
        </div>
        <div class="card analytics">
            <div>Total Completed</div>
            <div class="card-value">{{ $queueEfficiency['total_completed'] }}</div>
        </div>
    </div>
    
    <!-- Completion Status Table -->
    <div class="section-title">Completion Status Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="number">Count</th>
                <th class="number">Percentage</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><span class="badge badge-success">On Time</span></td>
                <td class="number">{{ $queueEfficiency['on_time_count'] }}</td>
                <td class="number">{{ $queueEfficiency['on_time_percent'] }}%</td>
            </tr>
            <tr>
                <td><span class="badge badge-success">Early</span></td>
                <td class="number">{{ $queueEfficiency['early_completions'] }}</td>
                <td class="number">{{ $queueEfficiency['early_percent'] }}%</td>
            </tr>
            <tr>
                <td><span class="badge badge-danger">Late</span></td>
                <td class="number">{{ $queueEfficiency['late_completions'] }}</td>
                <td class="number">{{ $queueEfficiency['late_percent'] }}%</td>
            </tr>
            @if($queueEfficiency['total_completed'] > 0)
                <tr class="total-row">
                    <td style="text-align: right;">TOTAL:</td>
                    <td class="number">{{ $queueEfficiency['total_completed'] }}</td>
                    <td class="number">100%</td>
                </tr>
            @endif
        </tbody>
    </table>
    
    <!-- Room Utilization Summary -->
    @if($roomUtilization && count($roomUtilization) > 0)
        <div class="section-title">Room Utilization</div>
        <table>
            <thead>
                <tr>
                    <th>Room</th>
                    <th class="number">Total Appointments</th>
                    <th class="number">Average Duration</th>
                    <th class="number">Utilization Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomUtilization as $room)
                    <tr>
                        <td>{{ $room['room_number'] ?? 'Unknown' }}</td>
                        <td class="number">{{ $room['total_appointments'] ?? 0 }}</td>
                        <td class="number">{{ round($room['average_duration'] ?? 0, 1) }} min</td>
                        <td class="number">{{ round($room['utilization_rate'] ?? 0, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    
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
