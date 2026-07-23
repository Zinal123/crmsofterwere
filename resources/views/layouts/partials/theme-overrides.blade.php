<style>
    :root[data-sidebar=dark] {
        --vz-vertical-menu-bg: #241a15;
        --vz-vertical-menu-border: #241a15;
        --vz-vertical-menu-item-color: #c9b8a8;
        --vz-vertical-menu-item-bg: rgba(245, 158, 11, .16);
        --vz-vertical-menu-item-hover-color: #fff;
        --vz-vertical-menu-item-active-color: #fff;
        --vz-vertical-menu-item-active-bg: rgba(245, 158, 11, .2);
        --vz-vertical-menu-sub-item-color: #c9b8a8;
        --vz-vertical-menu-sub-item-hover-color: #fff;
        --vz-vertical-menu-sub-item-active-color: #fff;
        --vz-vertical-menu-title-color: #8a7264;
        --vz-twocolumn-menu-iconview-bg: #332822;
    }

    /* Sidebar collapse/expand toggle in the topbar - give it the brand
       accent and a proper button affordance instead of bare floating lines. */
    .topnav-hamburger {
        border-radius: .5rem;
        background-color: rgba(194, 65, 12, .08);
        transition: background-color .15s ease;
    }

    .topnav-hamburger:hover {
        background-color: rgba(194, 65, 12, .16);
    }

    .hamburger-icon span {
        background-color: #c2410c;
    }

    /* Velzon's default page-content top padding leaves a large empty gap
       above the page-title bar on every page (measured ~55px on this
       layout). Pull the title bar up closer to the fixed topbar while
       keeping it clear of it (topbar is 71px tall). */
    .page-title-box {
        margin-top: -68px;
    }
</style>
