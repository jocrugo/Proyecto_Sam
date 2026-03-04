@extends('layouts.app')

@section('title', 'Editar entrevista - Proyecto Sam')

@section('content')
    <div class="card-header">
        <div class="title-group">
            <h1>Editar entrevista</h1>
            <p>Ajusta los datos generales de la entrevista seleccionada.</p>
        </div>
        <div class="badge">
            <span class="badge-dot"></span>
            Proyecto Sam · Edición
        </div>
    </div>

    <div class="grid">
        <div>
            <form method="POST" action="{{ route('interviews.update', $interview) }}">
                @csrf
                @method('PUT')

                <div class="field-group">
                    <label for="title">Título de la entrevista</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Ej. Evaluación psicológica inicial, Entrevista clínica, etc."
                        value="{{ old('title', $interview->title) }}"
                    >
                    <p class="helper">Opcional, solo para que luego puedas identificar el reporte.</p>
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label for="interviewer_name" class="required-dot">Entrevistadora</label>
                        <input
                            type="text"
                            id="interviewer_name"
                            name="interviewer_name"
                            placeholder="Nombre de quien entrevista"
                            value="{{ old('interviewer_name', $interview->interviewer_name) }}"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label for="interviewee_label" class="required-dot">La persona entrevistada es</label>
                        <select id="interviewee_label" name="interviewee_label" style="padding:0.55rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(148,163,184,0.55); background:#f9fafb; color:#111827;">
                            <option value="entrevistada" {{ old('interviewee_label', $interview->interviewee_label ?? 'entrevistada') === 'entrevistada' ? 'selected' : '' }}>Entrevistada</option>
                            <option value="entrevistado" {{ old('interviewee_label', $interview->interviewee_label ?? 'entrevistada') === 'entrevistado' ? 'selected' : '' }}>Entrevistado</option>
                        </select>
                    </div>
                </div>

                <div class="field-group">
                    <label for="interviewee_name" class="required-dot">Nombre de la persona entrevistada</label>
                    <input
                        type="text"
                        id="interviewee_name"
                        name="interviewee_name"
                        placeholder="Nombre"
                        value="{{ old('interviewee_name', $interview->interviewee_name) }}"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="scheduled_at">Fecha y hora programadas</label>
                    <input
                        type="datetime-local"
                        id="scheduled_at"
                        name="scheduled_at"
                        value="{{ old('scheduled_at', optional($interview->scheduled_at)->format('Y-m-d\TH:i')) }}"
                    >
                    <p class="helper">Úsalo si quieres registrar cuándo estaba planeada la entrevista.</p>
                </div>

                <div class="field-group">
                    <label for="description">Notas generales</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Contexto breve de la entrevista, objetivo, área de evaluación, etc."
                    >{{ old('description', $interview->description) }}</textarea>
                </div>

                <div class="actions">
                    <a href="{{ route('home') }}" class="btn btn-outline">
                        Volver al listado
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </form>
        </div>

        <aside class="right-panel">
            <div class="timeline">
                <div class="timeline-title">Consejo</div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <span>Procura que el título y las notas te ayuden a identificar la sesión en tus reportes.</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection

