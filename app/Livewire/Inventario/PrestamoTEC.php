<?php

namespace App\Livewire\Inventario;
use Livewire\Attributes\Layout;


use Livewire\Component;

class PrestamoTEC extends Component
{
    #[Layout('layouts.app')]

    public function render()
    {
        return view('livewire.inventario.prestamo-t-e-c');
    }
}
