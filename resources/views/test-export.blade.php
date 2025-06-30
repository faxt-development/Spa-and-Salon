<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .export-section {
            margin: 2rem 0;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }
        .btn-export {
            min-width: 120px;
            margin: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Export Test Page</h1>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="export-section">
            <h2>Appointments</h2>
            <div class="btn-group">
                <a href="{{ route('export.excel', 'appointments') }}" class="btn btn-success btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('export.pdf', 'appointments') }}" class="btn btn-danger btn-export">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('export.preview', 'appointments') }}" class="btn btn-info btn-export" target="_blank">
                    <i class="fas fa-eye"></i> Preview PDF
                </a>
            </div>
        </div>
        
        <div class="export-section">
            <h2>Services</h2>
            <div class="btn-group">
                <a href="{{ route('export.excel', 'services') }}" class="btn btn-success btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('export.pdf', 'services') }}" class="btn btn-danger btn-export">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('export.preview', 'services') }}" class="btn btn-info btn-export" target="_blank">
                    <i class="fas fa-eye"></i> Preview PDF
                </a>
            </div>
        </div>
        
        <div class="export-section">
            <h2>Orders</h2>
            <div class="btn-group">
                <a href="{{ route('export.excel', 'orders') }}" class="btn btn-success btn-export">
                    <i class="fas fa-file-excel"></i> Export Excel
                </a>
                <a href="{{ route('export.pdf', 'orders') }}" class="btn btn-danger btn-export">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
                <a href="{{ route('export.preview', 'orders') }}" class="btn btn-info btn-export" target="_blank">
                    <i class="fas fa-eye"></i> Preview PDF
                </a>
            </div>
        </div>
        
        <div class="export-section">
            <h2>Using Export Buttons Component</h2>
            <div class="mt-3">
                <h4>Appointments</h4>
                <x-export-buttons type="appointments" />
            </div>
            <div class="mt-3">
                <h4>Services</h4>
                <x-export-buttons type="services" buttonClass="btn-sm" />
            </div>
            <div class="mt-3">
                <h4>Orders</h4>
                <x-export-buttons type="orders" buttonClass="btn-outline-secondary" showIcon="true" />
            </div>
        </div>
    </div>
    
    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
