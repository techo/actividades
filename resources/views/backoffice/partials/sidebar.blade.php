<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image p-2">
                @if (Auth::user()->photo)
                    <img class="imagen-perfil-mini" src="{{ '/'.Auth::user()->photo }}" alt="Foto">
                @else
                    <img src="{{ asset('/bower_components/admin-lte/dist/img/user_avatar.png') }}" class="img-circle" alt="User Image"> 
                @endif
            </div>
            <div class="pull-left info">
                <p>{{ Auth::user()->nombreCompleto }}</p>
                <!-- Rol -->
                <p class="small"><i class="fa fa-circle text-success"></i> {{ Auth::user()->getRoleNames()->first() }}</p>
            </div>
        </div>

{{--        <!-- search form (Optional) -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
            </div>
        </form>
        <!-- /.search form -->--}}

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">{{ __('backend.menu') }}</li>
            <!-- Optionally, you can add icons to the links -->
            {{--<li class="active"><a href="#"><i class="fa fa-link"></i> <span>Link</span></a></li>--}}
            {{--<li><a href="#"><i class="fa fa-link"></i> <span>Another Link</span></a></li>--}}
            <li class="treeview {{ request()->is('admin/actividades*') ? 'active menu-open' : ''}}">
                <a href="#"><i class="fa fa-calendar"></i> <span>{{ __('backend.activities') }}</span>
                    <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                </a>
                <ul class="treeview-menu">
                    @if (Auth::user()->hasRole('admin'))
                        <li class="{{request()->is('admin/actividades') ? 'active' : ''}}"><a href="/admin/actividades">{{ __('backend.view_all') }}</a></li>
                    @endif
                    @if(Auth::user()->hasPermissionTo('ver_mis_actividades'))
                        <li class="{{request()->is('admin/actividades/usuario') ? 'active' : ''}}"><a href="/admin/actividades/usuario">{{ __('backend.my_activities') }}</a></li>
                    @endif
                    <li class="{{request()->is('admin/actividades/crear') ? 'active' : ''}}">
                        <a href="/admin/actividades/crear"><i class="fa fa-plus"></i>{{ __('backend.create_activity') }}</a>
                    </li>
                </ul>
            </li>
            @if (Auth::user()->hasRole('admin'))
                <li class="treeview {{ (request()->is('admin/usuarios*') || request()->is('admin/suscriptos') || request()->is('admin/equipos')) ? 'active menu-open' : ''}}">
                    <a href="#"><i class="fa fa-user"></i> <span>{{ __('backend.people') }}</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                  </span>
                    </a>
                    <ul class="treeview-menu">                        
                        <li class="{{request()->is('admin/usuarios/registrar') ? 'active' : ''}}">
                            <a href="/admin/usuarios/registrar"><i class="fa fa-plus"></i>{{ __('backend.create_person') }}</a>
                        </li>
                        <li class="{{request()->is('admin/usuarios') ? 'active' : ''}}"><a href="/admin/usuarios">{{ __('backend.view_all') }}</a></li>
                        <li class="{{request()->is('admin/suscriptos') ? 'active' : ''}}"><a href="/admin/suscriptos">{{ __('backend.view_subscribed') }}</a></li>
                        <li class="{{request()->is('admin/equipos') ? 'active' : ''}}"><a href="/admin/equipos">{{ __('backend.view_teams') }}</a></li>

                    </ul>
                </li>
                    <ul class="treeview-menu">
                        <li class="{{request()->is('admin/usuarios') ? 'active' : ''}}"><a href="/admin/usuarios">{{ __('backend.view_list') }}</a></li>
                        <li class="{{request()->is('admin/usuarios/registrar') ? 'active' : ''}}"><a href="/admin/usuarios/registrar">{{ __('backend.register_user') }}</a></li>
                    </ul>
                </li>
            @elseif (Auth::user()->hasRole('coordinador'))
                <li class="treeview {{ (request()->is('admin/equipos')) ? 'active menu-open' : ''}}">
                    <a href="#"><i class="fa fa-user"></i> <span>{{ __('backend.people') }}</span>
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                  </span>
                    </a>
                    <ul class="treeview-menu">                        
                        <li class="{{request()->is('admin/equipos') ? 'active' : ''}}"><a href="/admin/equipos">{{ __('backend.view_teams') }}</a></li>
                    </ul>
                </li>
            @endif



            @if (Auth::user()->hasRole('admin'))

            <li class="treeview {{ request()->is('admin/estadisticas*') ? 'active menu-open' : ''}}">
                <a href="#"><i class="fa fa-bar-chart"></i> <span>{{ __('backend.statistics') }}</span>
                    <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{request()->is('admin/estadisticas') ? 'active' : ''}}">
                        <a href="/admin/estadisticas">{{ __('backend.generals') }}</a>
                    </li>
                    <li class="{{request()->is('admin/estadisticas/actividades') ? 'active' : ''}}">
                        <a href="/admin/estadisticas/actividades">{{ __('backend.activities') }}</a>
                    </li>
                    <li class="{{request()->is('admin/estadisticas/personas') ? 'active' : ''}}">
                        <a href="/admin/estadisticas/personas">{{ __('backend.people') }}</a>
                    </li>
                    <li class="{{request()->is('admin/estadisticas/coordinadores') ? 'active' : ''}}">
                        <a href="/admin/estadisticas/coordinadores">{{ __('backend.coordinators') }}</a>
                    </li>
                    <li>
                        <a href="https://lookerstudio.google.com/s/lGVoxRKcZZg" target="_blank">
                            <span>{{ __('backend.looker_studio') }}</span>
                            <i class="fa fa-external-link"></i> 
                        </a>
                    </li>
                </ul>
            </li>

            <li class="treeview {{ request()->is('admin/configuracion*') ? 'active menu-open' : ''}}">
                <a href="#"><i class="fa fa-cogs"></i> <span>{{ __('backend.settings') }}</span>
                    <span class="pull-right-container">
                <i class="fa fa-angle-left pull-right"></i>
              </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{request()->is('admin/configuracion/oficinas') ? 'active' : ''}}">
                        <a href="/admin/configuracion/oficinas">{{ __('backend.offices') }}</a>
                    </li>
                    <li class="{{request()->is('admin/configuracion/tipos-actividad') ? 'active' : ''}}">
                        <a href="/admin/configuracion/tipos-actividad">{{ __('backend.activity_types') }}</a>
                    </li>
                    <li class="{{request()->is('admin/configuracion/provincias') ? 'active' : ''}}">
                        <a href="/admin/configuracion/provincias">{{ __('backend.geographical_divisions') }}</a>
                    </li>
                    <li class="{{request()->is('admin/configuracion/home-header') ? 'active' : ''}}">
                        <a href="/admin/configuracion/home-header">{{ __('backend.header') }}</a>
                    </li>
                </ul>
            </li>
            @endif

            @if(config('app.docs'))

            <li class="treeview {{ request()->is('admin/ayuda*') ? 'active menu-open' : ''}}">
                <a href="#"><i class="fa fa-question-circle"></i> <span>{{ __('backend.help') }}</span>
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li class="{{request()->is('admin/ayuda') ? 'active' : ''}}">
                        <a href="https://github.com/techo/voluntariado-eventual/wiki" target="_blank">{{ __('backend.wiki') }} <i class="fa fa-external-link"></i> </a>
                    </li>
                    <li class="{{request()->is('admin/ayuda') ? 'active' : ''}}">
                        <a href="https://github.com/techo/voluntariado-eventual/issues/new/choose" target="_blank">{{ __('backend.support') }} <i class="fa fa-external-link"></i> </a>
                    </li>
                </ul>
            </li>

            @endif

        </ul>
        <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
