<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entrevista - {{ $interview->title ?: 'Sin título' }}</title>
    <style>
        @page { margin: 25mm 20mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 6px 0;
        }

        .datetime {
            font-size: 11px;
            color: #6b7280;
            margin: 0;
        }

        .conversation { margin-top: 16px; }

        .msg {
            margin-bottom: 10px;
        }

        .msg.izq {
            text-align: left;
            padding-right: 15%;
        }

        .msg.der {
            text-align: right;
            padding-left: 15%;
        }

        .msg-role {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .msg-text {
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">{{ $interview->title ?: 'Sin título' }}</h1>
        @if ($dateTime)
            <p class="datetime">{{ $dateTime->format('d/m/Y H:i') }}</p>
        @endif
    </div>

    <div class="conversation">
        @if ($messages->isEmpty())
            <p>No hay mensajes registrados para esta entrevista.</p>
        @else
            @php
                $pdfIntervieweeLabel = ($interview->interviewee_label ?? 'entrevistada') === 'entrevistado' ? 'Entrevistado' : 'Entrevistada';
            @endphp
            @foreach ($messages as $message)
                <div class="msg {{ $message->sender_role === 'interviewer' ? 'izq' : 'der' }}">
                    <div class="msg-role">
                        {{ $message->sender_role === 'interviewer' ? 'Entrevistadora' : $pdfIntervieweeLabel }}
                    </div>
                    <div class="msg-text">
                        {{ \Illuminate\Support\Str::of($message->content)->squish() }}
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
