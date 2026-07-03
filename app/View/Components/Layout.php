<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Layout extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
        public string $ogType = 'website',
        public ?string $ogImage = null,
        public bool $noindex = false,
    )
    {
    }

    public function render(): View
    {
        return view('layout');
    }
}
