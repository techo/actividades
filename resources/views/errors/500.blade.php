@extends('errors.layout')

@section('title', __('errors.e500.title'))
@section('code', __('errors.code', ['n' => 500]))

@section('message')
    {{ __('errors.e500.message') }}
@endsection

@section('actions')
    @if (!empty($puedeReportar) && !empty($reportUrl))
        <a href="{{ $reportUrl }}" class="err-btn err-btn-primary">{{ __('errors.e500.report') }}</a>
    @endif
@endsection

@section('below')
    @if (!empty($puedeReportar))
        <p class="err-ref">{{ __('errors.e500.report_hint') }}</p>
    @endif
@endsection
