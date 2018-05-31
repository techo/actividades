<div class="modal fade" id="compartirModal" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body" style="padding: 1px">
                <div class="">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1; margin-top: -1em">
                        <span aria-hidden="true" class="cerrar">Cerrar &times;</span>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-12"  style="padding: 1em 2em">
                        <h4>COMPARTIR</h4>
                        @php
                            $medias = Share::load($url, $title)->services('facebook','twitter','email');
                        @endphp
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 text-center">
                        <a class="btn btn-lg" href="{{ $medias['facebook'] }}" target="_blank">
                            <i class="fab fa-facebook-f fa-2x" style="color:black"></i>
                            <br>
                            <h5>Facebook</h5>
                        </a>
                    </div>
                    <div class="col-md-3 text-center">
                        <a class="btn btn-lg" href="{{ $medias['twitter'] }}" target="_blank">
                            <i class="fab fa-twitter fa-2x" style="color:black"></i>
                            <br>
                            <h5>Twitter</h5>
                        </a>
                    </div>
                    <div class="col-md-3 text-center">
                        <a class="btn btn-lg" href="{{ $medias['email'] }}" target="_blank">
                            <i class="far fa-envelope fa-2x" style="color:black"></i>
                            <br>
                            <h5>Correo</h5>
                        </a>
                    </div>
                    <div class="col-md-3 text-center">
                        <a class="btn btn-lg" data-toggle="tooltip" title="Link copiado" data-placement="top" onclick="mostrarTooltip()" href="#" id="copiar_url" data-clipboard-text="{{ $url }}">
                            <i class="fa fa-clipboard fa-2x" style="color:black"></i>
                            <br>
                            <h5>Copiar link</h5>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


