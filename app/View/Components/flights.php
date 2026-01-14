<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class flights extends Component
{
    public $flightData;
    public $paxCount;
    /**
     * Create a new component instance.
     */
    public function __construct($flightData, $paxCount)
    {
        $this->flightData = $flightData;
        $this->paxCount = $paxCount;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.flights');
    }
}
