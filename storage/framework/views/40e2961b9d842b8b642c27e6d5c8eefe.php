<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="17">
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
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarDashboards" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span><?php echo app('translator')->get('translation.dashboards'); ?></span>
                    </a>
                    
                </li> <!-- end Dashboard Menu -->
                <li class="nav-item">
                    <a href="<?php echo e(route('product')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('Product'); ?></span></a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo e(route('invoice')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('Invoice'); ?></span></a>
                </li>
                   </li>
                            <li class="nav-item">
                    <a href="<?php echo e(route('invoice.histry')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('Payment history'); ?></span></a>
                </li>
                <!--<li class="nav-item">-->
                <!--    <a href="<?php echo e(route('invoice.vender')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('vander'); ?></span></a>-->
                <!--</li>-->
                <li class="nav-item">
                    <a href="<?php echo e(route('invoice.inventrylist')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('Invtery managemnet'); ?></span></a>
                </li>
                <li class="nav-item">
                    <a href="#sidebarEcommerce" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce"><?php echo app('translator')->get('Fiber Laser Cutting'); ?>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarEcommerce">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('standerconfig' ,1)); ?>" class="nav-link"><?php echo app('translator')->get('translation.Stander Config'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('TechnicalParameters' ,1)); ?>"class="nav-link"><?php echo app('translator')->get('translation.Technical Paramater'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('standerconfiglist',1)); ?>" class="nav-link"><?php echo app('translator')->get('translation.Standerd Config'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('generatequtation',1)); ?>" class="nav-link"><?php echo app('translator')->get('translation.Quation'); ?></a>
                         
                    
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="<?php echo e(route('co2quation',1)); ?>" class="nav-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarEcommerce"><?php echo app('translator')->get('Co2 Laser Cutting'); ?>
                    </a>
                    
                    
                </li>
                <li class="nav-item">
                    <a href="<?php echo e(route('listqutation')); ?>" class="nav-link"><i class="ri-dashboard-2-line"></i><span><?php echo app('translator')->get('Quation'); ?></span></a>
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
<?php /**PATH /home/u411614341/domains/cms.oraclemachinetech.com/public_html/resources/views/layouts/sidebar.blade.php ENDPATH**/ ?>