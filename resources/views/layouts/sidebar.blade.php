<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span>@lang('translation.dashboards')</span>
                    </a>
                    
                </li> <!-- end Dashboard Menu -->
                <li class="nav-item">
                    <a href="{{route('product')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Product')</span></a>
                </li>
                <li class="nav-item">
                    <a href="{{route('invoice')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Invoice')</span></a>
                </li>
                   </li>
                            <li class="nav-item">
                    <a href="{{route('invoice.histry')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Payment history')</span></a>
                </li>
                <li class="nav-item">
                    <a href="{{route('invoice.inventrylist')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Invtery managemnet')</span></a>
                </li>
                <li class="nav-item">
                    <a href="#sidebarEcommerce" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce">@lang('Fiber Laser Cutting')
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarEcommerce">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{route('standerconfig' ,1)}}" class="nav-link">@lang('translation.Stander Config')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('TechnicalParameters' ,1)}}"class="nav-link">@lang('translation.Technical Paramater')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('standerconfiglist',1)}}" class="nav-link">@lang('translation.Standerd Config')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('generatequtation',1)}}" class="nav-link">@lang('translation.Quation')</a>
                         
                    
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{route('co2quation',1)}}" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce">@lang('Co2 Laser Cutting')
                    </a>
                    
                    
                </li>
                <li class="nav-item">
                    <a href="{{route('listqutation')}}" class="nav-link"><i class="ri-dashboard-2-line"></i><span>@lang('Quation')</span></a>
                </li>
               
                

                


             

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<script>
    // These menu-expand links carry role="button" for assistive tech, which per
    // WAI-ARIA authoring practices means they should also respond to the Space
    // key. Native <a> elements already respond to Enter on their own (browser
    // default), so only Space needs a synthetic click here - handling Enter
    // too would double-fire the click. Purely additive - does not change
    // existing click/collapse behavior.
    document.addEventListener('keydown', function (event) {
        if (event.key !== ' ') {
            return;
        }
        var target = event.target.closest('.nav-link[role="button"]');
        if (!target) {
            return;
        }
        event.preventDefault();
        target.click();
    });
</script>
