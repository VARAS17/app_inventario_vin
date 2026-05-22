<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Inventario de Útiles y Papelería
            </h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar artículo..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 w-full md:w-64">
                
                <button wire:click="crear" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nuevo Artículo
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4 shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <!-- TABLA -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Descripción del Artículo</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Stock Actual</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Unidad</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($utiles as $util)
                    <tr wire:key="util-{{ $util->id }}" class="hover:bg-emerald-50/30 transition border-b border-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $util->nombre }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-sm font-bold rounded-full {{ $util->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $util->cantidad }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 italic text-sm">{{ $util->unidad }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-3">
                                <!-- BOTÓN MOVIMIENTO RÁPIDO (NUEVO) -->
                                <button wire:click="abrirModalMovimiento({{ $util->id }})" class="text-emerald-600 hover:text-emerald-800 transition" title="Entrada/Salida">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </button>

                                <button wire:click="verHistorial({{ $util->id }})" class="text-amber-500 hover:text-amber-700" title="Historial">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>

                                <button wire:click="editar({{ $util->id }})" class="text-blue-500 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-400 italic">No hay artículos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DE MOVIMIENTO (INGRESO / EGRESO) - FLUJO NUEVO -->
    @if($isOpenMovimiento)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/30 backdrop-blur-sm" wire:click="closeMovimientoModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden z-10 border border-gray-100">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Actualizar Stock</h3>
                <p class="text-sm text-gray-500 mb-4 italic">{{ $articuloSeleccionado->nombre }} (Stock actual: {{ $articuloSeleccionado->cantidad }})</p>
                
                <!-- 1. Selección de Tipo -->
                <div class="flex gap-4 mb-6">
                    <button wire:click="$set('tipoMovimiento', 'Ingreso')" 
                        class="flex-1 py-3 px-4 rounded-xl border-2 transition all font-bold flex items-center justify-center gap-2 {{ $tipoMovimiento === 'Ingreso' ? 'bg-emerald-100 border-emerald-500 text-emerald-700' : 'bg-gray-50 border-gray-100 text-gray-400 hover:border-emerald-200' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg> Ingreso
                    </button>
                    <button wire:click="$set('tipoMovimiento', 'Egreso')" 
                        class="flex-1 py-3 px-4 rounded-xl border-2 transition all font-bold flex items-center justify-center gap-2 {{ $tipoMovimiento === 'Egreso' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-gray-50 border-gray-100 text-gray-400 hover:border-red-200' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                        </svg> Egreso
                    </button>
                </div>

                <!-- 2. Contenido habilitado solo si se seleccionó tipo -->
                <div class="space-y-4 {{ !$tipoMovimiento ? 'opacity-30 pointer-events-none' : 'opacity-100' }}">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Cantidad a {{ $tipoMovimiento ?: 'mover' }}</label>
                        <input type="number" wire:model="cantidadMovimiento" placeholder="0"
                            class="w-full mt-1 border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500">
                        @error('cantidadMovimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Motivo / Descripción</label>
                        <textarea wire:model="descripcionMovimiento" rows="2" placeholder="Ej: Reposición de oficina, Compra factura #123..."
                            class="w-full mt-1 border-gray-300 rounded-lg text-sm focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                        @error('descripcionMovimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="procesarMovimiento" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition disabled:opacity-50" {{ !$tipoMovimiento ? 'disabled' : '' }}>
                    Actualizar Stock
                </button>
                <button wire:click="closeMovimientoModal" class="text-gray-500 font-semibold px-4">Cancelar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL DE HISTORIAL -->
    @if($isHistoryOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm" wire:click="closeHistoryModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden z-10 border border-gray-100">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-xl font-bold text-gray-800">Historial de Movimientos</h3>
                    <button wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold">
                            <tr>
                                <th class="px-4 py-2">Fecha</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2">Cant.</th>
                                <th class="px-4 py-2">Descripción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($historial as $mov)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 font-bold {{ $mov->tipo == 'Ingreso' ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $mov->tipo }}
                                </td>
                                <td class="px-4 py-2">{{ $mov->cantidad }}</td>
                                <td class="px-4 py-2 text-gray-500 text-xs">{{ $mov->descripcion }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-4 italic">Sin movimientos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 text-right">
                <button wire:click="closeHistoryModal" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold">Cerrar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL DE REGISTRO / EDICIÓN (CRUD) -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/10 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-md sm:w-full overflow-hidden z-10 border border-gray-100">
            <div class="bg-white px-6 py-5">
                <h3 class="text-xl font-bold text-emerald-800 mb-4 border-b pb-2">
                    {{ $util_id ? 'Editar Artículo' : 'Nuevo Artículo' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Nombre / Descripción</label>
                        <input type="text" wire:model="nombre" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Stock Inicial</label>
                            <input type="number" wire:model="cantidad" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                            @error('cantidad') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Unidad</label>
                            <select wire:model="unidad" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                                <option value="Unidad">Unidad</option>
                                <option value="Cajas">Cajas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="guardar" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                    Guardar
                </button>
                <button wire:click="closeModal" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>