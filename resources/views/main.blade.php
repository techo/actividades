<!doctype html>
<html lang="en">
<head>
    @include('partials.meta')
    @include('partials.css')
</head>

<body>
    @include('partials.header')

    <!-- Begin page content -->
    @yield('main_image')
    <main role="main" class="container" style="margin-bottom: 5em" id="app">
        @yield('main_content')
    </main>

    @yield('footer')

    @include('partials.scripts')
    </body>
</html>
