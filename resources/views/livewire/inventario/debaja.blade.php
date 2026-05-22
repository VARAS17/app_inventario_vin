<div class="p-6">
    {{-- Encabezado y Acciones --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 text-center md:text-left">Dados de Baja</h1>
            <p class="text-sm text-gray-500 text-center md:text-left">Historial de artículos malogrados o fuera de servicio</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center gap-3">
            {{-- Botón Exportar CSV --}}
            <button wire:click="exportarCSV" 
                    title="Exportar a CSV"
                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-all shadow-sm active:scale-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Exportar CSV</span>
            </button>

            {{-- Buscador --}}
            <div class="relative w-full sm:w-64">
                <input type="text" wire:model.live="search" 
                       class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm shadow-sm" 
                       placeholder="Buscar por nombre o tipo...">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles de Origen</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Baja</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
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
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $baja->personal->nombre ?? 'Sin asignar' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Columna Categoría --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-800 uppercase border border-red-200">
                                    {{ $baja->tipo_inventario }}
                                </span>
                            </td>

                            {{-- Columna Detalles --}}
                            <td class="px-6 py-4 text-sm">
                                <div class="text-[11px] text-gray-600 space-y-1 bg-gray-50 p-2 rounded-md border border-gray-100">
                                    @if($baja->detalles)
                                        @foreach($baja->detalles as $key => $value)
                                            <p><span class="font-bold text-gray-400 uppercase mr-1">{{ $key }}:</span> <span class="text-gray-800">{{ $value }}</span></p>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400 italic">Sin detalles técnicos</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Columna Fecha --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $baja->fecha_baja ? $baja->fecha_baja->format('d/m/Y') : $baja->created_at->format('d/m/Y') }}
                                </div>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button wire:click="eliminarPermanente({{ $baja->id }})" 
                                        wire:confirm="¿Estás seguro de eliminar este registro permanentemente? Esta acción borrará el archivo físico y no se puede deshacer."
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="text-gray-500 font-medium">No se encontraron registros en el historial.</p>
                                    <p class="text-gray-400 text-sm">Intenta con otro término de búsqueda.</p>
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