
<footer class="footer">
<div class="container">
    <div class="row">
        <div class="col-12 col-lg-2 text-center text-lg-left mb-2 mb-lg-0">
            <img src="{{ asset('/img/logo_n.png') }}" alt="Techo" style="padding-top: 5px;">
        </div>
        <div class="col-12 col-lg-5 text-center text-lg-left mb-2 mb-lg-0" style="padding-top:10px">
            <a class="mx-1"
                href="https://www.facebook.com/TECHO.org/"
               target="_blank"
               >
               <i class="fab fa-facebook-f"></i>
            </a>
            <a class="mx-1" href="https://www.instagram.com/techo_org/"
               target="_blank"
               >
               <i class="fab fa-instagram"></i>
            </a>
            <a class="mx-1" href="https://www.linkedin.com/company/techo-teto/"
               target="_blank"
               >
               <i class="fab fa-linkedin"></i>
            </a>
            <a class="mx-1" href="https://twitter.com/techo"
               target="_blank"
               >
               <i class="fa-brands fa-x-twitter"></i>
            </a>
        </div>
        <div class="col-12 col-lg-2 text-center text-lg-left">
            <p class="pt-2 mb-1">
                &copy; {{ date('Y') }} @if(\App::getLocale() == 'pt') TETO @else TECHO @endif
            </p>
        </div>
        <div class="col-12 col-lg-3 pt-1 text-center text-lg-left">
            <a href="https://www.techo.org/politicas-de-privacidad" target="_blank">{{ __('frontend.privacy_policy') }}</a>
        </div>
        @if(config('app.env') == 'local' || config('app.env') == 'development' )
        <div class="row ml-2 mt-0">
            @php
                $gitHead = base_path('../.git/HEAD');
                if (file_exists($gitHead)) {
                    $head = explode('/', file_get_contents($gitHead));
                    echo trim($head[count($head) - 1]);
                }
            @endphp
        </div>
        @endif
    </div>

</div>
</footer>
