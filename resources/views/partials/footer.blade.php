
<footer class="footer">
<div class="container">
    <div class="row">
        <div class="col-sm-3 col-lg-2">
            <img src="{{ asset('/img/logo_negro_154x41.png') }}" alt="Techo" style="padding-top: 5px;" align="left">
        </div>
        <div class="col-sm-3 col-lg-5" style="padding-top:10px">
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
               <i class="fab fa-twitter"></i>
            </a>
        </div>
        <div class="col-sm-3 col-lg-2">
            <p class="pt-2 mb-1">
                &copy; 2022 TECHO
            </p>
        </div>
        <div class="col-sm-3 pt-1">
            <a href="https://www.techo.org/politicas-de-privacidad" target="_blank">Políticas de Privacidad</a>
        </div>
        @if(config('app.env') == 'local' || config('app.env') == 'development' )
        <div class="row ml-2 mt-0">
            
            @php
                $head = explode('/',file_get_contents('../.git/HEAD'));
                echo $head[count($head)-1];
            @endphp              
        </div>
        @endif
    </div>

</div>
</footer>
