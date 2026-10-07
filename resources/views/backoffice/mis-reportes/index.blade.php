@extends('backoffice.main')

@section('page_title', __('backend.my_reports'))

@section('content')
    <div class="box">
        <div class="box-body">
            @if($reportes->isEmpty())
                <p class="text-muted">{{ __('backend.my_reports_empty') }}</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('backend.my_reports_date') }}</th>
                            <th>{{ __('backend.my_reports_detail') }}</th>
                            <th>{{ __('backend.my_reports_state') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportes as $r)
                            @php $visibles = $r->respuestas->where('is_internal', false); @endphp
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>{{ optional($r->created_at)->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($r->description, 90) }}</td>
                                <td>@include('backoffice.mis-reportes._estado', ['estado' => $r->status])</td>
                                <td class="text-right">
                                    <a href="/admin/mis-reportes/{{ $r->id }}" class="btn btn-sm btn-default">
                                        {{ __('backend.my_reports_view') }}
                                        @if($visibles->count())
                                            <span class="badge">{{ $visibles->count() }}</span>
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
