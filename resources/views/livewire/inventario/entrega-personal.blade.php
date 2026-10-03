<div class="p-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Entregas al Personal</h1>
            <p class="text-sm text-gray-500">Registro de consumo y salida de útiles para el personal de la oficina</p>
        </div>
        <button 
            wire:click="abrirModalCrear" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Entrega
        </button>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Buscador de Vales -->
    <div class="mb-4">
        <input 
            wire:model.live.debounce.300ms="search" 
            type="text" 
            placeholder="Buscar por nombre o apellido del personal..." 
            class="w-full md:w-1/3 px-4 py-2 border border-gray-300 focus:outline-none text-sm"
        />
    </div>

    <!-- Tabla de Entregas -->
    <div class="bg-white shadow-sm overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Fecha</th>
                    <th class="px-6 py-3 text-left">Personal Solicitante</th>
                    <th class="px-6 py-3 text-left">Área / Cargo</th>
                    <th class="px-6 py-3 text-center">Ítems Entregados</th>
                    <th class="px-6 py-3 text-left">Observaciones</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($entregas as $entrega)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $entrega->fecha_entrega->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-800">
                            {{ $entrega->personal->nombre }} {{ $entrega->personal->apellido }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <span class="block font-medium text-gray-700">{{ $entrega->personal->area->nombre ?? 'Sin Área' }}</span>
                            <span class="text-xs text-gray-500">{{ $entrega->personal->cargo }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-300">
                                {{ $entrega->detalles->sum('cantidad_entregada') }} unidades ({{ $entrega->detalles->count() }} útiles)
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">
                            {{ $entrega->observaciones ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button 
                                wire:click="verDetalle({{ $entrega->id }})" 
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 px-3 py-1 text-xs font-medium">
                                Ver Vale
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No se han registrado entregas de útiles.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-200">
            {{ $entregas->links() }}
        </div>
    </div>

    <!-- MODAL 1: REGISTRAR NUEVA ENTREGA (CON COMBOBOX MIXTO) -->
    @if ($isOpenCrear)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-3xl w-full border border-gray-300 max-h-[92vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Registrar Entrega de Útiles</h3>
                    <button wire:click="cerrarModal('isOpenCrear')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="guardarEntrega" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Seleccionar Personal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Personal que Recibe *</label>
                            <select wire:model="personal_id" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none">
                                <option value="">-- Seleccionar Trabajador --</option>
                                @foreach ($listaPersonal as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->nombre }} {{ $p->apellido }} ({{ $p->area->nombre ?? 'Sin Área' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('personal_id') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Fecha de Entrega -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Entrega *</label>
                            <input type="date" wire:model="fecha_entrega" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none">
                            @error('fecha_entrega') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- COMBOBOX / BUSCADOR MIXTO DE ÚTILES -->
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-bold text-gray-800 mb-1">Buscar y Agregar Útiles</label>
                        <p class="text-xs text-gray-500 mb-2">Busca por nombre o marca. Al hacer clic se añadirá a la lista de despacho y desaparecerá del buscador.</p>

                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live.debounce.150ms="searchUtil"
                                wire:focus="toggleDropdownUtil(true)"
                                placeholder="Escribe para buscar o haz clic para desplegar..." 
                                class="w-full px-3 py-2 border border-gray-300 focus:outline-none text-sm bg-white"
                            />

                            <!-- Lista Desplegable -->
                            @if ($mostrarDropdownUtil)
                                <div class="absolute left-0 right-0 z-30 mt-1 max-h-48 overflow-y-auto bg-white border border-gray-300 shadow-lg">
                                    @forelse ($utilesDisponibles as $u)
                                        <button 
                                            type="button" 
                                            wire:click="seleccionarUtil({{ $u->id }})" 
                                            class="w-full text-left px-3 py-2 text-sm hover:bg-blue-50 border-b border-gray-100 flex justify-between items-center">
                                            <div>
                                                <span class="font-medium text-gray-800">{{ $u->nombre }}</span>
                                                <span class="text-xs text-gray-500 block">{{ $u->marca ?? 'S/M' }} - {{ $u->unidad }}</span>
                                            </div>
                                            <span class="text-xs font-bold text-green-700 bg-green-50 px-2 py-0.5 border border-green-200">
                                                Stock: {{ $u->stock_actual }}
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-3 text-xs text-gray-500 text-center">
                                            No hay útiles con stock disponible o ya fueron todos agregados.
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- TABLA DE ÚTILES A DESPACHAR -->
                    <div class="border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-2 text-left">Útil Seleccionado</th>
                                    <th class="px-4 py-2 text-center">Stock Actual</th>
                                    <th class="px-4 py-2 text-center">Unidad</th>
                                    <th class="px-4 py-2 text-center w-36">Cant. a Entregar</th>
                                    <th class="px-4 py-2 text-center w-12">Quitar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($itemsEntrega as $index => $item)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">
                                            {{ $item['nombre'] }}
                                            <span class="block text-xs text-gray-500">{{ $item['marca'] }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center text-xs font-bold text-blue-700 bg-blue-50/30">
                                            {{ $item['stock_actual'] }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-gray-600 text-xs">{{ $item['unidad'] }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                max="{{ $item['stock_actual'] }}"
                                                wire:model="itemsEntrega.{{ $index }}.cantidad" 
                                                class="w-full px-2 py-1 border border-gray-300 text-center font-bold text-sm focus:outline-none"
                                            />
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button 
                                                type="button" 
                                                wire:click="eliminarFilaItem({{ $index }})" 
                                                title="Quitar de la lista"
                                                class="text-red-600 hover:text-red-800 font-bold text-lg px-2">
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">
                                            No has agregado ningún útil todavía. Usa el buscador de arriba para añadirlos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('itemsEntrega') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones / Motivo</label>
                        <input type="text" wire:model="observaciones" placeholder="Ej: Uso ordinario, tareas extraordinarias, etc." class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cerrarModal('isOpenCrear')" class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 font-medium text-sm">
                            Confirmar Entrega y Descontar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: VER VALE DE ENTREGA / COMPROBANTE -->
    @if ($isOpenDetalle && $entregaSeleccionada)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-lg w-full border border-gray-300">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Vale de Entrega Interna</h3>
                    <button wire:click="cerrarModal('isOpenDetalle')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <div class="p-6 space-y-4 text-sm">
                    <div class="bg-gray-50 p-3 border border-gray-200 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Fecha:</span>
                            <span class="font-bold text-gray-800">{{ $entregaSeleccionada->fecha_entrega->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Receptor:</span>
                            <span class="font-bold text-gray-800">{{ $entregaSeleccionada->personal->nombre }} {{ $entregaSeleccionada->personal->apellido }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Área:</span>
                            <span class="text-gray-700">{{ $entregaSeleccionada->personal->area->nombre ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Cargo:</span>
                            <span class="text-gray-700">{{ $entregaSeleccionada->personal->cargo }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 font-medium">Observación:</span>
                            <span class="text-gray-700">{{ $entregaSeleccionada->observaciones ?? '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-bold text-gray-800 mb-2">Materiales Entregados:</h4>
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-xs">
                            <thead class="bg-gray-100 text-gray-600 uppercase font-semibold">
                                <tr>
                                    <th class="px-3 py-2 text-left">Útil</th>
                                    <th class="px-3 py-2 text-center">Unidad</th>
                                    <th class="px-3 py-2 text-center">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($entregaSeleccionada->detalles as $det)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-gray-800">{{ $det->util->nombre }}</td>
                                        <td class="px-3 py-2 text-center text-gray-500">{{ $det->util->unidad }}</td>
                                        <td class="px-3 py-2 text-center font-bold text-gray-900">{{ $det->cantidad_entregada }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end bg-gray-50">
                    <button type="button" wire:click="cerrarModal('isOpenDetalle')" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100 text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>