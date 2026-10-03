<x-app-layout>
    {{-- BARRA SUPERIOR INSTITUCIONAL: TÍTULO Y RELOJ EN TIEMPO REAL --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-1">
            <div class="flex items-center gap-3">
                <span class="w-2 h-7 bg-blue-900 rounded-xs"></span>
                <div>
                    <h2 class="font-bold text-lg text-slate-900 leading-tight tracking-tight uppercase">
                        Panel de Control y Monitoreo General
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Vicerrectorado de Investigación — Universidad Nacional de Trujillo</p>
                </div>
            </div>

            {{-- Reloj Digital en Tiempo Real (Hora y Fecha Oficial) --}}
            <div id="reloj-digital" class="flex items-center gap-3 px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-right self-end sm:self-auto shadow-2xs">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <div>
                    <div class="font-mono font-bold text-xs text-slate-800 tracking-wider" id="reloj-hora">--:--:-- --</div>
                    <div class="text-[10px] text-slate-500 font-medium capitalize" id="reloj-fecha">--------, -- --- ----</div>
                </div>
            </div>

            <script>
                function actualizarReloj() {
                    const now = new Date();
                    const horaEl = document.getElementById('reloj-hora');
                    const fechaEl = document.getElementById('reloj-fecha');

                    if (horaEl) {
                        horaEl.textContent = now.toLocaleTimeString('es-PE', {
                            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
                        });
                    }
                    if (fechaEl) {
                        fechaEl.textContent = now.toLocaleDateString('es-PE', {
                            weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
                        });
                    }
                }

                actualizarReloj();
                setInterval(actualizarReloj, 1000);
            </script>
        </div>
    </x-slot>

    {{-- CONSULTAS AGREGADAS: EQUIPO TECNOLÓGICO Y ÚTILES DE OFICINA --}}
    @php
        // 1. Métricas Tecnología
        $conteoEstados = \App\Models\Tecnologia::selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $totalTecnologia = array_sum($conteoEstados);
        $disponibles     = $conteoEstados['Disponible'] ?? 0;
        $asignados       = $conteoEstados['Asignado'] ?? 0;
        $mantenimiento   = $conteoEstados['En Mantenimiento'] ?? 0;
        $deBaja          = $conteoEstados['De baja'] ?? 0;
        $prestados       = $conteoEstados['Prestado'] ?? 0;

        $pDisponibles   = $totalTecnologia > 0 ? round(($disponibles / $totalTecnologia) * 100, 1) : 0;
        $pAsignados     = $totalTecnologia > 0 ? round(($asignados / $totalTecnologia) * 100, 1) : 0;
        $pMantenimiento = $totalTecnologia > 0 ? round(($mantenimiento / $totalTecnologia) * 100, 1) : 0;
        $pDeBaja        = $totalTecnologia > 0 ? round(($deBaja / $totalTecnologia) * 100, 1) : 0;
        $pPrestados     = $totalTecnologia > 0 ? round(($prestados / $totalTecnologia) * 100, 1) : 0;

        // 2. Métricas Útiles de Oficina
        $totalCatalogoUtiles = \App\Models\utiles::count();
        $stockFisicoTotal    = \App\Models\utiles::sum('stock_actual');
        $utilesAgotados      = \App\Models\utiles::where('stock_actual', '<=', 0)->count();
        $utilesCriticos      = \App\Models\utiles::where('stock_actual', '>', 0)
                                    ->whereColumn('stock_actual', '<=', 'stock_minimo')
                                    ->count();
        $utilesDisponibles   = \App\Models\utiles::where('stock_actual', '>', 0)
                                    ->whereColumn('stock_actual', '>', 'stock_minimo')
                                    ->count();

        // Requerimiento Anual del año actual y su porcentaje de cumplimiento
        $reqActual = \App\Models\requerimientos_anuales::where('anio', date('Y'))
            ->with(['detalles', 'recepciones.detalles'])
            ->first();

        $totalSolicitadoAnual = $reqActual ? $reqActual->detalles->sum('cantidad_solicitada') : 0;
        $totalRecibidoAnual   = 0;
        if ($reqActual) {
            foreach ($reqActual->recepciones as $rec) {
                $totalRecibidoAnual += $rec->detalles->sum('cantidad_recibida');
            }
        }
        $pCumplimientoReq = $totalSolicitadoAnual > 0 ? round(($totalRecibidoAnual / $totalSolicitadoAnual) * 100, 1) : 0;

        // Entregas realizadas al personal este mes
        $totalEntregasMes = \App\Models\entregas_personal::whereMonth('fecha_entrega', date('m'))
            ->whereYear('fecha_entrega', date('Y'))
            ->count();

        // Porcentajes para gráfico de útiles
        $pUtilesDisp = $totalCatalogoUtiles > 0 ? round(($utilesDisponibles / $totalCatalogoUtiles) * 100, 1) : 0;
        $pUtilesCrit = $totalCatalogoUtiles > 0 ? round(($utilesCriticos / $totalCatalogoUtiles) * 100, 1) : 0;
        $pUtilesAgot = $totalCatalogoUtiles > 0 ? round(($utilesAgotados / $totalCatalogoUtiles) * 100, 1) : 0;
    @endphp

    <div class="space-y-8 pb-6">

        <!-- ================================================================= -->
        <!-- SECCIÓN 1: EQUIPO TECNOLÓGICO                                     -->
        <!-- ================================================================= -->
        <div class="space-y-6">

            <!-- 1. BLOQUE DE TARJETAS KPI (ESTADOS ASOCIADOS POR COLOR) -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                
                {{-- KPI 1: TOTAL EQUIPOS (AZUL INSTITUCIONAL) --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Activos</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-900 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-slate-900 font-mono leading-none">{{ $totalTecnologia }}</div>
                        <span class="text-[10px] text-slate-400 font-medium mt-1 block">Catálogo tecnológico</span>
                    </div>
                </div>

                {{-- KPI 2: DISPONIBLES (VERDE ESMERALDA) --}}
                <div class="bg-white p-4 rounded-xl border border-emerald-100 shadow-xs flex flex-col justify-between hover:border-emerald-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Disponibles</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-emerald-700 font-mono leading-none">{{ $disponibles }}</div>
                        <span class="text-[10px] text-emerald-600/80 font-medium mt-1 block">{{ $pDisponibles }}% en almacén</span>
                    </div>
                </div>

                {{-- KPI 3: ASIGNADOS (AZUL) --}}
                <div class="bg-white p-4 rounded-xl border border-blue-100 shadow-xs flex flex-col justify-between hover:border-blue-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Asignados</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-blue-700 font-mono leading-none">{{ $asignados }}</div>
                        <span class="text-[10px] text-blue-600/80 font-medium mt-1 block">{{ $pAsignados }}% en oficinas</span>
                    </div>
                </div>

                {{-- KPI 4: EN MANTENIMIENTO (ÁMBAR / NARANJA) --}}
                <div class="bg-white p-4 rounded-xl border border-amber-100 shadow-xs flex flex-col justify-between hover:border-amber-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">En Taller</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-amber-700 font-mono leading-none">{{ $mantenimiento }}</div>
                        <span class="text-[10px] text-amber-600/80 font-medium mt-1 block">{{ $pMantenimiento }}% soporte técnico</span>
                    </div>
                </div>

                {{-- KPI 5: PRESTADOS (MORADO) --}}
                <div class="bg-white p-4 rounded-xl border border-purple-100 shadow-xs flex flex-col justify-between hover:border-purple-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700">Prestados</span>
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-purple-700 font-mono leading-none">{{ $prestados }}</div>
                        <span class="text-[10px] text-purple-600/80 font-medium mt-1 block">{{ $pPrestados }}% cesión temporal</span>
                    </div>
                </div>

                {{-- KPI 6: DADOS DE BAJA (ROJO) --}}
                <div class="bg-white p-4 rounded-xl border border-rose-100 shadow-xs flex flex-col justify-between hover:border-rose-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">De Baja</span>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-rose-700 font-mono leading-none">{{ $deBaja }}</div>
                        <span class="text-[10px] text-rose-600/80 font-medium mt-1 block">{{ $pDeBaja }}% desincorporados</span>
                    </div>
                </div>

            </div>

            <!-- 2. BLOQUE CENTRAL: GRÁFICO CIRCULAR + ACCESOS RÁPIDOS TECNOLÓGICOS -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                
                <!-- GRÁFICO CIRCULAR (DONUT CHART) DE DISTRIBUCIÓN -->
                <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-900 rounded-xs"></span>
                            Distribución Operativa del Parque Tecnológico
                        </h3>
                        <span class="text-[10px] font-mono text-slate-400">100% inventario</span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row items-center justify-center gap-6">
                        <div class="relative w-44 h-44 shrink-0 flex items-center justify-center">
                            <canvas id="donutChartInventario"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-bold font-mono text-slate-800 leading-none">{{ $totalTecnologia }}</span>
                                <span class="text-[9px] uppercase font-bold text-slate-400 mt-0.5">Activos</span>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs w-full sm:w-auto">
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-emerald-500"></span> Disponibles
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $disponibles }} <span class="text-[10px] font-normal text-slate-400">({{ $pDisponibles }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-blue-600"></span> Asignados
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $asignados }} <span class="text-[10px] font-normal text-slate-400">({{ $pAsignados }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-amber-500"></span> Mantenimiento
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $mantenimiento }} <span class="text-[10px] font-normal text-slate-400">({{ $pMantenimiento }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-purple-600"></span> Prestados
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $prestados }} <span class="text-[10px] font-normal text-slate-400">({{ $pPrestados }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-rose-600"></span> De Baja
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $deBaja }} <span class="text-[10px] font-normal text-slate-400">({{ $pDeBaja }}%)</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full h-2 rounded-full overflow-hidden flex bg-slate-100 mt-2">
                        <div style="width: {{ $pDisponibles }}%" class="bg-emerald-500" title="Disponibles: {{ $pDisponibles }}%"></div>
                        <div style="width: {{ $pAsignados }}%" class="bg-blue-600" title="Asignados: {{ $pAsignados }}%"></div>
                        <div style="width: {{ $pMantenimiento }}%" class="bg-amber-500" title="Mantenimiento: {{ $pMantenimiento }}%"></div>
                        <div style="width: {{ $pPrestados }}%" class="bg-purple-600" title="Prestados: {{ $pPrestados }}%"></div>
                        <div style="width: {{ $pDeBaja }}%" class="bg-rose-600" title="De Baja: {{ $pDeBaja }}%"></div>
                    </div>
                </div>

                <!-- ACCESOS DIRECTOS DE MONITOREO TECNOLÓGICO -->
                <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-900 rounded-xs"></span>
                            Módulos de Consulta y Supervisión Rápida
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Acceso directo a las vistas de seguimiento y auditoría operativa.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 my-3">
                        <a href="{{ url('/inventariotec') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100/80 text-blue-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7M4 7h16M4 7l2-4h12l2 4"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Inventario General</span>
                                <span class="text-[10px] text-slate-400">Directorio de bienes</span>
                            </div>
                        </a>

                        <a href="{{ url('/trasnferencia-tec') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100/80 text-indigo-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Transferencias</span>
                                <span class="text-[10px] text-slate-400">Cadena de custodia</span>
                            </div>
                        </a>

                        <a href="{{ url('/prestamo-tec') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-purple-100/80 text-purple-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Préstamos</span>
                                <span class="text-[10px] text-slate-400">Salidas temporales</span>
                            </div>
                        </a>

                        <a href="{{ url('/mantenimiento-tec') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-amber-100/80 text-amber-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Mantenimiento</span>
                                <span class="text-[10px] text-slate-400">Taller y reparaciones</span>
                            </div>
                        </a>

                        <a href="{{ url('/salida-tec') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-rose-100/80 text-rose-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Bajas y Salidas</span>
                                <span class="text-[10px] text-slate-400">Disposición final / RAEE</span>
                            </div>
                        </a>

                        <a href="{{ url('/trazabilidad-tec') }}" 
                           class="p-3 bg-blue-900 hover:bg-blue-950 text-white rounded-xl shadow-xs transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-white block leading-tight">Trazabilidad</span>
                                <span class="text-[10px] text-blue-200">Timeline integral</span>
                            </div>
                        </a>
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>Sistema de Gestión Patrimonial VIN — UNT</span>
                        <span class="font-mono text-[10px]">Versión 2.0 Estable</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- ================================================================= -->
        <!-- SECCIÓN 2: GESTIÓN DE ÚTILES E INSUMOS DE OFICINA                 -->
        <!-- ================================================================= -->
        <div class="space-y-6 pt-2 border-t border-slate-200">

            {{-- TÍTULO DE LA SECCIÓN DE ÚTILES --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-2 h-6 bg-emerald-600 rounded-xs"></span>
                    <div>
                        <h3 class="font-bold text-base text-slate-900 uppercase tracking-tight">
                            Gestión y Consumo de Útiles de Oficina
                        </h3>
                        <p class="text-xs text-slate-500">Control de stock interno, requerimiento anual y entregas a personal</p>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200">
                    Año {{ date('Y') }}
                </span>
            </div>

            <!-- 1. BLOQUE DE TARJETAS KPI: ÚTILES DE OFICINA -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3.5">
                
                {{-- KPI 1: CATÁLOGO TOTAL DE ARTÍCULOS --}}
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between hover:border-slate-300 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Catálogo</span>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-slate-900 font-mono leading-none">{{ $totalCatalogoUtiles }}</div>
                        <span class="text-[10px] text-slate-400 font-medium mt-1 block">Artículos registrados</span>
                    </div>
                </div>

                {{-- KPI 2: TOTAL UNIDADES FÍSICAS EN ARMARIO --}}
                <div class="bg-white p-4 rounded-xl border border-emerald-100 shadow-xs flex flex-col justify-between hover:border-emerald-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Stock Total</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-emerald-700 font-mono leading-none">{{ $stockFisicoTotal }}</div>
                        <span class="text-[10px] text-emerald-600/80 font-medium mt-1 block">Unidades en armario</span>
                    </div>
                </div>

                {{-- KPI 3: ARTÍCULOS POR AGOTARSE (CRÍTICOS) --}}
                <div class="bg-white p-4 rounded-xl border border-amber-100 shadow-xs flex flex-col justify-between hover:border-amber-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Por Agotarse</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-amber-700 font-mono leading-none">{{ $utilesCriticos }}</div>
                        <span class="text-[10px] text-amber-600/80 font-medium mt-1 block">Stock &le; mínimo</span>
                    </div>
                </div>

                {{-- KPI 4: ARTÍCULOS AGOTADOS --}}
                <div class="bg-white p-4 rounded-xl border border-rose-100 shadow-xs flex flex-col justify-between hover:border-rose-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Agotados</span>
                        <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-rose-700 font-mono leading-none">{{ $utilesAgotados }}</div>
                        <span class="text-[10px] text-rose-600/80 font-medium mt-1 block">Sin existencias (0)</span>
                    </div>
                </div>

                {{-- KPI 5: CUMPLIMIENTO ABASTECIMIENTO --}}
                <div class="bg-white p-4 rounded-xl border border-blue-100 shadow-xs flex flex-col justify-between hover:border-blue-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Abastecimiento</span>
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-blue-700 font-mono leading-none">{{ $pCumplimientoReq }}%</div>
                        <span class="text-[10px] text-blue-600/80 font-medium mt-1 block">{{ $totalRecibidoAnual }} de {{ $totalSolicitadoAnual }} recibidos</span>
                    </div>
                </div>

                {{-- KPI 6: ENTREGAS A PERSONAL (ESTE MES) --}}
                <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-xs flex flex-col justify-between hover:border-indigo-200 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Entregas Mes</span>
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-2xl font-bold text-indigo-700 font-mono leading-none">{{ $totalEntregasMes }}</div>
                        <span class="text-[10px] text-indigo-600/80 font-medium mt-1 block">Vales de consumo</span>
                    </div>
                </div>

            </div>

            <!-- 2. BLOQUE CENTRAL: GRÁFICO DE SALUD DE STOCK + ACCESOS RÁPIDOS DE ÚTILES -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
                
                <!-- GRÁFICO CIRCULAR DE ESTADO DE ÚTILES -->
                <div class="lg:col-span-5 bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-600 rounded-xs"></span>
                            Disponibilidad Física de Útiles
                        </h3>
                        <span class="text-[10px] font-mono text-slate-400">Catálogo Actual</span>
                    </div>

                    <div class="py-4 flex flex-col sm:flex-row items-center justify-center gap-6">
                        <div class="relative w-44 h-44 shrink-0 flex items-center justify-center">
                            <canvas id="donutChartUtiles"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-bold font-mono text-slate-800 leading-none">{{ $totalCatalogoUtiles }}</span>
                                <span class="text-[9px] uppercase font-bold text-slate-400 mt-0.5">Artículos</span>
                            </div>
                        </div>

                        <div class="space-y-2.5 text-xs w-full sm:w-auto">
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-emerald-500"></span> Disponibles
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $utilesDisponibles }} <span class="text-[10px] font-normal text-slate-400">({{ $pUtilesDisp }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-amber-500"></span> Por Agotarse
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $utilesCriticos }} <span class="text-[10px] font-normal text-slate-400">({{ $pUtilesCrit }}%)</span></span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-xs bg-rose-600"></span> Agotados
                                </span>
                                <span class="font-mono font-bold text-slate-800">{{ $utilesAgotados }} <span class="text-[10px] font-normal text-slate-400">({{ $pUtilesAgot }}%)</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de progreso acumulativa de Útiles -->
                    <div class="w-full h-2 rounded-full overflow-hidden flex bg-slate-100 mt-2">
                        <div style="width: {{ $pUtilesDisp }}%" class="bg-emerald-500" title="Disponibles: {{ $pUtilesDisp }}%"></div>
                        <div style="width: {{ $pUtilesCrit }}%" class="bg-amber-500" title="Por Agotarse: {{ $pUtilesCrit }}%"></div>
                        <div style="width: {{ $pUtilesAgot }}%" class="bg-rose-600" title="Agotados: {{ $pUtilesAgot }}%"></div>
                    </div>
                </div>

                <!-- ACCESOS DIRECTOS DEL MÓDULO DE ÚTILES -->
                <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                            <span class="w-2 h-2 bg-emerald-600 rounded-xs"></span>
                            Operaciones de Útiles y Suministros
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Acceso a las 4 subsecciones del flujo administrativo de oficina.</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 my-3">
                        
                        {{-- 1. Catálogo y Stock Actual --}}
                        <a href="{{ route('inventarioutil') }}" 
                           class="p-3 bg-slate-50 hover:bg-emerald-50/70 border border-slate-200 hover:border-emerald-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100/80 text-emerald-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-emerald-900 block leading-tight">Stock y Catálogo</span>
                                <span class="text-[10px] text-slate-400">Existencias físicas en armario</span>
                            </div>
                        </a>

                        {{-- 2. Requerimientos Anuales y Recepciones --}}
                        <a href="{{ route('requerimiento-anual') }}" 
                           class="p-3 bg-slate-50 hover:bg-blue-50/70 border border-slate-200 hover:border-blue-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-blue-100/80 text-blue-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-blue-900 block leading-tight">Requerimiento Anual</span>
                                <span class="text-[10px] text-slate-400">Control con Abastecimiento</span>
                            </div>
                        </a>

                        {{-- 3. Entregas al Personal Interno --}}
                        <a href="{{ route('entrega-personal') }}" 
                           class="p-3 bg-slate-50 hover:bg-indigo-50/70 border border-slate-200 hover:border-indigo-300 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100/80 text-indigo-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-indigo-900 block leading-tight">Entregas al Personal</span>
                                <span class="text-[10px] text-slate-400">Vales y consumo interno</span>
                            </div>
                        </a>

                        {{-- 4. Historial y Kardex --}}
                        <a href="{{ route('kardex-util') }}" 
                           class="p-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 hover:border-slate-400 rounded-xl transition flex flex-col justify-between group">
                            <div class="w-8 h-8 rounded-lg bg-slate-200 text-slate-800 flex items-center justify-center mb-2 group-hover:scale-105 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="font-bold text-xs text-slate-800 group-hover:text-slate-900 block leading-tight">Kardex e Historial</span>
                                <span class="text-[10px] text-slate-400">Auditoría y cierres anuales</span>
                            </div>
                        </a>

                    </div>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                        <span>Requerimiento {{ date('Y') }}: {{ $pCumplimientoReq }}% completado</span>
                        <span class="font-mono text-[10px] font-bold text-emerald-700">Stock Operativo</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- ================================================================= -->
        <!-- 3. BLOQUE INFERIOR: MONITOREO DE EVENTOS CRÍTICOS Y SUMINISTROS    -->
        <!-- ================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- TABLA DE MONITOREO: ACTIVOS EN MANTENIMIENTO TÉCNICO -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Equipos en Revisión Técnica / Taller ({{ $mantenimiento }})
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">Seguimiento activo</span>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-slate-100 text-xs">
                        @forelse(\App\Models\Tecnologia::where('estado', 'En Mantenimiento')->with('mantenimientos')->get() as $tech)
                            @php $ultMant = $tech->mantenimientos->sortByDesc('id')->first(); @endphp
                            <li class="py-2.5 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 truncate">{{ $tech->nombre }} ({{ $tech->marca }})</div>
                                    <div class="text-[11px] text-slate-500 font-mono truncate">
                                        {{ $tech->codigo_vin }} &bull; Falla: {{ $ultMant ? substr($ultMant->motivo, 0, 40) . '...' : 'En revisión' }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 bg-amber-50 border border-amber-200 text-amber-800 font-bold text-[10px] rounded-md font-mono shrink-0">
                                    {{ $ultMant?->taller_proveedor ?? 'Taller Interno' }}
                                </span>
                            </li>
                        @empty
                            <li class="py-6 text-center text-slate-400 font-mono text-xs">
                                No hay activos tecnológicos en taller en este momento.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- TABLA DE ALERTA: SUMINISTROS CRÍTICOS EN ALMACÉN -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Suministros Críticos en Almacén (Stock &le; Mínimo)
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">Alerta de reposición</span>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-slate-100 text-xs">
                        @forelse(\App\Models\utiles::whereColumn('stock_actual', '<=', 'stock_minimo')->orderBy('stock_actual', 'asc')->get() as $util)
                            <li class="py-2.5 flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-medium text-slate-800 truncate block">{{ $util->nombre }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $util->marca ?? 'S/M' }} &bull; Alerta mín: {{ $util->stock_minimo }}</span>
                                </div>
                                <span class="font-mono font-bold {{ $util->stock_actual <= 0 ? 'text-rose-700 bg-rose-50 border-rose-200' : 'text-amber-700 bg-amber-50 border-amber-200' }} px-2 py-0.5 rounded-md border text-xs shrink-0">
                                    {{ $util->stock_actual }} {{ $util->unidad }}
                                </span>
                            </li>
                        @empty
                            <li class="py-6 text-center text-slate-400 italic text-xs">
                                Stock suficiente en todos los artículos de almacén.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>

    </div>

    <!-- SCRIPT DE INICIALIZACIÓN DE LOS GRÁFICOS (CHART.JS) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Gráfico Donut de Parque Tecnológico
            const ctxTec = document.getElementById('donutChartInventario');
            if (ctxTec) {
                new Chart(ctxTec, {
                    type: 'doughnut',
                    data: {
                        labels: ['Disponibles', 'Asignados', 'En Mantenimiento', 'Prestados', 'De Baja'],
                        datasets: [{
                            data: [
                                {{ $disponibles }},
                                {{ $asignados }},
                                {{ $mantenimiento }},
                                {{ $prestados }},
                                {{ $deBaja }}
                            ],
                            backgroundColor: [
                                '#10b981', // Verde esmeralda (Disponibles)
                                '#2563eb', // Azul (Asignados)
                                '#f59e0b', // Ámbar (Mantenimiento)
                                '#9333ea', // Morado (Prestados)
                                '#e11d48'  // Rosa/Rojo (De baja)
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const total = {{ $totalTecnologia }};
                                        const value = context.raw || 0;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Gráfico Donut de Salud de Stock de Útiles
            const ctxUtiles = document.getElementById('donutChartUtiles');
            if (ctxUtiles) {
                new Chart(ctxUtiles, {
                    type: 'doughnut',
                    data: {
                        labels: ['Disponibles', 'Por Agotarse', 'Agotados'],
                        datasets: [{
                            data: [
                                {{ $utilesDisponibles }},
                                {{ $utilesCriticos }},
                                {{ $utilesAgotados }}
                            ],
                            backgroundColor: [
                                '#10b981', // Verde (Disponibles)
                                '#f59e0b', // Ámbar (Por agotarse)
                                '#e11d48'  // Rojo (Agotados)
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const total = {{ $totalCatalogoUtiles }};
                                        const value = context.raw || 0;
                                        const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>