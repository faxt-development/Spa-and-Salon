<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Services Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: right; font-size: 10px; margin-top: 20px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Services Report</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Category</th>
                <th class="text-right">Price</th>
                <th>Duration (min)</th>
                <th>Active</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->category->name ?? 'Uncategorized' }}</td>
                    <td class="text-right">{{ number_format($service->price, 2) }}</td>
                    <td>{{ $service->duration }}</td>
                    <td>{{ $service->is_active ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Page {PAGE_NUM} of {PAGE_COUNT}</p>
    </div>
</body>
</html>
