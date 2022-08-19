@auth
    <login ref="login"
           usuario="{{Auth::user()}}"
           veradmin="{{ Auth::user()->hasPermissionTo('ver_backoffice') }}"
           @if(config('app.docs'))
           docs="{{ config('app.docs') }}"
           @endif
           @if(config('app.available_locales'))
           available_locales="{{ config('app.available_locales') }}"
           @endif
           showpaises="{{isset($showPaises) ? 1 : 0 }}"
           pais="{{ config('app.pais') }}"
    ></login>
@endauth
@guest
    <login ref="login"
           showlogin="{{empty($showLogin) ? "" : "1" }}"
           showpaises="{{isset($showPaises) ? 1 : 0 }}"
           available_locales="{{ config('app.available_locales') }}"
           pais="{{ config('app.pais') }}"
    ></login>
@endguest
