@extends('errors.layout')

@section('title', __('errors.e503.title'))
@section('code', __('errors.code', ['n' => 503]))

@section('message')
    {{ __('errors.e503.message') }}
@endsection
