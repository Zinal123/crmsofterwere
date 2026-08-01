<style>
    /* Admin Panel UI Design reference (Admin Panel UI Design/src/styles/theme.css):
       primary #4361EE, accent #F7941D, sidebar #0D1B48, body bg #F0F4FF,
       0.75rem card radius. Applied as CSS-variable overrides on top of the
       existing Velzon build - same mechanism as the previous single-color
       brand theme, extended with the sidebar/background/radius tokens the
       old override didn't touch. */
    :root {
        /* Radius applies regardless of theme - not a light/dark concern. */
        --vz-border-radius: .5rem;
        --vz-border-radius-sm: .375rem;
        --vz-border-radius-lg: .75rem;
        --vz-border-radius-xl: 1rem;
    }

    /* Scoped to light mode only - a bare :root rule here would win the
       cascade over Velzon's own [data-bs-theme=dark] body-bg (same
       specificity, later source order) and leave dark mode showing this
       light background instead of a dark one. */
    [data-bs-theme=light] {
        --vz-body-bg: #F0F4FF;
    }

    [data-bs-theme=dark] {
        --vz-body-bg: #070D24;
    }

    :root[data-sidebar=dark] {
        --vz-vertical-menu-bg: #0D1B48;
        --vz-vertical-menu-border: #0D1B48;
        --vz-vertical-menu-item-color: #93A0C9;
        --vz-vertical-menu-item-bg: rgba(247, 148, 29, .16);
        --vz-vertical-menu-item-hover-color: #fff;
        --vz-vertical-menu-item-active-color: #fff;
        --vz-vertical-menu-item-active-bg: rgba(247, 148, 29, .22);
        --vz-vertical-menu-sub-item-color: #93A0C9;
        --vz-vertical-menu-sub-item-hover-color: #fff;
        --vz-vertical-menu-sub-item-active-color: #fff;
        --vz-vertical-menu-title-color: #5A6A9E;
        --vz-twocolumn-menu-iconview-bg: #13234F;
    }

    /* Sidebar collapse/expand toggle in the topbar - matches the other
       ghost-secondary circular icon buttons in the topbar (transparent
       idle state, tinted on hover), just recolored to the brand accent. */
    .topnav-hamburger:hover,
    .topnav-hamburger:focus {
        background-color: rgba(67, 97, 238, .12) !important;
    }

    .hamburger-icon span {
        background-color: #4361ee;
    }

    /* Velzon's default page-content top padding leaves a large empty gap
       above the page-title bar on every page (measured ~55px on this
       layout). Pull the title bar up closer to the fixed topbar while
       keeping it clear of it (topbar is 71px tall). */
    .page-title-box {
        margin-top: -68px;
    }
</style>
