<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Exports\AppointmentsExport;
use App\Exports\ServicesExport;
use App\Exports\OrdersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    protected $excel;
    /**
     * The valid export types.
     *
     * @var array
     */
    protected $validTypes;

    /**
     * Create a new controller instance.
     *
     * @param  \Maatwebsite\Excel\Excel  $excel
     * @return void
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->validTypes = array_keys(config('export.exportables', []));
    }
    
    /**
     * Check if the export type is valid
     */
    /**
     * Check if the given type is a valid export type.
     *
     * @param  string  $type
     * @return bool
     */
    protected function isValidType($type): bool
    {
        return in_array(Str::lower($type), $this->validTypes);
    }
    
    /**
     * Get the export class for the given type.
     *
     * @param  string  $type
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getExportClass($type)
    {
        $exportClass = config("export.exportables.{$type}");
        
        if (!class_exists($exportClass)) {
            throw new \InvalidArgumentException("Export class [{$exportClass}] does not exist.");
        }
        
        return $exportClass;
    }

    /**
     * Export data to Excel
     *
     * @param string $type Type of data to export (appointments, services, orders)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    /**
     * Export data to Excel.
     *
     * @param  string  $type
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function exportExcel($type): BinaryFileResponse
    {
        if (!$this->isValidType($type)) {
            abort(404, 'Invalid export type');
        }
        
        $filename = $this->generateFilename($type, 'xlsx');
        $exportClass = $this->getExportClass($type);
        
        return $this->excel->download(
            new $exportClass,
            $filename,
            $this->getExcelWriterType('xlsx')
        );
    }

    /**
     * Export data to PDF
     *
     * @param string $type Type of data to export (appointments, services, orders)
     * @return \Illuminate\Http\Response
     */
    /**
     * Export data to PDF.
     *
     * @param  string  $type
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    /**
     * Export data to PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $type
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function exportPdf(Request $request, $type)
    {
        if (!$this->isValidType($type)) {
            return $this->handleErrorResponse($request, 'Invalid export type', 400);
        }
        
        try {
            $exportClass = $this->getExportClass($type);
            $export = new $exportClass();
            
            // Get PDF data and view from the export class if available
            if (method_exists($export, 'getPdfData') && method_exists($export, 'getPdfView')) {
                $data = $export->getPdfData();
                $view = $export->getPdfView();
            } else {
                // Fallback to default implementation
                list($data, $view) = $this->getDefaultPdfDataAndView($type);
            }
            
            // Generate a filename
            $filename = $this->generateFilename($type, 'pdf');
            
            // Get PDF options from config
            $paper = config('export.pdf.paper', 'a4');
            $orientation = config('export.pdf.orientation', 'portrait');
            
            // Create PDF instance
            $pdf = PDF::loadView($view, $data);
            
            // Set paper size and orientation
            $pdf->setPaper($paper, $orientation);
            
            // Set additional PDF options
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => config('export.pdf.font', 'sans-serif'),
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'isJavascriptEnabled' => true,
                'enable_remote' => true,
            ]);
            
            // Set PDF metadata
            $pdf->setOption('title', $data['title'] ?? 'Export');
            $pdf->setOption('author', config('app.name'));
            $pdf->setOption('creator', config('app.name'));
            
            // Stream or download based on request
            if ($request->has('preview')) {
                return $pdf->stream($filename);
            }
            
            return $pdf->download($filename);
                
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return $this->handleErrorResponse($request, 'Failed to generate PDF export', 500, $e);
        }
    }

    /**
     * Get the default data and view for PDF export.
     *
     * @param  string  $type
     * @return array
     */
    protected function getDefaultPdfDataAndView($type)
    {
        $data = [
            'title' => Str::title($type) . ' Report',
            'date' => now()->format('F j, Y'),
        ];
        
        $view = "exports.{$type}";
        
        switch (Str::lower($type)) {
            case 'appointments':
                $data['appointments'] = Appointment::with(['client', 'services', 'staff'])
                    ->latest()
                    ->get();
                $data['totalAppointments'] = $data['appointments']->count();
                break;
                
            case 'services':
                $data['services'] = Service::with('category')
                    ->orderBy('name')
                    ->get();
                $data['totalServices'] = $data['services']->count();
                $data['totalRevenue'] = $data['services']->sum('price');
                break;
                
            case 'orders':
                $data['orders'] = Order::with(['client', 'items.service'])
                    ->latest()
                    ->get();
                $data['totalOrders'] = $data['orders']->count();
                $data['totalRevenue'] = $data['orders']->sum('total');
                $data['totalTax'] = $data['orders']->sum('tax');
                $data['totalDiscount'] = $data['orders']->sum('discount');
                break;
        }
        
        return [$data, $view];
    }
    
    /**
     * Handle error responses consistently.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $message
     * @param  int  $status
     * @param  \Exception|null  $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    protected function handleErrorResponse(Request $request, $message, $status = 500, $exception = null)
    {
        if ($exception) {
            \Log::error('Export Error: ' . $exception->getMessage());
            \Log::error($exception->getTraceAsString());
        }
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error' => $exception && config('app.debug') ? $exception->getMessage() : null,
            ], $status);
        }
        
        return view('errors.export', [
            'message' => $message,
            'error' => $exception && config('app.debug') ? $exception->getMessage() : null,
        ]);
    }
    
    /**
     * Generate a filename for the export.
     *
     * @param  string  $type
     * @param  string  $extension
     * @return string
     */
    protected function generateFilename($type, $extension)
    {
        return sprintf(
            '%s_%s.%s',
            Str::title(Str::snake($type, '_')),
            now()->format('Y-m-d_His'),
            $extension
        );
    }
    
    /**
     * Get the Excel writer type.
     *
     * @param  string  $extension
     * @return string|null
     */
    protected function getExcelWriterType($extension)
    {
        return [
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'html' => \Maatwebsite\Excel\Excel::HTML,
            'pdf' => \Maatwebsite\Excel\Excel::DOMPDF,
            'ods' => \Maatwebsite\Excel\Excel::ODS,
        ][$extension] ?? null;
    }
}
