<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SessionTimeoutContainer extends Component
{
    public $expTime;
    /**
     * Create a new component instance.
     */
    public function __construct($expTime = null)
    {
        $this->expTime = $expTime ?? session('IdsExpireTimeFj') ?? session('IdsExpireTimeEmi') ?? now()->addMinutes(10);
        // $this->expTime = now()->addMinutes(80);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.session-timeout-container');
    }
}
