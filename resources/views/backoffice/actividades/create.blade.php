@extends('backoffice.main')

@section('page_title', 'Nueva Actividad')

@section('subtitulo')

@endsection

@section('content')
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#general" data-toggle="tab" aria-expanded="true">General</a>
            </li>
            <li class="disabled">
                <a href="#" data-toggle="" aria-expanded="true">Grupos</a>
            </li>
            <li class="disabled">
                <a href="#" data-toggle="" aria-expanded="true">Inscripciones</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="general">
                <actividades-show
                        actividad="{{ $actividad }}"
                        paises="{{ $paises }}"
                        localidades=""
                        provincias="{{ $provincias ? $provincias : '' }}"
                        tipos=""
                        categorias="{{ $categorias }}"
                        edicion="{{ $edicion }}"
                ></actividades-show>
            </div>
            <div class="tab-pane" id="grupos">
            </div>
            <div class="tab-pane" id="inscripciones">
            </div>
        </div>
    </div>
    <p style="margin-bottom: 4em"></p>
@endsection

@push('additional_scripts')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script>
        var editor_config = {
            path_absolute : "/",
            selector: "textarea#descripcion",
            menubar: false,
            statusbar: true,
            resize: true,
            toolbar: "undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
            plugins: [
                "advlist autolink lists link image charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars code fullscreen",
                "insertdatetime nonbreaking save table contextmenu directionality",
                "emoticons template paste textcolor colorpicker textpattern"
            ],
            relative_urls: false,
            file_browser_callback : function(field_name, url, type, win) {
                var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                var cmsURL = editor_config.path_absolute + 'laravel-filemanager?field_name=' + field_name;
                if (type == 'image') {
                    cmsURL = cmsURL + "&type=Images";
                } else {
                    cmsURL = cmsURL + "&type=Files";
                }

                tinyMCE.activeEditor.windowManager.open({
                    file : cmsURL,
                    title : 'Administrador de archivos',
                    width : x * 0.8,
                    height : y * 0.8,
                    resizable : "yes",
                    close_previous : "no"
                });
            }
        };

        tinymce.init(editor_config);
    </script>
@endpush

@push('additional_css')
    <link rel="stylesheet" href="{{ asset('/css/custom.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@section('footer')
    <crud-footer
            cancelar-url="/admin/actividades"
            edicion="{{ $edicion }}"
    >
    </crud-footer>
@endsection