@php
    $sideBarLinks = json_decode($sidenav);
    $user = auth()->guard('admin')->user(); // current logged-in admin guard
@endphp

<div class="sidebar bg--dark">
    <button class="res-sidebar-close-btn"><i class="las la-times"></i></button>
    <div class="sidebar__inner">
        <div class="sidebar__logo">
            <a href="{{ route('admin.dashboard') }}" class="sidebar__main-logo">
                <img src="{{ siteLogo() }}" alt="image">
            </a>
        </div>
        <div class="sidebar__menu-wrapper">
            <ul class="sidebar__menu">
                @foreach ($sideBarLinks ?? [] as $key => $data)
                    @php
                        $showMainMenu = false;

                        // Check submenu permissions
                        if (!empty($data->submenu)) {
                            foreach ($data->submenu as $submenu) {
                                if (!isset($submenu->permission) || ($user && $user->can($submenu->permission, 'admin'))) {
                                    $showMainMenu = true;
                                    break;
                                }
                            }
                        } else {
                            // Main menu without submenu
                            $showMainMenu = !isset($data->permission) || ($user && $user->can($data->permission, 'admin'));
                        }
                    @endphp

                    @if($showMainMenu)
                        @if (!empty($data->header))
                            <li class="sidebar__menu-header">{{ __($data->header) }}</li>
                        @endif

                        @if (!empty($data->submenu))
                            <li class="sidebar-menu-item sidebar-dropdown">
                                <a href="javascript:void(0)" class="{{ menuActive(@$data->menu_active, 3) }}">
                                    <i class="menu-icon {{ @$data->icon }}"></i>
                                    <span class="menu-title">{{ __(@$data->title) }}</span>
                                </a>
                                <div class="sidebar-submenu {{ menuActive(@$data->menu_active, 2) }}">
                                    <ul>
                                        @foreach ($data->submenu ?? [] as $menu)
                                            @php
                                                $submenuPermission = !isset($menu->permission) || ($user && $user->can($menu->permission, 'admin'));
                                                $submenuParams = [];
                                                if (!empty($menu->params)) {
                                                    foreach ($menu->params as $paramVal) {
                                                        $submenuParams[] = array_values((array) $paramVal)[0];
                                                    }
                                                }
                                            @endphp

                                            @if ($submenuPermission)
                                                <li class="sidebar-menu-item {{ menuActive(@$menu->menu_active) }}">
                                                    @if(!empty($menu->route_name) && Route::has($menu->route_name))
                                                        <a href="{{ route($menu->route_name, $submenuParams) }}" class="nav-link">
                                                    @else
                                                        <a href="javascript:void(0)" class="nav-link">
                                                    @endif
                                                        <i class="menu-icon las la-dot-circle"></i>
                                                        <span class="menu-title">{{ __($menu->title) }}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @else
                            {{-- Main menu without submenu --}}
                            @php
                                $mainParams = [];
                                if (!empty($data->params)) {
                                    foreach ($data->params as $paramVal) {
                                        $mainParams[] = array_values((array) $paramVal)[0];
                                    }
                                }
                            @endphp
                            <li class="sidebar-menu-item {{ menuActive(@$data->menu_active) }}">
                                @if(!empty($data->route_name) && Route::has($data->route_name))
                                    <a href="{{ route($data->route_name, $mainParams) }}" class="nav-link">
                                @else
                                    <a href="javascript:void(0)" class="nav-link">
                                @endif
                                    <i class="menu-icon {{ $data->icon }}"></i>
                                    <span class="menu-title">{{ __(@$data->title) }}</span>
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <div class="version-info text-center text-uppercase">
        <span class="text--primary">Rafusoft Dashboard</span>
        <span class="text--success">@lang('V')1.0 </span>
    </div>
</div>

@push('script')
<script>
    if ($('li').hasClass('active')) {
        $('.sidebar__menu-wrapper').animate({
            scrollTop: eval($(".active").offset().top - 320)
        }, 500);
    }
</script>
@endpush
