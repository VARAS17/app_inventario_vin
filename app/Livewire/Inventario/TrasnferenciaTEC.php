<?php

namespace App\Livewire\Inventario;
use Livewire\Attributes\Layout;


use Livewire\Component;

class TrasnferenciaTEC extends Component
{
    #[Layout('layouts.app')]

    public function render()
    {
        return view('livewire.inventario.trasnferencia-t-e-c');
    }
}
