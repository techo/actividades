@auth
    <login ref="login" pais="{{ config('techo.nombre_pais') }}"
           usuario="{{Auth::user()}}"
           veradmin="{{ Auth::user()->hasPermissionTo('ver_backoffice') }}"
    ></login>
@endauth
@guest
    <login ref="login" pais="{{ config('techo.nombre_pais') }}"
           showlogin="{{empty($showLogin) ? "" : "1" }}"
    ></login>
@endguest