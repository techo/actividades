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
            <div class="container pt-4 mb-5">
                @if (isset($requiere_auth))
                    <autenticar></autenticar>
                @endif
                @yield('main_content')
            </div>        

            </main>
            @yield('footer')
        </div>
        @yield('aditional_html')
        @include('partials.scripts')
</body>
</html>
