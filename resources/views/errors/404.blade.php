@extends('errors.layout')

@section('title', __('errors.e404.title'))
@section('code', __('errors.code', ['n' => 404]))

@section('message')
    {{ __('errors.e404.message') }}
@endsection
