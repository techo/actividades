<nav class="navbar navbar-expand-md navbar-dark bg-techo-blue">
    <div class="container">
        <a class="navbar-brand" href="/"><img class="techo-logo" src="{{ asset('/img/techo-logo_269x83.png') }}" alt="Techo"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link text-uppercase" href="/actividades">Actividades <span class="sr-only">(current)</span></a>
            
                </li>
            </ul>
        </div>
        @auth
        <login ref="login"
               usuario="{{Auth::user()}}"
               veradmin="{{ Auth::user()->hasPermissionTo('ver_backoffice') }}"
        ></login>
        @endauth
        @guest
        <login ref="login" showlogin="{{empty($showLogin) ? "" : "1" }}"></login>
        @endguest
    </div>
</nav>
