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
    ></login>
@endauth
@guest
    <login ref="login"
           showlogin="{{empty($showLogin) ? "" : "1" }}"
    ></login>
@endguest