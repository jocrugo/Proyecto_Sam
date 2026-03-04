@extends('layouts.app')

@section('title', 'Listado de entrevistas - Proyecto Sam')

@section('content')
    <div class="card-header">
        <div class="title-group">
            <h1>Entrevistas</h1>
            <p>Gestiona tus entrevistas: crea, edita, elimina o revisa los detalles.</p>
        </div>
        <div>
            <a href="{{ route('interviews.create') }}" class="btn btn-primary">
                <span>Crear entrevista</span>
                <svg class="btn-icon" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 4V16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    <path d="M4 10H16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="grid">
        <div class="field-group" style="grid-column: 1 / -1;">
            @if (session('success'))
                <div style="margin-bottom:0.75rem; padding:0.6rem 0.75rem; border-radius:0.85rem; font-size:0.85rem; border:1px solid rgba(34,197,94,0.4); background:rgba(220,252,231,0.9); color:#166534;">
                    {{ session('success') }}
                </div>
            @endif

            @if ($interviews->isEmpty())
                <p class="helper">Aún no tienes entrevistas registradas. Crea la primera con el botón “Crear entrevista”.</p>
            @else
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid rgba(148,163,184,0.4); text-align: left;">
                                <th style="padding: 0.5rem 0.25rem;">Título</th>
                                <th style="padding: 0.5rem 0.25rem;">Entrevistadora</th>
                                <th style="padding: 0.5rem 0.25rem;">Entrevistado(a)</th>
                                <th style="padding: 0.5rem 0.25rem;">Fecha</th>
                                <th style="padding: 0.5rem 0.25rem; text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($interviews as $interview)
                                <tr style="border-bottom: 1px solid rgba(31,41,55,0.7);">
                                    <td style="padding: 0.55rem 0.25rem;">
                                        {{ $interview->title ?: 'Sin título' }}
                                    </td>
                                    <td style="padding: 0.55rem 0.25rem;">
                                        {{ $interview->interviewer_name }}
                                    </td>
                                    <td style="padding: 0.55rem 0.25rem;">
                                        {{ $interview->interviewee_name }}
                                    </td>
                                    <td style="padding: 0.55rem 0.25rem; white-space: nowrap;">
                                        @php
                                            $dateTime = $interview->scheduled_at ?? $interview->created_at;
                                        @endphp
                                        {{ $dateTime ? $dateTime->format('d/m/Y H:i') : 'Sin fecha' }}
                                    </td>
                                    <td style="padding: 0.55rem 0.25rem;">
                                        <div style="display:flex; justify-content:flex-end; gap:0.4rem;">
                                            <a href="{{ route('interviews.show', $interview) }}" class="btn btn-outline" style="padding-inline:0.7rem; font-size:0.8rem;">
                                                Ver
                                            </a>
                                            <a href="{{ route('interviews.edit', $interview) }}" class="btn btn-outline" style="padding-inline:0.7rem; font-size:0.8rem;">
                                                Editar
                                            </a>
                                            <form method="POST" action="{{ route('interviews.destroy', $interview) }}" onsubmit="return confirm('¿Eliminar esta entrevista?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline" style="padding-inline:0.7rem; font-size:0.8rem; border-color: rgba(248,113,113,0.7); color:#fecaca;">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 0.75rem;">
                    {{ $interviews->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
