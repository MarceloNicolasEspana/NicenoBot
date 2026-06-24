@extends('admin.layout')

@section('title', 'Credenciales · '.$participant->display_name)

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Credenciales de acceso</h1>
        <div class="flex gap-2 print:hidden">
            <button onclick="window.print()" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Imprimir</button>
            <a href="{{ route('admin.nicenito.participantes.index') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Listo</a>
        </div>
    </div>

    <div class="mx-auto mt-6 max-w-md rounded-2xl border border-slate-300 bg-white p-8 text-center">
        <p class="text-sm text-slate-500">Entrega esto a:</p>
        <p class="text-lg font-bold text-slate-900">{{ $participant->display_name ?? $participant->full_name }}</p>
        @if ($participant->group_name)
            <p class="text-sm text-slate-500">{{ $participant->group_name }}</p>
        @endif

        <div class="mt-6 space-y-4">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Código de acceso</p>
                <p class="font-mono text-2xl font-bold tracking-widest text-slate-900">{{ $participant->access_code }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">PIN temporal</p>
                @if ($tempPin)
                    <p class="font-mono text-2xl font-bold tracking-widest text-slate-900">{{ $tempPin }}</p>
                @else
                    <p class="text-sm text-slate-400">Ya no es visible. Si lo necesitas, regenera el PIN.</p>
                @endif
            </div>
        </div>

        <p class="mt-6 text-xs leading-5 text-slate-500">
            Entra a <span class="font-semibold">{{ route('participant.access.show') }}</span>,
            escribe tu código y PIN. En el primer ingreso deberás crear tu PIN personal.
        </p>
    </div>

    @if ($tempPin)
        <p class="mt-4 text-center text-sm text-amber-700 print:hidden">
            Anota o imprime el PIN ahora: por seguridad no se volverá a mostrar.
        </p>
    @endif
@endsection
