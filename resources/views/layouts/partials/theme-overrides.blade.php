<style>
    /* Unified design system (see the approved design-system artifact):
       primary #2954A6 (engineering blue), brand accent #F0742A (reserved for
       identity contexts - sidebar active-item tint below, auth hero),
       sidebar #0D1B48, body bg #F0F4FF, 0.75rem card radius. Applied as
       CSS-variable overrides on top of the existing admin-template build -
       same mechanism as build/css/brand-theme.css, extended with the
       sidebar/background/radius tokens that file doesn't touch. */
    :root {
        /* Radius applies regardless of theme - not a light/dark concern. */
        --vz-border-radius: .5rem;
        --vz-border-radius-sm: .375rem;
        --vz-border-radius-lg: .75rem;
        --vz-border-radius-xl: 1rem;

        /* Auth-shell palette (login/forgot-password/confirm screens). Not
           theme-mode-dependent - these auth pages don't toggle dark mode -
           so this is the single unconditional source of truth for
           auth/partials/styles.blade.php, instead of that file hardcoding
           its own copy of these hex values. */
        --oms-auth-body-bg: #F0F4FF;
        --oms-auth-sidebar: #0D1B48;
        --oms-auth-accent: #F7941D;
        --oms-auth-muted: #6B7BB8;
        --oms-auth-muted-light: #93A0C9;
        --oms-auth-border-light: #E4EAFB;
        --oms-auth-bg-light: #EEF2FF;
    }

    /* Lining, fixed-width figures so money columns line up digit-for-digit.
       font-variant-numeric inherits, so setting it on a table (or a single
       cell) flows down to all its numbers. */
    .tabular-nums { font-variant-numeric: tabular-nums; }

    /* Shared dashboard (Materio-style) building blocks - used by every role
       dashboard (owner/manager/account/worker). */
    .dash-hero {
        background: linear-gradient(135deg, rgba(41,84,166,.10), rgba(41,84,166,.02));
        border: 1px solid rgba(41,84,166,.14);
    }
    [data-bs-theme=dark] .dash-hero {
        background: linear-gradient(135deg, rgba(108,147,232,.16), rgba(108,147,232,.03));
        border-color: rgba(108,147,232,.22);
    }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 1.35rem; flex-shrink: 0;
    }
    .dash-card { border: 0; box-shadow: 0 2px 14px rgba(13,27,72,.06); }
    [data-bs-theme=dark] .dash-card { box-shadow: 0 2px 14px rgba(0,0,0,.35); }
    .top-progress { height: 6px; border-radius: 6px; }
    .txn-item + .txn-item { border-top: 1px solid var(--vz-border-color); }
    .trend-tab.active { background: var(--vz-primary); color: #fff; }
    .trend-tab { cursor: pointer; }

    /* Scoped to light mode only - a bare :root rule here would win the
       cascade over the base template's own [data-bs-theme=dark] body-bg (same
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

    /* Sidebar collapse/expand toggle in the topbar. Two problems fixed
       here: (1) the theme's default ghost-secondary style is fully
       transparent until hover, so with no border/shadow it read as a bare
       floating icon rather than a button; (2) the default icon is a
       3-bar hamburger that *morphs into a bare arrow* when the sidebar is
       collapsed - an arrow with no label is ambiguous (expand? next?
       forward?) and was flagged as unclear. Both fixed the same way: the
       spans that drew the morphing hamburger/arrow are hidden, replaced by
       one static "ri-menu-line" glyph (via ::before, so the theme's
       existing click handlers and .open state JS - which only toggle a
       class, not this content - keep working untouched) that never
       changes shape. The sidebar itself visibly collapsing/expanding is
       what communicates state; the icon's only job is "click to toggle." */
    .topnav-hamburger {
        background-color: var(--vz-primary-bg-subtle) !important;
    }
    .topnav-hamburger:hover,
    .topnav-hamburger:focus {
        background-color: rgba(41, 84, 166, .22) !important;
    }
    [data-bs-theme=dark] .topnav-hamburger:hover,
    [data-bs-theme=dark] .topnav-hamburger:focus {
        background-color: rgba(108, 147, 232, .22) !important;
    }

    .hamburger-icon span {
        display: none;
    }
    .hamburger-icon {
        width: 20px;
        height: 20px;
    }
    .hamburger-icon::before {
        content: "\ef3e"; /* ri-menu-line */
        font-family: 'remixicon' !important;
        font-size: 20px;
        line-height: 1;
        color: #2954a6;
    }
    [data-bs-theme=dark] .hamburger-icon::before {
        color: #6c93e8;
    }

    /* Same "bare floating icon" problem as the hamburger above, on the other
       three circular topbar icon buttons (fullscreen, dark-mode, notifications) -
       consistent idle-visible treatment across all of them. */
    .btn-topbar.btn-ghost-secondary.rounded-circle {
        background-color: var(--vz-primary-bg-subtle) !important;
    }
    .btn-topbar.btn-ghost-secondary.rounded-circle:hover,
    .btn-topbar.btn-ghost-secondary.rounded-circle:focus {
        background-color: rgba(41, 84, 166, .22) !important;
    }
    [data-bs-theme=dark] .btn-topbar.btn-ghost-secondary.rounded-circle:hover,
    [data-bs-theme=dark] .btn-topbar.btn-ghost-secondary.rounded-circle:focus {
        background-color: rgba(108, 147, 232, .22) !important;
    }

    /* Sidebar active-page indicator. --vz-vertical-menu-item-active-bg is
       already defined (brand-orange tint, in the :root[data-sidebar=dark]
       block above) but the base admin template only wires it to the
       two-column icon-only layout mode this app doesn't use - connect it
       to the actual list-mode nav-link this app renders, so the current
       page is visibly marked. */
    .navbar-nav .nav-item .nav-link.active {
        background-color: var(--vz-vertical-menu-item-active-bg);
        border-radius: .375rem;
        font-weight: 600;
    }

    /* The base admin template's default page-content top padding leaves a large empty gap
       above the page-title bar on every page (measured ~55px on this
       layout). Pull the title bar up closer to the fixed topbar while
       keeping it clear of it (topbar is 71px tall). */
    .page-title-box {
        margin-top: -68px;
    }
</style>
