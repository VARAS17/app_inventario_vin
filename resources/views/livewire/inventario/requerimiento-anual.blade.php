<div class="p-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Requerimientos Anuales y Recepciones</h1>
            <p class="text-sm text-gray-500">Planificación anual y control de entregas de Abastecimiento</p>
        </div>
        <button 
            wire:click="abrirModalCrear" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Requerimiento Anual
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

    <!-- Tabla Principal de Años -->
    <div class="bg-white shadow-sm overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Año</th>
                    <th class="px-6 py-3 text-left">Documento / Oficio</th>
                    <th class="px-6 py-3 text-center">Ítems Pedidos</th>
                    <th class="px-6 py-3 text-center">Lotes Recibidos</th>
                    <th class="px-6 py-3 text-center">Estado</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requerimientos as $req)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-bold text-gray-900 text-base">{{ $req->anio }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $req->codigo ?? 'Sin código' }}</td>
                        <td class="px-6 py-4 text-center font-medium">{{ $req->detalles->count() }} útiles</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">{{ $req->recepciones->count() }} entregas</td>
                        <td class="px-6 py-4 text-center">
                            @if ($req->estado == 'Abierto')
                                <span class="px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                    En Curso
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                    Cerrado
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button 
                                wire:click="verDetalle({{ $req->id }})" 
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 text-xs font-medium border border-gray-300">
                                Ver Balance / Llegadas
                            </button>
                            @if ($req->estado == 'Abierto')
                                <button 
                                    wire:click="abrirModalRecepcion({{ $req->id }})" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 text-xs font-medium">
                                    + Registrar Llegada
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No hay requerimientos anuales registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-200">
            {{ $requerimientos->links() }}
        </div>
    </div>

    <!-- MODAL 1: REGISTRAR REQUERIMIENTO ANUAL (CON BUSCADOR MIXTO) -->
    @if ($isOpenCrear)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-3xl w-full border border-gray-300 max-h-[92vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">Nuevo Requerimiento Anual</h3>
                    <button wire:click="cerrarModal('isOpenCrear')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="guardarRequerimiento" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Año Presupuestal *</label>
                            <input type="number" wire:model="anio" class="w-full px-3 py-2 border border-gray-300 focus:outline-none">
                            @error('anio') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° de Oficio / Trámite</label>
                            <input type="text" wire:model="codigo" placeholder="Ej: OFICIO-0012-2024" class="w-full px-3 py-2 border border-gray-300 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento Escaneado (PDF)</label>
                        <input type="file" wire:model="documento_anual" class="w-full px-3 py-1 border border-gray-300 text-sm">
                        @error('documento_anual') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- BUSCADOR MIXTO / SELECTOR DE ÚTILES -->
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-bold text-gray-800 mb-1">Agregar Útiles al Pedido</label>
                        <p class="text-xs text-gray-500 mb-2">Busca o haz clic para ver la lista. Al seleccionar un útil se agregará a la tabla y desaparecerá de la lista.</p>

                        <!-- Input Combobox / Search -->
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
                                            <span class="font-medium text-gray-800">{{ $u->nombre }}</span>
                                            <span class="text-xs text-gray-500">Unidad: {{ $u->unidad }} | Marca: {{ $u->marca ?? '-' }}</span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-3 text-xs text-gray-500 text-center">
                                            No hay útiles disponibles o ya fueron todos agregados.
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- TABLA DE ÚTILES SELECCIONADOS -->
                    <div class="border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-100 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-2 text-left">Útil Seleccionado</th>
                                    <th class="px-4 py-2 text-left">Marca</th>
                                    <th class="px-4 py-2 text-center">Unidad</th>
                                    <th class="px-4 py-2 text-center w-32">Cant. Solicitada</th>
                                    <th class="px-4 py-2 text-center w-12">Quitar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($itemsRequerimiento as $index => $item)
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $item['nombre'] }}</td>
                                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $item['marca'] }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600 text-xs">{{ $item['unidad'] }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <input 
                                                type="number" 
                                                min="1" 
                                                wire:model="itemsRequerimiento.{{ $index }}.cantidad" 
                                                class="w-full px-2 py-1 border border-gray-300 text-center font-bold text-sm focus:outline-none"
                                            />
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button 
                                                type="button" 
                                                wire:click="eliminarFilaItem({{ $index }})" 
                                                title="Eliminar de la lista"
                                                class="text-red-600 hover:text-red-800 font-bold text-lg px-2">
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-xs">
                                            No has agregado ningún útil todavía. Usa el buscador de arriba para agregarlos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @error('itemsRequerimiento') <span class="text-xs text-red-600 block">{{ $message }}</span> @enderror

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <input type="text" wire:model="observaciones" placeholder="Opcional..." class="w-full px-3 py-2 border border-gray-300 focus:outline-none text-sm">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cerrarModal('isOpenCrear')" class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700">
                            Guardar Requerimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- MODAL 2: VER BALANCE / CUMPLIMIENTO DE ABASTECIMIENTO -->
    @if ($isOpenDetalle && $requerimientoSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-4xl w-full border border-gray-300 max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            Balance Anual {{ $requerimientoSeleccionado->anio }}
                        </h3>
                        <p class="text-xs text-gray-500">Oficio: {{ $requerimientoSeleccionado->codigo ?? 'N/A' }}</p>
                    </div>
                    <button wire:click="cerrarModal('isOpenDetalle')" class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                </div>

                <div class="p-6 space-y-6 overflow-y-auto">
                    <!-- Tabla Comparativa -->
                    <div>
                        <h4 class="font-bold text-sm text-gray-800 mb-2">Resumen de Cumplimiento (¿Qué nos debe Abastecimiento?)</h4>
                        <table class="min-w-full divide-y divide-gray-200 text-sm border border-gray-200">
                            <thead class="bg-gray-100 text-gray-600 text-xs uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-2 text-left">Útil</th>
                                    <th class="px-4 py-2 text-center">Meta Pedida</th>
                                    <th class="px-4 py-2 text-center">Total Recibido</th>
                                    <th class="px-4 py-2 text-center">Pendiente de Abastecimiento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($requerimientoSeleccionado->detalles as $det)
                                    @php
                                        $totalRecibido = 0;
                                        foreach ($requerimientoSeleccionado->recepciones as $rec) {
                                            $totalRecibido += $rec->detalles->where('util_id', $det->util_id)->sum('cantidad_recibida');
                                        }
                                        $pendiente = $det->cantidad_solicitada - $totalRecibido;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $det->util->nombre }}</td>
                                        <td class="px-4 py-2 text-center font-bold">{{ $det->cantidad_solicitada }} {{ $det->util->unidad }}</td>
                                        <td class="px-4 py-2 text-center text-blue-600 font-bold">{{ $totalRecibido }}</td>
                                        <td class="px-4 py-2 text-center font-bold">
                                            @if ($pendiente <= 0)
                                                <span class="text-green-700 bg-green-100 px-2 py-0.5 text-xs">Completado</span>
                                            @else
                                                <span class="text-red-700 bg-red-100 px-2 py-0.5 text-xs">Deben {{ $pendiente }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Historial de Entregas Parciales -->
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="font-bold text-sm text-gray-800">Entregas Parciales Registradas</h4>
                            <button 
                                wire:click="abrirModalRecepcion({{ $requerimientoSeleccionado->id }})" 
                                class="text-xs bg-green-600 hover:bg-green-700 text-white font-medium px-3 py-1">
                                + Registrar Nueva Llegada
                            </button>
                        </div>

                        @if ($requerimientoSeleccionado->recepciones->count() > 0)
                            <div class="space-y-3">
                                @foreach ($requerimientoSeleccionado->recepciones as $recep)
                                    <div class="border border-gray-200 p-3 bg-gray-50 text-xs">
                                        <div class="flex justify-between font-bold text-gray-700 mb-1">
                                            <span>Documento: {{ $recep->numero_documento }}</span>
                                            <span>Fecha: {{ $recep->fecha_recepcion->format('d/m/Y') }}</span>
                                        </div>
                                        <p class="text-gray-500 mb-2">Obs: {{ $recep->observaciones ?? 'Ninguna' }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($recep->detalles as $rDet)
                                                <span class="bg-white border border-gray-300 px-2 py-0.5 font-medium">
                                                    {{ $rDet->util->nombre }}: +{{ $rDet->cantidad_recibida }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 italic">No se han registrado entregas todavía para este año.</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-t border-gray-200 flex justify-end bg-gray-50">
                    <button type="button" wire:click="cerrarModal('isOpenDetalle')" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-100">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 3: REGISTRAR LLEGADA DE LOTE (CON COMPARATIVA EN TIEMPO REAL) -->
    @if ($isOpenRecepcion && $requerimientoSeleccionado)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-4xl w-full border border-gray-300 max-h-[92vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Registrar Entrega de Abastecimiento</h3>
                        <p class="text-xs text-gray-500">Requerimiento Anual {{ $requerimientoSeleccionado->anio }} - Oficio: {{ $requerimientoSeleccionado->codigo ?? 'N/A' }}</p>
                    </div>
                    <button wire:click="cerrarModal('isOpenRecepcion')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="guardarRecepcion" class="p-6 space-y-4 overflow-y-auto flex-1">
                    <!-- Datos de la Remisión / PECOSA -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">N° Documento (PECOSA / Guía) *</label>
                            <input type="text" wire:model="numero_documento" placeholder="Ej: PECOSA-0045" class="w-full px-3 py-2 border border-gray-300 focus:outline-none text-sm">
                            @error('numero_documento') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Recepción *</label>
                            <input type="date" wire:model="fecha_recepcion" class="w-full px-3 py-2 border border-gray-300 focus:outline-none text-sm">
                            @error('fecha_recepcion') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjuntar Acta / Guía Escaneada (PDF o Imagen)</label>
                        <input type="file" wire:model="documento_recepcion" class="w-full px-3 py-1.5 border border-gray-300 text-sm">
                    </div>

                    <!-- TABLA COMPARATIVA: PEDIDO vs YA RECIBIDO vs RESTANTE vs LLEGA AHORA -->
                    <div class="pt-4 border-t border-gray-200">
                        <label class="block text-sm font-bold text-gray-800 mb-1">
                            Detalle de Útiles del Lote
                        </label>
                        <p class="text-xs text-gray-500 mb-3">
                            Verifica lo que Abastecimiento aún debe y anota en la columna derecha lo que llegó en esta entrega.
                        </p>

                        <div class="border border-gray-200 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-100 text-gray-600 text-xs uppercase font-semibold">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Útil / Artículo</th>
                                        <th class="px-3 py-2.5 text-center">Unidad</th>
                                        <th class="px-3 py-2.5 text-center bg-gray-50">Pedido Anual</th>
                                        <th class="px-3 py-2.5 text-center bg-blue-50/50 text-blue-800">Ya Recibido</th>
                                        <th class="px-3 py-2.5 text-center bg-amber-50/50 text-amber-800">Restante</th>
                                        <th class="px-4 py-2.5 text-center w-36 bg-green-50 text-green-900">Llega en este Lote</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($requerimientoSeleccionado->detalles as $det)
                                        @php
                                            // Calcular lo que ya llegó en entregas previas
                                            $totalPrevio = 0;
                                            foreach ($requerimientoSeleccionado->recepciones as $rec) {
                                                $totalPrevio += $rec->detalles->where('util_id', $det->util_id)->sum('cantidad_recibida');
                                            }
                                            $restante = $det->cantidad_solicitada - $totalPrevio;
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <!-- Útil -->
                                            <td class="px-4 py-2 font-medium text-gray-900">
                                                {{ $det->util->nombre }}
                                                <span class="block text-xs font-normal text-gray-500">{{ $det->util->marca ?? 'S/M' }}</span>
                                            </td>

                                            <!-- Unidad -->
                                            <td class="px-3 py-2 text-center text-gray-600 text-xs">
                                                {{ $det->util->unidad }}
                                            </td>

                                            <!-- Total Pedido -->
                                            <td class="px-3 py-2 text-center font-bold text-gray-700 bg-gray-50">
                                                {{ $det->cantidad_solicitada }}
                                            </td>

                                            <!-- Ya Recibido -->
                                            <td class="px-3 py-2 text-center font-bold text-blue-600 bg-blue-50/20">
                                                {{ $totalPrevio }}
                                            </td>

                                            <!-- Restante / Debe Abastecimiento -->
                                            <td class="px-3 py-2 text-center font-bold bg-amber-50/20">
                                                @if ($restante <= 0)
                                                    <span class="text-green-700 bg-green-100 border border-green-200 px-2 py-0.5 text-xs">
                                                        Completado
                                                    </span>
                                                @else
                                                    <span class="text-amber-800 bg-amber-100 border border-amber-200 px-2 py-0.5 text-xs">
                                                        Faltan {{ $restante }}
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Input: Lo que llega ahora -->
                                            <td class="px-4 py-2 text-center bg-green-50/30">
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    wire:model="cantidadesRecibidas.{{ $det->util_id }}" 
                                                    class="w-full px-2 py-1.5 border border-gray-300 text-center font-bold text-sm bg-white focus:outline-none focus:border-green-600"
                                                    placeholder="0"
                                                />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <input type="text" wire:model="obs_recepcion" placeholder="Ej: Entrega parcial incompleta por falta de stock central..." class="w-full px-3 py-2 border border-gray-300 focus:outline-none text-sm">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="cerrarModal('isOpenRecepcion')" class="px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-100 text-sm">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white hover:bg-green-700 font-medium text-sm">
                            Confirmar Ingreso a Almacén
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>