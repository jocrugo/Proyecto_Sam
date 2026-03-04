@extends('layouts.app')

@section('title', 'Entrevista: ' . ($interview->title ?? ($interview->interviewer_name . ' / ' . $interview->interviewee_name)))

@section('content')
    <div class="card-header">
        <div class="title-group">
            <h1>{{ $interview->title ?: 'Sin título' }}</h1>
            <p>{{ $interview->interviewer_name }} · {{ $interview->interviewee_name }}</p>
            @php
                $dateTime = $interview->scheduled_at ?? $interview->created_at;
            @endphp
            @if ($dateTime)
                <p class="helper" style="margin-top:0.2rem;">
                    {{ $dateTime->format('d/m/Y H:i') }}
                </p>
            @endif
        </div>
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.4rem;">
            <div class="badge">
                <span class="badge-dot"></span>
                Conversación
            </div>
            @if ($messages->isNotEmpty())
                <div style="display:flex; gap:0.4rem;">
                    <button type="button" class="btn btn-outline" id="toggle-edit-conversation" style="padding-inline:0.9rem; font-size:0.8rem;">
                        Editar conversación
                    </button>
                    <a href="{{ route('interviews.export.pdf', $interview) }}"
                       class="btn btn-outline"
                       style="padding-inline:0.9rem; font-size:0.8rem;"
                       target="_blank" rel="noopener">
                        Exportar PDF
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="grid">
        <div class="field-group" style="grid-column: 1 / -1;">
            @if ($messages->isNotEmpty())
                <form method="POST"
                      action="{{ route('interviews.messages.update', $interview) }}"
                      id="conversation-edit-form"
                      data-editing="false"
                      style="max-height: 480px; overflow-y: auto; padding-right: 0.5rem;">
                    @csrf
                    @method('PUT')

                    @php
                        $intervieweeLabel = ($interview->interviewee_label ?? 'entrevistada') === 'entrevistado' ? 'Entrevistado' : 'Entrevistada';
                    @endphp
                    @foreach ($messages as $message)
                        @php
                            $isInterviewer = $message->sender_role === 'interviewer';
                        @endphp
                        <div class="conversation-message-wrapper"
                             data-id="{{ $message->id }}"
                             data-role="{{ $message->sender_role }}"
                             style="display:flex; margin-bottom:0.75rem; {{ $isInterviewer ? 'justify-content:flex-start;' : 'justify-content:flex-end;' }}">
                            <div style="max-width:68%; padding:0.6rem 0.8rem; border-radius:1rem;
                                {{ $isInterviewer
                                    ? 'background:#e0f2fe; color:#0f172a; border:1px solid rgba(59,130,246,0.3);'
                                    : 'background:#fef3c7; color:#78350f; border:1px solid rgba(245,158,11,0.4);' }}">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.15rem; opacity:0.8; font-size:0.75rem; font-weight:600;">
                                    <span>{{ $isInterviewer ? 'Entrevistadora' : $intervieweeLabel }}</span>
                                    <button type="button"
                                            class="message-menu-trigger"
                                            data-message-id="{{ $message->id }}"
                                            data-message-role="{{ $message->sender_role }}">
                                        ···
                                    </button>
                                </div>
                                <div class="message-menu" data-message-id="{{ $message->id }}" style="display:none;">
                                    <button type="button" data-action="add-interviewer" data-message-id="{{ $message->id }}">
                                        Agregar turno como entrevistadora
                                    </button>
                                    <button type="button" data-action="add-interviewee" data-message-id="{{ $message->id }}">
                                        Agregar turno como {{ strtolower($intervieweeLabel) }}
                                    </button>
                                    <button type="button" data-action="delete-message" data-message-id="{{ $message->id }}">
                                        Eliminar mensaje
                                    </button>
                                </div>
                                <textarea
                                    name="messages[{{ $message->id }}][content]"
                                    class="conversation-edit-text"
                                    data-role="{{ $message->sender_role }}"
                                    data-original="{{ old("messages.{$message->id}.content", $message->content) }}"
                                    style="width:100%; border-radius:0.75rem; border:1px solid transparent; padding:0.45rem 0.55rem; font-size:0.9rem; resize:vertical; background:transparent; cursor:default;"
                                >{{ old("messages.{$message->id}.content", $message->content) }}</textarea>
                                <input type="hidden"
                                       name="messages[{{ $message->id }}][position]"
                                       class="conversation-position"
                                       value="{{ $message->position ?? $loop->iteration }}">
                            </div>
                        </div>
                    @endforeach

                    <div id="conversation-extra-lines"
                         data-interviewer-label="Entrevistadora"
                         data-interviewee-label="{{ $intervieweeLabel }}"
                         style="margin-top:0.75rem;">
                        {{-- nuevos turnos se agregarán aquí desde JS --}}
                    </div>

                    <div id="conversation-deletes"></div>

                    <div class="actions" id="conversation-edit-actions" style="margin-top: 1rem; display:none;">
                        <button type="button" class="btn btn-outline" id="cancel-edit-conversation">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Guardar cambios de conversación
                        </button>
                    </div>
                </form>
            @else
                <p class="helper">Aún no hay mensajes registrados para esta entrevista.</p>

                <form method="POST" action="{{ route('interviews.messages.store', $interview) }}" id="conversation-form" style="margin-top: 1rem;">
                    @csrf

                    <div class="field-row">
                        <div class="field-group">
                            @php
                                $intervieweeLabelForm = ($interview->interviewee_label ?? 'entrevistada') === 'entrevistado' ? 'Entrevistado' : 'Entrevistada';
                            @endphp
                            <label for="starter_role" class="required-dot">Quién inicia la conversación</label>
                            <select id="starter_role" name="starter_role" style="padding:0.55rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(148,163,184,0.55); background:#f9fafb; color:#111827;">
                                <option value="interviewer">Entrevistadora</option>
                                <option value="interviewee">{{ $intervieweeLabelForm }}</option>
                            </select>
                            <p class="helper">Los turnos se irán alternando automáticamente: uno y uno.</p>
                        </div>
                    </div>

                    <div id="conversation-lines"
                         data-interviewer-label="Entrevistadora"
                         data-interviewee-label="{{ $intervieweeLabelForm }}">
                        <div class="field-group conversation-line" data-index="0">
                            <label class="conversation-label">Entrevistadora</label>
                            <textarea
                                name="lines[]"
                                class="conversation-text"
                                placeholder="Escribe el primer turno y presiona Enter para agregar el siguiente..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            Guardar conversación
                        </button>
                    </div>
                </form>
            @endif

            <div class="actions" style="margin-top: 1.25rem;">
                <a href="{{ route('home') }}" class="btn btn-outline">
                    Volver al listado
                </a>
            </div>
        </div>
    </div>
@endsection

