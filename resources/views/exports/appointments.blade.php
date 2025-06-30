<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Appointments Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: right; font-size: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Appointments Report</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Staff</th>
                <th>Services</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->id }}</td>
                    <td>{{ $appointment->client->name ?? 'N/A' }}</td>
                    <td>{{ $appointment->staff->name ?? 'N/A' }}</td>
                    <td>{{ $appointment->services->pluck('name')->implode(', ') }}</td>
                    <td>{{ $appointment->start_time->format('Y-m-d H:i') }}</td>
                    <td>{{ $appointment->end_time->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst($appointment->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>
</html>
