@extends('errors.layout')

@section('title', __('errors.e403.title'))
@section('code', __('errors.code', ['n' => 403]))

@section('message')
    {{ __('errors.e403.message') }}
@endsection
