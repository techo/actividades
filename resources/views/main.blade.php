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
        <main role="main" class="container" style="margin-bottom: 5em">
            @yield('main_content')
        </main>

        @yield('footer')
    </div>
    @include('partials.scripts')
</body>
</html>
