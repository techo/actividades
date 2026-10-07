@extends('backoffice.main')

@section('page_title', __('backend.my_reports_report') . ' #' . $reporte->id)

@section('content')
    <p><a href="/admin/mis-reportes">&larr; {{ __('backend.my_reports') }}</a></p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">{{ __('backend.my_reports_what_you_reported') }}</h3>
            <div class="box-tools">@include('backoffice.mis-reportes._estado', ['estado' => $reporte->status])</div>
        </div>
        <div class="box-body">
            <p style="white-space:pre-wrap; margin:0;">{{ $reporte->description }}</p>
            <p class="text-muted" style="margin:8px 0 0; font-size:12px;">{{ optional($reporte->created_at)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">{{ __('backend.my_reports_conversation') }}</h3>
        </div>
        <div class="box-body">
            @forelse($hilo as $m)
                <div style="margin:0 0 12px; padding:10px 14px; border-radius:6px; border-left:4px solid {{ $m->esDelReportante() ? '#c3c9d0' : '#0092dd' }}; background:{{ $m->esDelReportante() ? '#f3f5f7' : '#e8f4fb' }};">
                    <div style="font-size:12px; color:#6b7280; margin-bottom:4px;">
                        <strong>{{ $m->esDelReportante() ? __('backend.my_reports_you') : ($m->author_name ?: 'TECHO') }}</strong>
                        · {{ optional($m->created_at)->format('d/m/Y H:i') }}
                    </div>
                    @if($m->body)
                        <div style="white-space:pre-wrap;">{{ $m->body }}</div>
                    @endif
                    @if($m->tipo === \App\IssueReportReply::TIPO_RESUELTO)
                        <div style="margin-top:4px; color:#1f8a4c; font-weight:600;">{{ __('backend.my_reports_marked_resolved') }}</div>
                    @endif
                </div>
            @empty
                <p class="text-muted">{{ __('backend.my_reports_no_replies') }}</p>
            @endforelse

            <form method="POST" action="/admin/mis-reportes/{{ $reporte->id }}/responder" style="margin-top:16px;">
                @csrf
                <label for="body">{{ __('backend.my_reports_reply_label') }}</label>
                <textarea id="body" name="body" class="form-control" rows="4" maxlength="5000" required>{{ old('body') }}</textarea>
                @if($errors->has('body'))
                    <p class="text-danger">{{ $errors->first('body') }}</p>
                @endif
                @if($reporte->estaCerrado())
                    <p class="text-muted" style="font-size:12px; margin:6px 0 0;">{{ __('backend.my_reports_reopen_hint') }}</p>
                @endif
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">{{ __('backend.my_reports_send') }}</button>
            </form>
        </div>
    </div>
@endsection
