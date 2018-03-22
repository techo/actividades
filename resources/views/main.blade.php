<!doctype html>
<html lang="en">
<head>
    @include('partials.meta')
    @include('partials.css')
</head>

<body>
    <div id="app">
        @include('partials.header')

        <!-- Begin page content -->
        @yield('main_image')
        <main role="main" class="container-fluid" style="margin-bottom: 5em">
            @if (isset($requiere_auth))
                <autenticar></autenticar>
            @endif
            @yield('main_content')
        </main>

        @yield('footer')
    </div>
    @include('partials.scripts')
</body>
</html>
