<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Client::query();
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSortFields = ['first_name', 'last_name', 'email', 'phone', 'last_visit', 'created_at'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }
        
        // Pagination
        $perPage = $request->input('per_page', 15);
        $clients = $query->paginate($perPage);
        
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.clients.create');
    }

    /**
     * Store a newly created client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'marketing_consent' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        Client::create($request->all());
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $client = Client::withCount([
            'appointments as total_appointments' => function($query) {
                $query->where('status', 'completed');
            },
            'payments as total_payments' => function($query) {
                $query->where('status', 'completed');
            }
        ])
        ->with(['appointments' => function($query) {
            $query->where('status', 'completed')
                  ->orderBy('start_time', 'desc')
                  ->take(5);
        }])
        ->findOrFail($id);
        
        // Get spend by category
        $spendByCategory = \App\Models\Appointment::select(
            'services.name as category',
            DB::raw('SUM(appointment_service.price) as total_spent')
        )
        ->join('appointment_service', 'appointments.id', '=', 'appointment_service.appointment_id')
        ->join('services', 'appointment_service.service_id', '=', 'services.id')
        ->where('appointments.client_id', $id)
        ->where('appointments.status', 'completed')
        ->groupBy('services.name')
        ->orderBy('total_spent', 'desc')
        ->get();
        
        // Get payment methods
        $paymentMethods = \App\Models\Payment::select(
            'payment_method',
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->where('client_id', $id)
        ->where('status', 'completed')
        ->groupBy('payment_method')
        ->get();
        
        return view('admin.clients.show', compact(
            'client', 
            'spendByCategory',
            'paymentMethods'
        ));
    }

    /**
     * Show the form for editing the specified client.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $client = Client::findOrFail($id);
        
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'marketing_consent' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $client->update($request->all());
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();
        
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
