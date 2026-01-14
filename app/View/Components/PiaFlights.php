<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PiaFlights extends Component
{
    public $flights;
    public $isRoundTrip;
    /**
     * Create a new component instance.
     */
    public function __construct($flight, $roundTrip)
    {
        $this->flights = $flight;
        $this->isRoundTrip = $roundTrip;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.pia-flights');
    }
}
