@php
	$n = \App\Novedad::latest('created_at')->first();
@endphp
@if(!Request::hasCookie('cookie-novedades') ||
	Request::hasCookie('cookie-novedades') && Cookie::get('cookie-novedades') != $n->id )
	<novedades></novedades>
@endif
