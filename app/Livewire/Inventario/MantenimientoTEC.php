<?php

namespace App\Livewire\Inventario;
use Livewire\Attributes\Layout;


use Livewire\Component;

class MantenimientoTEC extends Component
{
    #[Layout('layouts.app')]

    public function render()
    {
        return view('livewire.inventario.mantenimiento-t-e-c');
    }
}
