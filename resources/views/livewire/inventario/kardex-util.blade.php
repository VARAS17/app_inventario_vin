<div class="p-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Historial y Kardex</h1>
            <p class="text-sm text-gray-500">Trazabilidad completa de entradas y salidas de útiles</p>
        </div>
        <button 
            wire:click="abrirModalCierre" 
            class="bg-gray-800 hover:bg-gray-900 text-white font-medium px-4 py-2 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Resumen / Cierre de Stock
        </button>
    </div>

    <!-- Tarjetas de Resumen Rápido -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 border border-gray-200 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-xs font-bold uppercase text-gray-500">Entradas del Período</p>
                <p class="text-2xl font-bold text-green-700">+{{ $totalIngresos }}</p>
            </div>
            <span class="text-xs bg-green-100 text-green-800 border border-green-200 px-2 py-1 font-semibold">
                Abastecimiento
            </span>
        </div>
        <div class="bg-white p-4 border border-gray-200 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-xs font-bold uppercase text-gray-500">Salidas del Período</p>
                <p class="text-2xl font-bold text-red-700">-{{ $totalEgresos }}</p>
            </div>
            <span class="text-xs bg-red-100 text-red-800 border border-red-200 px-2 py-1 font-semibold">
                Consumo Personal
            </span>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 border border-gray-200 shadow-sm mb-4 space-y-3">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
            <!-- Buscar texto -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Buscar</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Descripción o útil..." 
                    class="w-full px-3 py-1.5 border border-gray-300 focus:outline-none"
                />
            </div>

            <!-- Filtro por Útil -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Útil</label>
                <select wire:model.live="filtro_util_id" class="w-full px-3 py-1.5 border border-gray-300 focus:outline-none">
                    <option value="">Todos los útiles</option>
                    @foreach ($catalogoUtiles as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Tipo -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo de Movimiento</label>
                <select wire:model.live="filtro_tipo" class="w-full px-3 py-1.5 border border-gray-300 focus:outline-none">
                    <option value="">Todos los tipos</option>
                    <option value="Ingreso">Ingreso (Entrada)</option>
                    <option value="Egreso">Egreso (Salida)</option>
                    <option value="Saldo Inicial">Saldo Inicial</option>
                </select>
            </div>

            <!-- Fechas -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_desde" class="w-full px-3 py-1.5 border border-gray-300 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_hasta" class="w-full px-3 py-1.5 border border-gray-300 focus:outline-none">
            </div>
        </div>

        <div class="flex justify-end pt-1">
            <button wire:click="limpiarFiltros" class="text-xs text-gray-600 hover:text-gray-900 border border-gray-300 bg-gray-50 px-3 py-1">
                Limpiar Filtros
            </button>
        </div>
    </div>

    <!-- Tabla del Kardex -->
    <div class="bg-white shadow-sm overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Útil / Artículo</th>
                    <th class="px-4 py-3 text-center">Tipo</th>
                    <th class="px-4 py-3 text-center">Cantidad</th>
                    <th class="px-4 py-3 text-center">Stock Previo</th>
                    <th class="px-4 py-3 text-center">Nuevo Stock</th>
                    <th class="px-4 py-3 text-left">Detalle / Referencia</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movimientos as $mov)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700 whitespace-nowrap">
                            {{ $mov->fecha->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-900">
                            {{ $mov->util->nombre }}
                            <span class="block text-xs font-normal text-gray-500">
                                {{ $mov->util->unidad }} - {{ $mov->util->marca ?? 'S/M' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($mov->tipo == 'Ingreso')
                                <span class="px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                    Ingreso
                                </span>
                            @elseif ($mov->tipo == 'Egreso')
                                <span class="px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                    Egreso
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                    Saldo Inicial
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-base {{ $mov->tipo == 'Egreso' ? 'text-red-600' : 'text-green-600' }}">
                            {{ $mov->tipo == 'Egreso' ? '-' : '+' }}{{ $mov->cantidad }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">
                            {{ $mov->stock_anterior }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-gray-900 bg-gray-50">
                            {{ $mov->stock_nuevo }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $mov->descripcion }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron movimientos con los filtros aplicados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-200">
            {{ $movimientos->links() }}
        </div>
    </div>

    <!-- MODAL: RESUMEN / CIERRE DE STOCK FÍSICO -->
    @if ($isOpenCierre)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-3xl w-full border border-gray-300 max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Foto de Inventario Físico (Auditoría)</h3>
                        <p class="text-xs text-gray-500">Existencias físicas consolidadas en armario</p>
                    </div>
                    <button wire:click="cerrarModal('isOpenCierre')" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto space-y-4">
                    <p class="text-xs text-gray-600 bg-blue-50 border border-blue-200 p-3">
                        Este resumen refleja el saldo real actual en armario. Puede utilizarse como acta de arqueo para el cierre anual o punto de partida del año entrante.
                    </p>

                    <table class="min-w-full divide-y divide-gray-200 border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700 text-xs uppercase font-semibold">
                            <tr>
                                <th class="px-4 py-2 text-left">Útil / Insumo</th>
                                <th class="px-4 py-2 text-left">Marca</th>
                                <th class="px-4 py-2 text-center">Unidad</th>
                                <th class="px-4 py-2 text-center">Saldo en Armario</th>
                                <th class="px-4 py-2 text-center">Condición</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($catalogoUtiles as $u)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $u->nombre }}</td>
                                    <td class="px-4 py-2 text-gray-500 text-xs">{{ $u->marca ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center text-gray-600 text-xs">{{ $u->unidad }}</td>
                                    <td class="px-4 py-2 text-center font-bold text-gray-900">{{ $u->stock_actual }}</td>
                                    <td class="px-4 py-2 text-center text-xs">
                                        @if ($u->stock_actual > 0)
                                            <span class="text-green-700 font-bold">Con Stock</span>
                                        @else
                                            <span class="text-red-700 font-bold">Agotado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50">
                    <button type="button" wire:click="cerrarModal('isOpenCierre')" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>