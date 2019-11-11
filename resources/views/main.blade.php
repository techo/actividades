<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>

    @if(config('app.env') == 'production' && !empty(config('app.google_analytics_token')))
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.google_analytics_token') }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', '{{ config('app.google_analytics_token') }}');
        </script>
    @endif

    @include('partials.meta')
    @include('partials.css')
</head>

<body>
    <div id="app">
        @include('partials.cookies-bar')
        @include('partials.header')
        <main role="main">

        <!-- Begin page content -->
        @yield('main_image')
        <div class="container pt-4 pb-5 mb-5">
            @if (isset($requiere_auth))
                <autenticar></autenticar>
            @endif
            @yield('main_content')
                @yield('footer')
        </div>

        </main>
    </div>
    @yield('aditional_html')
    @include('partials.scripts')
    @stack('additional_scripts')

    @if(config('app.env') == 'production' && !empty(config('app.userreport_token')))
        <script type="text/javascript">
            window._urq = window._urq || [];
            _urq.push(['initSite', '{{ config('app.userreport_token') }}']);
            (function() {
            var ur = document.createElement('script'); ur.type = 'text/javascript'; ur.async = true;
            ur.src = ('https:' == document.location.protocol ? 'https://cdn.userreport.com/userreport.js' : 'http://cdn.userreport.com/userreport.js');
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ur, s);
            })();
        </script>
    @endif

</body>
</html>
