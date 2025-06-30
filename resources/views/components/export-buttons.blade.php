@props([
    'type' => 'appointments',
    'formats' => [],
    'sizeClass' => '',
    'title' => 'Export',
    'label' => 'Export',
    'btnClass' => 'btn-outline-secondary',
    'showIcons' => true,
])

<div class="export-buttons">
    <div class="btn-group" role="group" aria-label="Export options">
        <button type="button" 
                class="btn {{ $btnClass }} {{ $sizeClass }} dropdown-toggle d-flex align-items-center" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
                title="{{ $title }}">
            @if($showIcons)
                <i class="fas fa-download me-1"></i>
            @endif
            {{ $label }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li class="dropdown-header text-muted small px-3 mb-1">{{ $title }}</li>
            @foreach($formats as $format => $options)
                <li>
                    <a class="dropdown-item d-flex align-items-center" 
                       href="{{ route($options['route'], $type) }}"
                       @if(isset($options['target'])) target="{{ $options['target'] }}" @endif>
                        <i class="fas fa-{{ $options['icon'] }} me-2 text-{{ $options['class'] }}"></i>
                        {{ $options['label'] }}
                    </a>
                </li>
                @if(!$loop->last)
                    <li><hr class="dropdown-divider my-1"></li>
                @endif
            @endforeach
        </ul>
    </div>
</div>

@push('styles')
<style>
    .export-buttons .btn {
        transition: all 0.2s ease-in-out;
    }
    .export-buttons .dropdown-toggle::after {
        margin-left: 0.5em;
    }
    .export-buttons .dropdown-menu {
        min-width: 180px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
    }
    .export-buttons .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    .export-buttons .dropdown-header {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .export-buttons .dropdown-item:focus,
    .export-buttons .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .export-buttons .dropdown-divider {
        margin: 0.25rem 0;
    }
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .export-buttons .btn {
            width: 100%;
            justify-content: center;
        }
        .export-buttons .dropdown-menu {
            width: 100%;
        }
    }
</style>
@endpush