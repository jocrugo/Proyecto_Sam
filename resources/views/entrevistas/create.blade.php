@extends('layouts.app')

@section('title', 'Configurar entrevista - Proyecto Sam')

@section('content')
    <div class="card-header">
        <div class="title-group">
            <h1>Configurar nueva entrevista</h1>
            <p>Define los datos iniciales de la sesión para generar luego la transcripción.</p>
        </div>
        <div class="badge">
            <span class="badge-dot"></span>
            Proyecto Sam · Entrevista
        </div>
    </div>

    <div class="grid">
        <div>
            <form method="POST" action="{{ route('interviews.store') }}">
                @csrf

                <div class="field-group">
                    <label for="title">Título de la entrevista</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Ej. Evaluación psicológica inicial, Entrevista clínica, etc."
                        value="{{ old('title') }}"
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
                            value="{{ old('interviewer_name') }}"
                            required
                        >
                    </div>

                    <div class="field-group">
                        <label for="interviewee_label" class="required-dot">La persona entrevistada es</label>
                        <select id="interviewee_label" name="interviewee_label" style="padding:0.55rem 0.75rem; border-radius:0.75rem; border:1px solid rgba(148,163,184,0.55); background:#f9fafb; color:#111827;">
                            <option value="entrevistada" {{ old('interviewee_label', 'entrevistada') === 'entrevistada' ? 'selected' : '' }}>Entrevistada</option>
                            <option value="entrevistado" {{ old('interviewee_label') === 'entrevistado' ? 'selected' : '' }}>Entrevistado</option>
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
                        value="{{ old('interviewee_name') }}"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="scheduled_at">Fecha y hora programadas</label>
                    <input
                        type="datetime-local"
                        id="scheduled_at"
                        name="scheduled_at"
                        value="{{ old('scheduled_at') }}"
                    >
                    <p class="helper">Úsalo si quieres registrar cuándo estaba planeada la entrevista.</p>
                </div>

                <div class="field-group">
                    <label for="description">Notas generales</label>
                    <textarea
                        id="description"
                        name="description"
                        placeholder="Contexto breve de la entrevista, objetivo, área de evaluación, etc."
                    >{{ old('description') }}</textarea>
                </div>

                <div class="actions">
                    <a href="{{ route('home') }}" class="btn btn-outline">
                        Volver al listado
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span>Guardar entrevista</span>
                        <svg class="btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 4L14 10L5 16V4Z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <aside class="right-panel">
            <div class="timeline">
                <div class="timeline-title">Flujo de trabajo</div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <span>1. Configura participantes y contexto.</span>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <span>2. Registra la conversación turno por turno.</span>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div>
                        <span>3. Genera la transcripción y el reporte final.</span>
                    </div>
                </div>
            </div>

            <div>
                <div class="timeline-title">Configuración rápida</div>
                <div class="tag-cloud">
                    <span class="chip">Entrevista clínica</span>
                    <span class="chip">Evaluación psicológica</span>
                    <span class="chip">Primera sesión</span>
                    <span class="chip">Seguimiento</span>
                </div>
            </div>

            <div>
                <div class="status-pill">
                    <span class="status-dot"></span>
                    <span class="status-text">Listo para crear una nueva entrevista</span>
                </div>
            </div>
        </aside>
    </div>
@endsection

