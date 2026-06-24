<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(Request $request): View
    {
        $participants = Participant::query()
            ->withCount('questions')
            ->when($request->filled('group'), fn ($q) => $q->where('group_name', $request->string('group')))
            ->when($request->filled('q'), fn ($q) => $q->where('full_name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        $groups = Participant::query()->whereNotNull('group_name')->distinct()->pluck('group_name');

        return view('admin.nicenito.participantes.index', [
            'participants' => $participants,
            'groups' => $groups,
            'filters' => $request->only(['group', 'q']),
        ]);
    }

    public function create(): View
    {
        return view('admin.nicenito.participantes.form', [
            'participant' => new Participant(['is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $participant = new Participant($data);
        $participant->access_code = Participant::generateAccessCode();
        $pin = Participant::generatePin();
        $participant->setPin($pin);
        $participant->must_change_pin = true;
        $participant->save();

        return redirect()
            ->route('admin.nicenito.participantes.credentials', $participant)
            ->with('temp_pin', $pin);
    }

    public function edit(Participant $participante): View
    {
        return view('admin.nicenito.participantes.form', [
            'participant' => $participante,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, Participant $participante): RedirectResponse
    {
        $participante->update($this->validateData($request));

        return redirect()
            ->route('admin.nicenito.participantes.index')
            ->with('status', 'Participante actualizado.');
    }

    public function destroy(Participant $participante): RedirectResponse
    {
        $participante->delete();

        return redirect()
            ->route('admin.nicenito.participantes.index')
            ->with('status', 'Participante eliminado.');
    }

    public function toggleActive(Participant $participante): RedirectResponse
    {
        $participante->update(['is_active' => ! $participante->is_active]);

        return back()->with('status', $participante->is_active ? 'Participante activado.' : 'Participante desactivado.');
    }

    public function regeneratePin(Participant $participante): RedirectResponse
    {
        $pin = Participant::generatePin();
        $participante->setPin($pin);
        $participante->must_change_pin = true;
        $participante->save();

        return redirect()
            ->route('admin.nicenito.participantes.credentials', $participante)
            ->with('temp_pin', $pin);
    }

    public function regenerateCode(Participant $participante): RedirectResponse
    {
        $participante->update(['access_code' => Participant::generateAccessCode()]);

        return redirect()
            ->route('admin.nicenito.participantes.credentials', $participante)
            ->with('status', 'Código regenerado. El PIN no cambió.');
    }

    /**
     * Vista imprimible con el código y el PIN temporal. El PIN solo se muestra
     * una vez (flash de sesión); al refrescar ya no aparece.
     */
    public function credentials(Request $request, Participant $participante): View
    {
        return view('admin.nicenito.participantes.credentials', [
            'participant' => $participante,
            'tempPin' => $request->session()->get('temp_pin'),
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:60'],
            'group_name' => ['nullable', 'string', 'max:80'],
            'is_active' => ['sometimes', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
