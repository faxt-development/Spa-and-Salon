<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class ExportButtons extends Component
{
    /**
     * The type of data to export (appointments, services, orders).
     *
     * @var string
     */
    public $type;

    /**
     * The button label text.
     *
     * @var string
     */
    public $label;

    /**
     * The button CSS class.
     *
     * @var string
     */
    public $buttonClass;

    /**
     * Whether to show icons in the buttons.
     *
     * @var bool
     */
    public $showIcon;

    /**
     * The size of the buttons (sm, md, lg).
     *
     * @var string
     */
    public $size;

    /**
     * Available export formats.
     *
     * @var array
     */
    protected $formats = [
        'excel' => [
            'label' => 'Excel',
            'icon' => 'file-excel',
            'class' => 'btn-success',
            'route' => 'export.excel',
        ],
        'pdf' => [
            'label' => 'PDF',
            'icon' => 'file-pdf',
            'class' => 'btn-danger',
            'route' => 'export.pdf',
        ],
        'preview' => [
            'label' => 'Preview',
            'icon' => 'eye',
            'class' => 'btn-info',
            'route' => 'export.preview',
            'target' => '_blank',
        ],
    ];

    /**
     * Create a new component instance.
     *
     * @param  string  $type  The type of data to export (appointments, services, orders)
     * @param  string  $label  The button label text
     * @param  string  $buttonClass  The button CSS class
     * @param  bool  $showIcon  Whether to show icons in the buttons
     * @param  string  $size  The size of the buttons (sm, md, lg)
     * @return void
     */
    public function __construct(
        string $type,
        string $label = 'Export',
        string $buttonClass = 'btn-brand-primary',
        bool $showIcon = true,
        string $size = 'md'
    ) {
        $this->type = $type;
        $this->label = $label;
        $this->buttonClass = $buttonClass;
        $this->showIcon = $showIcon;
        $this->size = in_array($size, ['sm', 'md', 'lg']) ? $size : 'md';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.export-buttons', [
            'formats' => $this->formats,
            'sizeClass' => $this->size !== 'md' ? 'btn-' . $this->size : '',
            'title' => Str::title($this->type) . ' Export',
        ]);
    }
}
