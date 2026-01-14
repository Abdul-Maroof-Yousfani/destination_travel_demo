<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FlightAndPriceTicket extends Component
{
    public $tax;
    public $flightData;
    public $totalFare;
    public $priceclass;

    /**
     * Create a new component instance.
     */
    public function __construct($flightData = [], $totalFare = [], $tax = null, $priceclass = null)
    {
        // dd($flightData, $totalFare, $tax);
        $this->flightData = is_array($flightData) ? $flightData : [];
        $this->totalFare = is_array($totalFare) ? $totalFare : [];
        $this->tax = $tax ? config('variables.flyjinnah_api.tax') : 0;
        $this->priceclass = $priceclass ?? '';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.flight-and-price-ticket');
    }
}
