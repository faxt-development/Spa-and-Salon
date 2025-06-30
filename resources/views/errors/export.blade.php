<!DOCTYPE html>
<html>
<head>
    <title>Export Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .error-container {
            max-width: 600px;
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .error-icon {
            font-size: 64px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            color: #dc3545;
            margin-bottom: 20px;
        }
        .btn-retry {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h2 class="error-title">Export Failed</h2>
        <p class="lead">{{ $message ?? 'An error occurred while generating the export.' }}</p>
        <p>Please try again or contact support if the problem persists.</p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <button onclick="window.history.back()" class="btn btn-primary btn-lg px-4 gap-3">
                <i class="fas fa-arrow-left me-2"></i> Go Back
            </button>
            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-sync-alt me-2"></i> Try Again
            </button>
        </div>
    </div>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
