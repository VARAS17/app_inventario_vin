<?php

namespace App\Livewire\Inventario;
use Livewire\Attributes\Layout;


use Livewire\Component;

class SalidaTEC extends Component
{
    #[Layout('layouts.app')]

    public function render()
    {
        return view('livewire.inventario.salida-t-e-c');
    }
}
