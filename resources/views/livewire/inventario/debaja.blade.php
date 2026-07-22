<div class="p-6">
    {{-- Encabezado y Acciones --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 text-center md:text-left">Historial de Bajas</h1>
            <p class="text-sm text-gray-500 text-center md:text-left">Gestión de artículos fuera de servicio y eliminaciones físicas</p>
        </div>
        
        <div class="flex flex-col sm:flex-row flex-wrap items-center gap-3">
            {{-- Filtro por Categoría --}}
            <div class="w-full sm:w-44">
                <select wire:model.live="filterCategoria" 
                        class="w-full pl-3 pr-8 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm shadow-sm bg-white">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filtro por Responsable --}}
            <div class="w-full sm:w-56">
                <select wire:model.live="filterPersonal" 
                        class="w-full pl-3 pr-8 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm shadow-sm bg-white">
                    <option value="">Todos los responsables</option>
                    @foreach($personal_list as $p)
                        <option value="{{ $p->id }}">{{ $p->nombre }} {{ $p->apellido }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Buscador --}}
            <div class="relative w-full sm:w-64">
                <input type="text" wire:model.live="search" 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm shadow-sm" 
                       placeholder="Buscar por nombre...">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Botón Exportar CSV --}}
            <button wire:click="exportarCSV" 
                    title="Exportar reporte filtrado"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm active:scale-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="hidden sm:inline">Exportar</span>
            </button>
        </div>
    </div>

    {{-- Indicador de carga --}}
    <div wire:loading class="w-full mb-4">
        <div class="flex items-center justify-center gap-2 text-red-600 text-sm font-medium">
            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Actualizando lista...
        </div>
    </div>

    {{-- Tabla de Registros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Artículo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles Técnicos</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Baja</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" wire:loading.class="opacity-50">
                    @forelse($bajas as $baja)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Columna Artículo --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($baja->imagen)
                                            <img class="h-10 w-10 rounded-lg object-cover border border-gray-200" 
                                                 src="{{ route('personal.foto', ['path' => $baja->imagen]) }}" 
                                                 alt="{{ $baja->nombre }}">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $baja->nombre }}</div>
                                        <div class="text-[10px] text-gray-500 uppercase font-semibold flex items-center gap-1">
                                            <span class="text-gray-400 italic">Resp:</span> {{ $baja->personal->nombre ?? 'Sin asignar' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Columna Categoría --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 uppercase border border-gray-200">
                                    {{ $baja->tipo_inventario }}
                                </span>
                            </td>

                            {{-- Columna Detalles --}}
                            <td class="px-6 py-4 text-sm">
                                <div class="text-[11px] text-gray-600 space-y-0.5 bg-gray-50 p-2 rounded-md border border-gray-100 max-w-xs">
                                    @if($baja->detalles)
                                        @foreach($baja->detalles as $key => $value)
                                            <p><span class="font-bold text-gray-400 uppercase text-[9px]">{{ $key }}:</span> {{ $value ?: 'N/A' }}</p>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic">Sin datos técnicos</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Columna Fecha y Motivo --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="font-medium text-gray-800">
                                    {{ $baja->fecha_baja ? $baja->fecha_baja->format('d/m/Y') : $baja->created_at->format('d/m/Y') }}
                                </div>
                                <div class="text-[10px] text-red-500 font-bold uppercase">{{ $baja->motivo }}</div>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="eliminarPermanente({{ $baja->id }})" 
                                        wire:confirm="¿ESTÁS SEGURO? Esta acción eliminará permanentemente el registro de la base de datos y borrará el archivo de imagen del servidor."
                                        class="text-red-400 hover:text-red-600 transition-all p-2 hover:bg-red-50 rounded-full group"
                                        title="Eliminar permanentemente">
                                    <svg class="h-5 w-5 transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-gray-500 font-medium">No se encontraron registros de baja con los filtros aplicados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="mt-6">
        {{ $bajas->links() }}
    </div>
</div>