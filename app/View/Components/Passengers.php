<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Passengers extends Component
{
    public $flightData;
    /**
     * Create a new component instance.
     */
    public function __construct($flightData = [])
    {
        $this->flightData = is_array($flightData) ? $flightData : [];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.Passengers');
    }
}
