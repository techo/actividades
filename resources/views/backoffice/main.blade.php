<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('backoffice.partials.meta')
    @include('backoffice.partials.css')
    @include('backoffice.partials.head-scripts')
</head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper" id="app">

    @include('backoffice.partials.header')
    @include('backoffice.partials.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content news bar -->
        @include('backoffice.partials.novedades')
        
        <!-- Content Header (Page header) -->
        @include('backoffice.partials.content-header')

        <!-- Main content -->
        <section class="content container-fluid">

            @yield('content')

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    @yield('footer')


    <!-- Control Sidebar -->
{{--    @include('backoffice.partials.control-sidebar')--}}
</div>
<!-- ./wrapper -->

    @include('backoffice.partials.body-scripts')
</body>
</html>