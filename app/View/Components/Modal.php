<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public string $id;
    public string $title;
    public ?string $size;

    public function __construct(string $id, string $title, string $size = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->size = $size;
    }

    public function render()
    {
        return view('components.modal');
    }
}