<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Input extends Component
{
    /**
     * The input type.
     *
     * @var string
     */
    public $type;

    /**
     * The input name.
     *
     * @var string
     */
    public $name;

    /**
     * The input value.
     *
     * @var string
     */
    public $value;

    /**
     * Whether the input is required.
     *
     * @var bool
     */
    public $required;

    /**
     * Create a new component instance.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $value
     * @param  bool  $required
     * @return void
     */
    public function __construct($name, $type = 'text', $value = null, $required = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->required = $required;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.text-input');
    }
}
