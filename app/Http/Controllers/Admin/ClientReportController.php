<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientReportController extends Controller
{
    /**
     * Display client spend reports
     */
    public function index()
    {
        $clients = Client::withCount([
            'appointments as completed_appointments' => function($query) {
                $query->where('status', 'completed');
            }
        ])
        ->addSelect([
            'total_spent' => Payment::selectRaw('COALESCE(SUM(amount), 0)')
                ->whereColumn('appointments.client_id', 'clients.id')
                ->where('payments.status', 'completed')
                ->join('appointments', 'appointments.id', '=', 'payments.appointment_id')
                ->groupBy('appointments.client_id')
        ])
        ->orderBy('total_spent', 'desc')
        ->paginate(15);

        // Calculate metrics for the dashboard
        $metrics = [
            'total_clients' => Client::count(),
            'total_spend' => Client::sum('total_spent'),
            'avg_spend_per_client' => Client::avg('total_spent'),
            'avg_visits' => Client::avg('visit_count'),
        ];

        // Get spend trend data for the chart
        $spendTrends = $this->getSpendTrends();

        return view('admin.reports.clients.index', compact('clients', 'metrics', 'spendTrends'));
    }

    /**
     * Export client spend report
     */
    public function export(Request $request)
    {
        $clients = Client::withCount([
            'appointments as completed_appointments' => function($query) {
                $query->where('status', 'completed');
            }
        ])
        ->withSum(['payments as total_spent' => function($query) {
            $query->where('status', 'completed');
        }], 'amount')
        ->orderBy('total_spent', 'desc')
        ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="client_spend_report_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Client',
                'Email',
                'Phone',
                'Total Spent',
                'Total Visits',
                'Avg. Spend/Visit',
                'First Visit',
                'Last Visit'
            ]);

            // Add data rows
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->full_name,
                    $client->email,
                    $client->phone,
                    $client->total_spent,
                    $client->completed_appointments,
                    $client->completed_appointments > 0 ? $client->total_spent / $client->completed_appointments : 0,
                    $client->first_visit_at?->format('Y-m-d') ?? 'N/A',
                    $client->last_visit?->format('Y-m-d') ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get spend trends data for charts
     */
    protected function getSpendTrends()
    {
        $endDate = now();
        $startDate = $endDate->copy()->subMonths(11);

        // Get monthly spend data
        $monthlySpend = \App\Models\Payment::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->where('status', 'completed')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        // Format data for chart
        $labels = [];
        $data = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $month = $currentDate->format('M Y');
            $labels[] = $month;

            $found = $monthlySpend->first(function($item) use ($currentDate) {
                return $item->year == $currentDate->year && $item->month == $currentDate->month;
            });

            $data[] = $found ? (float) $found->total : 0;
            $currentDate->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
}
