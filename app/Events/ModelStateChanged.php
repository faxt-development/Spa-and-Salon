<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ModelStateChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The state change data.
     *
     * @var array
     */
    public $stateChange;

    /**
     * Create a new event instance.
     *
     * @param array $stateChange
     * @return void
     */
    public function __construct(array $stateChange)
    {
        $this->stateChange = $stateChange;
    }
}
