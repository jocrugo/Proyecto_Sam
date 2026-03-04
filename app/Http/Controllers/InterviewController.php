<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\Message;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InterviewController extends Controller
{
    public function index(): View
    {
        $interviews = Interview::orderByDesc('created_at')->paginate(10);

        return view('inicio', [
            'interviews' => $interviews,
        ]);
    }

    public function create(): View
    {
        return view('entrevistas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'interviewer_name' => ['required', 'string', 'max:255'],
            'interviewee_name' => ['required', 'string', 'max:255'],
            'interviewee_label' => ['nullable', 'string', 'in:entrevistado,entrevistada'],
            'scheduled_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);
        $data['interviewee_label'] = $data['interviewee_label'] ?? 'entrevistada';

        $interview = Interview::create($data);

        $dateTime = $interview->scheduled_at ?? $interview->created_at;
        $title = $interview->title ?: 'Sin título';
        $formattedDate = $dateTime ? $dateTime->format('d/m/Y H:i') : 'sin fecha';

        return redirect()
            ->route('home')
            ->with('success', "Entrevista \"{$title}\" creada para {$formattedDate}.");
    }

    public function show(Interview $interview)
    {
        $interview->load(['messages' => function ($query) {
            $query->orderByRaw('COALESCE(position, 0)')
                ->orderBy('id');
        }]);

        return view('entrevista', [
            'interview' => $interview,
            'messages' => $interview->messages,
        ]);
    }

    public function edit(Interview $interview): View
    {
        return view('entrevistas.edit', compact('interview'));
    }

    public function update(Request $request, Interview $interview): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'interviewer_name' => ['required', 'string', 'max:255'],
            'interviewee_name' => ['required', 'string', 'max:255'],
            'interviewee_label' => ['nullable', 'string', 'in:entrevistado,entrevistada'],
            'scheduled_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);
        $data['interviewee_label'] = $data['interviewee_label'] ?? $interview->interviewee_label ?? 'entrevistada';

        $interview->update($data);

        return redirect()
            ->route('home')
            ->with('success', 'Entrevista actualizada correctamente.');
    }

    public function destroy(Interview $interview): RedirectResponse
    {
        $interview->delete();

        return redirect()
            ->route('home')
            ->with('success', 'Entrevista eliminada correctamente.');
    }

    public function storeMessages(Request $request, Interview $interview): RedirectResponse
    {
        $data = $request->validate([
            'starter_role' => ['required', 'in:interviewer,interviewee'],
            'lines' => ['required', 'array'],
            'lines.*' => ['nullable', 'string'],
        ]);

        $lines = array_values(array_filter($data['lines'], function ($line) {
            return $line !== null && trim($line) !== '';
        }));

        if (count($lines) === 0) {
            return back()->withErrors([
                'lines' => 'Agrega al menos una línea de conversación.',
            ]);
        }

        $currentMax = $interview->messages()->max('position') ?? 0;
        $nextPosition = (int) $currentMax;

        foreach ($lines as $index => $content) {
            $role = $index % 2 === 0
                ? $data['starter_role']
                : ($data['starter_role'] === 'interviewer' ? 'interviewee' : 'interviewer');

            Message::create([
                'interview_id' => $interview->id,
                'sender_role' => $role,
                'content' => $content,
                'position' => ++$nextPosition,
            ]);
        }

        // Normalizar posiciones consecutivas
        $this->resequenceMessagePositions($interview);

        return redirect()
            ->route('interviews.show', $interview)
            ->with('success', 'Conversación guardada correctamente.');
    }

    public function updateMessages(Request $request, Interview $interview): RedirectResponse
    {
        $data = $request->validate([
            'messages' => ['required', 'array'],
            'messages.*.content' => ['nullable', 'string'],
            'messages.*.position' => ['nullable', 'integer', 'min:1'],
            'extra_lines' => ['nullable', 'array'],
            'extra_lines.*' => ['nullable', 'string'],
            'extra_roles' => ['nullable', 'array'],
            'extra_roles.*' => ['nullable', 'in:interviewer,interviewee'],
            'messages_to_delete' => ['nullable', 'array'],
            'messages_to_delete.*' => ['integer'],
        ]);

        foreach ($data['messages'] as $messageId => $payload) {
            $content = $payload['content'] ?? null;

            $message = $interview->messages()->where('id', $messageId)->first();
            if (! $message) {
                continue;
            }

            $message->content = $content;
            if (isset($payload['position'])) {
                $message->position = (int) $payload['position'];
            }
            $message->save();
        }

        // Marcar mensajes para eliminación
        $toDelete = $data['messages_to_delete'] ?? [];
        if (! empty($toDelete)) {
            Message::where('interview_id', $interview->id)
                ->whereIn('id', $toDelete)
                ->delete();
        }

        $extraContents = $data['extra_lines'] ?? [];
        $extraRoles = $data['extra_roles'] ?? [];

        $hasExtra = false;
        foreach ($extraContents as $index => $rawContent) {
            $content = $rawContent !== null ? trim((string) $rawContent) : '';
            if ($content === '') {
                continue;
            }

            $role = $extraRoles[$index] ?? null;
            if (! in_array($role, ['interviewer', 'interviewee'], true)) {
                $role = 'interviewer';
            }

            $hasExtra = true;

            Message::create([
                'interview_id' => $interview->id,
                'sender_role' => $role,
                'content' => $content,
                // posición se normaliza después
            ]);
        }

        // Normalizar posiciones consecutivas
        $this->resequenceMessagePositions($interview);

        return redirect()
            ->route('interviews.show', $interview)
            ->with('success', 'Conversación actualizada correctamente.');
    }

    /**
     * Reasigna posiciones consecutivas a todos los mensajes de la entrevista
     * respetando el orden actual (position, luego id).
     */
    protected function resequenceMessagePositions(Interview $interview): void
    {
        $all = $interview->messages()
            ->orderByRaw('COALESCE(position, 0)')
            ->orderBy('id')
            ->get();

        $pos = 0;

        /** @var \App\Models\Message $msg */
        foreach ($all as $msg) {
            $msg->position = ++$pos;
            $msg->save();
        }
    }

    public function exportPdf(Interview $interview): Response
    {
        $interview->load(['messages' => function ($query) {
            $query->orderByRaw('COALESCE(position, 0)')
                ->orderBy('id');
        }]);

        $dateTime = $interview->scheduled_at ?? $interview->created_at;

        $pdf = Pdf::loadView('entrevistas.pdf', [
            'interview' => $interview,
            'messages' => $interview->messages,
            'dateTime' => $dateTime,
        ])->setPaper('a4', 'portrait');

        $safeTitle = $interview->title ?: ($interview->interviewer_name . '-' . $interview->interviewee_name);
        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $safeTitle) ?: 'entrevista';

        return $pdf->download("entrevista_{$safeTitle}.pdf");
    }
}

