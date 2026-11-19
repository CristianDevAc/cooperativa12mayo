<div class="mx-auto w-full max-w-7xl space-y-6 pb-10">

    {{-- ENCABEZADO --}}
    <div class="rounded-2xl bg-gradient-to-r from-emerald-950 to-emerald-800 p-6 text-white shadow-lg">

        <h1 class="text-2xl font-bold">
            Registro de Egresos
        </h1>

        <p class="mt-2 text-sm text-emerald-100">
            Cooperativa de Transportes 12 de Mayo R.L.
        </p>

        <p class="mt-2 text-sm text-emerald-100">
            Control de pagos y gastos realizados.
        </p>

    </div>

    {{-- INTERRUPTOR DE SEGURIDAD --}}
    @if(! $pagosHabilitados)
        <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            <strong>Modo de preparación:</strong>
            puede registrar los datos y revisar la vista previa,
            pero todavía no se permite guardar egresos definitivos.
        </div>
    @endif

    @if($errorOperacion)
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-sm font-semibold text-red-800">
            {{ $errorOperacion }}
        </div>
    @endif

    {{-- EGRESO REGISTRADO --}}
    @if($ultimoEgreso)
        <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 text-emerald-900">

            <h2 class="text-lg font-bold">
                Egreso registrado correctamente
            </h2>
                        <a
                href="{{ route('egresos.imprimir', [
                    'current_team' => auth()->user()->currentTeam->slug,
                    'egreso' => $ultimoEgreso['id'],
                ]) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-4 inline-flex items-center rounded-lg
                    bg-emerald-700 px-5 py-3
                    text-sm font-bold text-white
                    hover:bg-emerald-800"
            >
                Imprimir comprobante
            </a>
            <p class="mt-3 font-bold">
                Comprobante EGR-{{ str_pad((string) $ultimoEgreso['numero'], 6, '0', STR_PAD_LEFT) }}
            </p>

            <p class="mt-2">
                Concepto: {{ $ultimoEgreso['concepto'] }}
            </p>

            <p>
                Beneficiario: {{ $ultimoEgreso['beneficiario'] }}
            </p>

            <p class="mt-2 text-xl font-bold">
                Bs {{ number_format((float) $ultimoEgreso['importe'], 2, ',', '.') }}
            </p>

        </div>
    @endif

    @if($cotizacion === null)

        {{-- FORMULARIO --}}
        <form
            wire:submit="previsualizar"
            class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
        >

            <h2 class="text-lg font-bold">
                1. Datos del egreso
            </h2>

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Concepto de egreso
                    </label>

                    <select
                        wire:model="conceptoEgresoId"
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

                    @error('conceptoEgresoId')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Beneficiario
                    </label>

                    <input
                        type="text"
                        wire:model="beneficiario"
                        required
                        maxlength="200"
                        placeholder="Persona o empresa que recibe el pago"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('beneficiario')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Importe (Bs)
                    </label>

                    <input
                        type="number"
                        min="0.01"
                        step="0.01"
                        wire:model="importe"
                        required
                        placeholder="0.00"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('importe')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Medio de pago
                    </label>

                    <select
                        wire:model="medioPago"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="DEPOSITO">Depósito</option>
                        <option value="OTRO">Otro</option>
                    </select>

                    @error('medioPago')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Detalle del gasto
                </label>

                <textarea
                    wire:model="detalle"
                    rows="3"
                    required
                    minlength="5"
                    maxlength="500"
                    placeholder="Explique qué se está pagando."
                    class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                ></textarea>

                @error('detalle')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Referencia del pago (si corresponde)
                    </label>

                    <input
                        type="text"
                        wire:model="referenciaPago"
                        maxlength="150"
                        placeholder="N.º de transferencia o depósito"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    <p class="mt-1 text-xs text-zinc-500">
                        Obligatoria para transferencias y depósitos.
                    </p>

                    @error('referenciaPago')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Documento de respaldo
                    </label>

                    <select
                        wire:model="tipoDocumento"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">Sin documento</option>
                        <option value="FACTURA">Factura</option>
                        <option value="RECIBO">Recibo</option>
                        <option value="OTRO">Otro documento</option>
                    </select>

                    @error('tipoDocumento')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold">
                        Número del documento
                    </label>

                    <input
                        type="text"
                        wire:model="numeroDocumento"
                        maxlength="100"
                        placeholder="N.º de factura o recibo"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('numeroDocumento')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">
                    Observaciones (opcional)
                </label>

                <textarea
                    wire:model="observaciones"
                    rows="2"
                    maxlength="500"
                    placeholder="Observaciones adicionales"
                    class="w-full rounded-lg border border-zinc-300 bg-white p-3 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                ></textarea>

                @error('observaciones')
                    <p class="mt-1 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            @if($conceptos->isEmpty())
                <p class="text-sm text-amber-700">
                    No hay conceptos de egreso habilitados.
                    Primero active al menos uno desde Configuración.
                </p>
            @endif

            <div class="flex flex-wrap justify-end gap-3">

                <button
                    type="button"
                    wire:click="cancelar"
                    class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-600"
                >
                    Limpiar
                </button>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="previsualizar"
                    @disabled($conceptos->isEmpty())
                    class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white hover:bg-emerald-800 disabled:opacity-50"
                >
                    Revisar egreso
                </button>

            </div>

        </form>

    @else

        {{-- VISTA PREVIA --}}
        <div class="space-y-5 rounded-xl border border-emerald-300 bg-white p-6 shadow-sm dark:border-emerald-800 dark:bg-zinc-900">

            <h2 class="text-lg font-bold">
                2. Revisar el pago antes de registrarlo
            </h2>

            <div class="space-y-3 text-sm">

                <div class="flex justify-between gap-4">
                    <span>Concepto</span>
                    <strong>{{ $cotizacion['concepto_nombre'] }}</strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Clasificación</span>
                    <strong>{{ $cotizacion['grupo_reporte'] }}</strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Beneficiario</span>
                    <strong>{{ $cotizacion['beneficiario'] }}</strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Detalle</span>
                    <strong class="max-w-sm text-right">
                        {{ $cotizacion['detalle'] }}
                    </strong>
                </div>

                <div class="flex justify-between gap-4">
                    <span>Medio de pago</span>
                    <strong>{{ $cotizacion['medio_pago'] }}</strong>
                </div>

                @if($cotizacion['referencia_pago'] !== '')
                    <div class="flex justify-between gap-4">
                        <span>Referencia</span>
                        <strong>{{ $cotizacion['referencia_pago'] }}</strong>
                    </div>
                @endif

                <div class="flex justify-between gap-4">
                    <span>Documento</span>
                    <strong>
                        {{ $cotizacion['tipo_documento'] ?? 'Sin documento' }}
                        {{ $cotizacion['numero_documento'] }}
                    </strong>
                </div>

                @if($cotizacion['observaciones'] !== '')
                    <div class="flex justify-between gap-4">
                        <span>Observaciones</span>
                        <strong class="max-w-sm text-right">
                            {{ $cotizacion['observaciones'] }}
                        </strong>
                    </div>
                @endif

            </div>

            <div class="flex items-center justify-between gap-3 border-t-2 border-emerald-700 pt-5">

                <span class="text-lg font-bold">
                    TOTAL A PAGAR
                </span>

                <span class="text-2xl font-bold text-emerald-700">
                    Bs {{ number_format((float) $cotizacion['importe'], 2, ',', '.') }}
                </span>

            </div>

            <div class="flex flex-wrap justify-end gap-3">

                <button
                    type="button"
                    wire:click="editarDatos"
                    class="rounded-lg border border-zinc-300 px-5 py-3 font-semibold dark:border-zinc-600"
                >
                    Modificar datos
                </button>

                <button
                    type="button"
                    wire:click="confirmar"
                    wire:confirm="¿Confirma que este gasto ya fue pagado y desea registrar el egreso?"
                    wire:loading.attr="disabled"
                    wire:target="confirmar"
                    @disabled(! $pagosHabilitados)
                    class="rounded-lg bg-red-700 px-6 py-3 font-bold text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Confirmar egreso
                </button>

            </div>

            @if(! $pagosHabilitados)
                <p class="text-sm text-amber-700">
                    El botón permanecerá bloqueado hasta habilitar
                    las pruebas de registro definitivo.
                </p>
            @endif

        </div>

    @endif

</div>