<!doctype html>
<html lang="en">
<head>
    @include('partials.meta')
    @include('partials.css')
</head>

<body>
    <div id="app">
        @include('partials.header')
        <main role="main">

        <!-- Begin page content -->
        @yield('main_image')
        <div class="container pt-4 pb-5 mb-5">
            @if (isset($requiere_auth))
                <autenticar></autenticar>
            @endif
            @yield('main_content')
        </div>

        @yield('footer')
        </main>
    </div>
    @yield('aditional_html')
    @include('partials.scripts')
    @stack('additional_scripts')
</body>
</html>
