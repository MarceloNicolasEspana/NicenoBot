@php
    $isEdit = $mode === 'edit';
    $action = $isEdit
        ? route('admin.nicenito.participantes.update', $participant)
        : route('admin.nicenito.participantes.store');
@endphp

<div class="p-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold" style="color: var(--admin-text);">{{ $isEdit ? 'Editar participante' : 'Nuevo participante' }}</h2>
        <button type="button" data-modal-close aria-label="Cerrar" class="text-xl leading-none" style="color: var(--admin-text-soft);">&times;</button>
    </div>

    @unless ($isEdit)
        <p class="mt-2 text-sm" style="color: var(--admin-text-soft);">El código y un PIN temporal se generan automáticamente al guardar.</p>
    @endunless

    <div data-form-errors class="mt-4 hidden rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"></div>

    <form data-ajax-form method="POST" action="{{ $action }}" class="mt-4 space-y-4">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div>
            <label class="admin-label">Nombre completo <span class="font-normal" style="color: var(--admin-text-soft);">(solo el equipo)</span></label>
            <input name="full_name" value="{{ $participant->full_name }}" required class="admin-input mt-1">
        </div>
        <div>
            <label class="admin-label">Nombre visible <span class="font-normal" style="color: var(--admin-text-soft);">(ej: Martín P.)</span></label>
            <input name="display_name" value="{{ $participant->display_name }}" class="admin-input mt-1">
        </div>
        <div>
            <label class="admin-label">Grupo</label>
            <input name="group_name" value="{{ $participant->group_name }}" placeholder="Confirmación 2026" class="admin-input mt-1">
        </div>
        <label class="flex items-center gap-2 text-sm" style="color: var(--admin-text);">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked($participant->is_active ?? true)>
            Activo
        </label>

        <div class="flex justify-end gap-2 border-t pt-4" style="border-color: var(--admin-card-border);">
            <button type="button" data-modal-close class="btn-secondary">Cancelar</button>
            <button type="submit" class="btn-primary">{{ $isEdit ? 'Guardar cambios' : 'Crear y generar credenciales' }}</button>
        </div>
    </form>
</div>
