<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">
        <h1 class="text-2xl font-bold">
            Registro de Otros Ingresos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-xs text-emerald-200">
            Certificaciones, cuotas de ingreso, cambios de herramienta
            y otros conceptos autorizados.
        </p>
    </div>

    @if(! $cobrosHabilitados)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            <strong>Modo preparación:</strong>
            Puede consultar conceptos y calcular importes, pero el registro
            definitivo de cobros permanece deshabilitado.
        </div>
    @endif

    @if($errorCobro)
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-800">
            {{ $errorCobro }}
        </div>
    @endif

    {{-- RESULTADO DE UN COBRO --}}
    @if($ultimoRecibo)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 text-emerald-900">

            <h2 class="text-lg font-bold">
                Cobro registrado correctamente
            </h2>

            <p class="mt-2">
                Recibo N.º
                <strong>
                    {{ $ultimoRecibo['codigo'] }}-{{ str_pad((string) $ultimoRecibo['numero'], 6, '0', STR_PAD_LEFT) }}
                </strong>
            </p>

            <p>
                Concepto: {{ $ultimoRecibo['concepto'] }}
            </p>

            <p>
                Total:
                <strong>
                    Bs {{ number_format((float) $ultimoRecibo['importe'], 2, ',', '.') }}
                </strong>
            </p>
            <a
                href="{{ route('recibos-otros-ingresos.imprimir', [
                    'recibo' => $ultimoRecibo['id']
                ]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-4 inline-flex rounded-lg bg-blue-700 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800"
            >
                Imprimir recibo
            </a>

        </div>
    @endif

    {{-- FORMULARIO --}}
    <form wire:submit="previsualizar" class="space-y-6">

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-5 text-lg font-bold">
                1. Datos del ingreso
            </h2>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Concepto de ingreso
                </label>

                <select
                    wire:model.live="conceptoId"
                    required
                    class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                >
                    <option value="">Seleccione un concepto</option>

                    @foreach($conceptos as $concepto)
                        <option value="{{ $concepto->id }}">
                            {{ $concepto->nombre }}
                        </option>
                    @endforeach
                </select>

                @error('conceptoId')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                @if($conceptos->isEmpty())
                    <p class="mt-3 text-sm text-amber-700 dark:text-amber-300">
                        No existen conceptos habilitados para cobrar.
                        Primero deben completarse sus reglas y autorizar su activación.
                    </p>
                @endif
            </div>

            @if($conceptoSeleccionado)

                <div class="mt-5 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800">
                    <p>
                        <strong>Importe:</strong>
                        {{ $conceptoSeleccionado->forma_importe }}
                    </p>

                    <p class="mt-1">
                        <strong>Periodicidad:</strong>
                        {{ $conceptoSeleccionado->periodicidad }}
                    </p>

                    <p class="mt-1">
                        <strong>Afiliado obligatorio:</strong>
                        {{ $conceptoSeleccionado->requiere_afiliado ? 'Sí' : 'No' }}
                    </p>
                </div>

                <div class="mt-6">

                    <label class="mb-2 block text-sm font-semibold">
                        Buscar afiliado
                    </label>

                    <input
                        type="text"
                        wire:model.live.debounce.300ms="buscarAfiliado"
                        placeholder="Nombre, CI o número de socio..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @if($afiliadoSeleccionado)

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">

                            <div class="text-sm text-emerald-900">
                                <p class="font-semibold">
                                    {{ $afiliadoSeleccionado->nombre_completo }}
                                </p>
                                <p>
                                    CI: {{ $afiliadoSeleccionado->ci ?? '—' }}
                                    · Socio: {{ $afiliadoSeleccionado->numero_socio ?? '—' }}
                                </p>
                            </div>

                            <button
                                type="button"
                                wire:click="quitarAfiliado"
                                class="rounded-lg border border-emerald-600 px-3 py-2 text-sm font-semibold text-emerald-800"
                            >
                                Quitar
                            </button>

                        </div>
                    @endif

                    @if(
                        $conceptoSeleccionado?->codigo === 'CAMBIO_HERRAMIENTA'
                        && $afiliadoSeleccionado
                    )

                        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5">

                            <label class="mb-2 block text-sm font-bold text-emerald-900">
                                Cambio de herramienta pendiente de cobro
                            </label>

                            <select
                                wire:model.live="historialVehiculoId"
                                required
                                class="w-full rounded-lg border border-emerald-300 bg-white px-4 py-3 text-sm text-zinc-900"
                            >
                                <option value="">
                                    Seleccione un cambio registrado
                                </option>

                                @foreach($movimientosPendientes as $movimiento)
                                    <option value="{{ $movimiento->id }}">
                                        Movimiento N.º {{ str_pad(
                                            (string) $movimiento->id,
                                            6,
                                            '0',
                                            STR_PAD_LEFT
                                        ) }}
                                    </option>
                                @endforeach
                            </select>

                            @if($movimientosPendientes->isEmpty())
                                <p class="mt-3 text-sm text-amber-800">
                                    Este afiliado no tiene cambios de herramienta
                                    pendientes de cobro.
                                </p>
                            @endif

                            @error('historialVehiculoId')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    @endif
                    @if($afiliados->isNotEmpty())

                        <div class="mt-3 divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">

                            @foreach($afiliados as $afiliado)
                                <div
                                    wire:key="otro-ingreso-afiliado-{{ $afiliado->id }}"
                                    class="flex flex-wrap items-center justify-between gap-3 p-3"
                                >
                                    <div class="text-sm">
                                        <p class="font-semibold">
                                            {{ $afiliado->nombre_completo }}
                                        </p>

                                        <p class="text-xs text-zinc-500">
                                            CI: {{ $afiliado->ci ?? '—' }}
                                            · Socio: {{ $afiliado->numero_socio ?? '—' }}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        wire:click="seleccionarAfiliado({{ $afiliado->id }})"
                                        class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white"
                                    >
                                        Seleccionar
                                    </button>
                                </div>
                            @endforeach

                        </div>
                    @endif

                    @error('afiliadoSeleccionadoId')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                <div class="mt-6">
                    <label class="mb-2 block text-sm font-semibold">
                        Detalle del ingreso
                    </label>

                    <textarea
                        wire:model.live.debounce.300ms="detalle"
                        rows="3"
                        minlength="5"
                        maxlength="500"
                        required
                        placeholder="Ejemplo: Emisión de certificado de afiliación"
                        class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    ></textarea>

                    @error('detalle')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                @if($conceptoSeleccionado->forma_importe === 'VARIABLE')

                    <div class="mt-6">
                        <label class="mb-2 block text-sm font-semibold">
                            Importe a cobrar (Bs)
                        </label>

                        <input
                            type="text"
                            inputmode="decimal"
                            wire:model.live.debounce.300ms="importeVariable"
                            placeholder="Ejemplo: 50.00"
                            required
                            class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >

                        <p class="mt-2 text-xs text-zinc-500">
                            Utilice punto decimal y máximo dos decimales.
                        </p>

                        @error('importeVariable')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                @else

                    <p class="mt-5 text-sm text-emerald-700 dark:text-emerald-400">
                        La tarifa se obtendrá automáticamente de la configuración histórica.
                    </p>

                @endif

            @endif

        </div>

        @if($conceptoSeleccionado)
            <div class="flex flex-wrap justify-end gap-3">

                <button
                    type="button"
                    wire:click="cancelar"
                    class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold"
                >
                    Cancelar
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="previsualizar"
                    class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                >
                    Calcular importe
                </button>

            </div>
        @endif

    </form>

    {{-- VISTA PREVIA --}}
    @if($cotizacion)

        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

            <h2 class="mb-5 text-lg font-bold">
                2. Vista previa del cobro
            </h2>

            <div class="space-y-3 text-sm">

                <div class="flex justify-between gap-4">
                    <span>Concepto</span>
                    <strong>{{ $cotizacion['concepto'] }}</strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Afiliado</span>
                    <strong>{{ $cotizacion['afiliado'] }}</strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Detalle</span>
                    <strong class="text-right">
                        {{ $cotizacion['detalle'] }}
                    </strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Tipo de importe</span>
                    <strong>{{ $cotizacion['forma_importe'] }}</strong>
                </div>

            </div>

            <div class="mt-6 flex items-center justify-between border-t-2 border-emerald-700 pt-5">

                <span class="text-lg font-bold">
                    Total a cobrar
                </span>

                <span class="text-2xl font-bold text-emerald-700">
                    Bs {{ number_format((float) $cotizacion['importe_esperado'], 2, ',', '.') }}
                </span>

            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">

                <p class="text-xs text-zinc-500">
                    La tarifa y las condiciones se verificarán nuevamente al confirmar.
                </p>

                <button
                    type="button"
                    wire:click="procesarCobro"
                    wire:confirm="¿Confirma el registro de este cobro por Bs {{ $cotizacion['importe_esperado'] }}?"
                    wire:loading.attr="disabled"
                    wire:target="procesarCobro"
                    @disabled(! $cobrosHabilitados)
                    class="rounded-lg bg-red-700 px-6 py-3 font-bold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Confirmar y registrar cobro
                </button>

            </div>

            @if(! $cobrosHabilitados)
                <p class="mt-4 text-sm text-amber-700">
                    El cobro permanece bloqueado hasta autorizar las pruebas.
                </p>
            @endif

        </div>

    @endif
    @if(!empty($cotizacion['solicitud_reincorporacion_id']))

        <div class="flex justify-between gap-4">
            <span>Solicitud de reincorporación</span>

            <strong>
                N.º {{ str_pad(
                    (string) $cotizacion['solicitud_reincorporacion_id'],
                    6,
                    '0',
                    STR_PAD_LEFT
                ) }}
            </strong>
        </div>

    @endif
    @if(!empty($cotizacion['historial_vehiculo_id']))

        <div class="flex justify-between gap-4">
            <span>Cambio de herramienta</span>

            <strong>
                Movimiento N.º {{ str_pad(
                    (string) $cotizacion['historial_vehiculo_id'],
                    6,
                    '0',
                    STR_PAD_LEFT
                ) }}
            </strong>
        </div>

    @endif

</div>