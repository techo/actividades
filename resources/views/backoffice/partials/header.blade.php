<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="{{ URL::to('/') }}" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><img src="{{ asset('/img/logo_small.png') }}" alt="{{__('backend.ong')}}" width="40"></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><img src="{{ asset('/img/logo_large.png') }}" alt="{{__('backend.ong')}}" width="140"></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- Notifications Menu -->
{{--
                <li class="dropdown notifications-menu">
                    <!-- Menu toggle button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning">10</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 10 notifications</li>
                        <li>
                            <!-- Inner Menu: contains the notifications -->
                            <ul class="menu">
                                <li><!-- start notification -->
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                                <!-- end notification -->
                            </ul>
                        </li>
                        <li class="footer"><a href="#">View all</a></li>
                    </ul>
                </li>
--}}
                <!-- User Account Menu -->
                <li class="dropdown user user-menu">
                    <!-- Menu Toggle Button -->
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <!-- The user image in the navbar-->
                        <!-- <img src="{{ asset('/bower_components/admin-lte/dist/img/user_avatar.png') }}" class="user-image" alt="User Image">
                         -->
                        @if (Auth::user()->photo)
                                <img class="user-image imagen-perfil-mini" src="{{ '/'.Auth::user()->photo }}" alt="{{__('backend.photo')}}">
                            @else
                                <img src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-mini" alt="User Image"> 
                            @endif
                        <!-- hidden-xs hides the username on small devices so only the image appears. -->
                        <span class="hidden-xs"> {{ Auth::user()->nombreCompleto }} </span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- The user image in the menu -->
                        <li class="user-header">
                            
                            @if (Auth::user()->photo)
                                <img class="img-circle" src="{{ '/'.Auth::user()->photo }}" alt="{{__('backend.photo')}}">
                            @else
                                <img src="{{ asset('/bower_components/admin-lte/dist/img/user_avatar.png') }}" class="img-circle" alt="User Image"> 
                            @endif
                            

                             <p>
                                {{ Auth::user()->nombreCompleto }}
                                {{--<small>Member since Nov. 2012</small>--}}
                            </p> 
                        </li>
{{--                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="row">
                                <div class="col-xs-4 text-center">
                                    <a href="#">Followers</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="#">Sales</a>
                                </div>
                                <div class="col-xs-4 text-center">
                                    <a href="#">Friends</a>
                                </div>
                            </div>
                            <!-- /.row -->
                        </li>--}}
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="/perfil" class="btn btn-default btn-flat">{{__('backend.profile')}}</a>
                            </div>
                            <div class="pull-right">
                                <form action="/logout" method="post">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-default btn-flat">{{__('backend.logout')}}</button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </li>
{{--                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>--}}
            </ul>
        </div>
    </nav>
</header>