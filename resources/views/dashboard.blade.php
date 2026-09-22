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

    {{-- CONSULTA AGREGADA EFICIENTE (1 SOLA CONSULTA SQL) --}}
    @php
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

        // Porcentajes para gráfico y barras
        $pDisponibles   = $totalTecnologia > 0 ? round(($disponibles / $totalTecnologia) * 100, 1) : 0;
        $pAsignados     = $totalTecnologia > 0 ? round(($asignados / $totalTecnologia) * 100, 1) : 0;
        $pMantenimiento = $totalTecnologia > 0 ? round(($mantenimiento / $totalTecnologia) * 100, 1) : 0;
        $pDeBaja        = $totalTecnologia > 0 ? round(($deBaja / $totalTecnologia) * 100, 1) : 0;
        $pPrestados     = $totalTecnologia > 0 ? round(($prestados / $totalTecnologia) * 100, 1) : 0;
    @endphp

    <div class="space-y-6 pb-6">

        <!-- ================================================================= -->
        <!-- 1. BLOQUE DE TARJETAS KPI (ESTADOS ASOCIADOS POR COLOR)           -->
        <!-- ================================================================= -->
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

        <!-- ================================================================= -->
        <!-- 2. BLOQUE CENTRAL: GRÁFICO CIRCULAR + ACCESOS RÁPIDOS             -->
        <!-- ================================================================= -->
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

                <!-- Gráfico Donut Renderizado (Chart.js con respaldo interactivo) -->
                <div class="py-4 flex flex-col sm:flex-row items-center justify-center gap-6">
                    <div class="relative w-44 h-44 shrink-0 flex items-center justify-center">
                        <canvas id="donutChartInventario"></canvas>
                        <!-- Texto central dentro del donut -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-bold font-mono text-slate-800 leading-none">{{ $totalTecnologia }}</span>
                            <span class="text-[9px] uppercase font-bold text-slate-400 mt-0.5">Activos</span>
                        </div>
                    </div>

                    <!-- Leyenda Detallada con porcentajes exactos -->
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

                <!-- Barra de Progreso Acumulativo -->
                <div class="w-full h-2 rounded-full overflow-hidden flex bg-slate-100 mt-2">
                    <div style="width: {{ $pDisponibles }}%" class="bg-emerald-500" title="Disponibles: {{ $pDisponibles }}%"></div>
                    <div style="width: {{ $pAsignados }}%" class="bg-blue-600" title="Asignados: {{ $pAsignados }}%"></div>
                    <div style="width: {{ $pMantenimiento }}%" class="bg-amber-500" title="Mantenimiento: {{ $pMantenimiento }}%"></div>
                    <div style="width: {{ $pPrestados }}%" class="bg-purple-600" title="Prestados: {{ $pPrestados }}%"></div>
                    <div style="width: {{ $pDeBaja }}%" class="bg-rose-600" title="De Baja: {{ $pDeBaja }}%"></div>
                </div>
            </div>

            <!-- ACCESOS DIRECTOS DE MONITOREO (6 TARJETAS VISUALES) -->
            <div class="lg:col-span-7 bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-900 rounded-xs"></span>
                        Módulos de Consulta y Supervisión Rápida
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Acceso directo a las vistas de seguimiento y auditoría operativa.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 my-3">
                    
                    {{-- 1. Catálogo General --}}
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

                    {{-- 2. Transferencias y Asignaciones --}}
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

                    {{-- 3. Préstamos Temporales --}}
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

                    {{-- 4. Mantenimiento y Soporte --}}
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

                    {{-- 5. Bajas Definitivas / Salidas --}}
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

                    {{-- 6. Trazabilidad Completa (Destacada) --}}
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

            <!-- TABLA DE ALERTA: SUMINISTROS CRÍTICOS (STOCK <= 5) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-50 border-b border-slate-200 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Suministros Críticos en Almacén (Stock &le; 5)
                    </span>
                    <span class="text-[10px] font-mono text-slate-400">Alerta de reposición</span>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-slate-100 text-xs">
                        @forelse(\App\Models\Util::where('cantidad', '<=', 5)->get() as $util)
                            <li class="py-2.5 flex items-center justify-between gap-2">
                                <span class="font-medium text-slate-800 truncate">{{ $util->nombre }}</span>
                                <span class="font-mono font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200 text-xs shrink-0">
                                    {{ $util->cantidad }} {{ $util->unidad }}
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

    <!-- SCRIPT DE INICIALIZACIÓN DEL GRÁFICO DONUT (CHART.JS) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('donutChartInventario');
            if (ctx) {
                new Chart(ctx, {
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
        });
    </script>
</x-app-layout>