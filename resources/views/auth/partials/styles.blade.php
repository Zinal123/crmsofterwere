<style>
    .oms-auth-shell {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        background: var(--oms-auth-body-bg);
    }

    /* ── Hero panel (branding) — desktop only ────────────────────────── */
    .oms-auth-hero {
        display: none;
        flex: 1;
        position: relative;
        overflow: hidden;
        align-items: center;
        justify-content: center;
        background: linear-gradient(150deg, var(--oms-auth-sidebar) 0%, #1a2d6b 60%, var(--oms-auth-sidebar) 100%);
    }

    .oms-auth-hero-content {
        position: relative;
        z-index: 1;
        text-align: center;
        color: #fff;
        padding: 2rem;
    }

    .oms-auth-hero-badge {
        width: 108px;
        height: 108px;
        margin: 0 auto 1.5rem;
        border-radius: 1.5rem;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 40px -18px rgba(0, 0, 0, .5);
        padding: .75rem;
    }

    .oms-auth-hero-badge img { max-width: 100%; max-height: 100%; object-fit: contain; }

    .oms-auth-hero-content h2 {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: -.01em;
        margin-bottom: .375rem;
    }

    .oms-auth-hero-content p {
        color: rgba(255, 255, 255, .5);
        font-size: .875rem;
        margin: 0;
    }

    @media (min-width: 992px) {
        .oms-auth-hero { display: flex; }
    }

    /* ── Form panel ───────────────────────────────────────────────────── */
    .oms-auth-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        width: 100%;
    }

    @media (min-width: 992px) {
        .oms-auth-panel { flex: 0 0 440px; }
    }

    .oms-auth-logo {
        display: block;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .oms-auth-logo img { height: 36px; }

    .oms-auth-tagline {
        text-align: center;
        color: var(--oms-auth-muted);
        font-size: .85rem;
        margin: -.75rem 0 1.75rem;
    }

    @media (min-width: 992px) {
        .oms-auth-logo,
        .oms-auth-tagline { display: none; }
    }

    .oms-auth-card {
        position: relative;
        width: 100%;
        max-width: 25rem;
        background: #fff;
        border: 1px solid rgba(67, 97, 238, .12);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(13, 27, 72, .04), 0 20px 40px -18px rgba(13, 27, 72, .18);
        padding: 2.25rem 2rem 2rem;
    }

    .oms-auth-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--vz-primary), var(--oms-auth-accent));
    }

    .oms-auth-badge {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        background: linear-gradient(135deg, rgba(67, 97, 238, .12), rgba(247, 148, 29, .14));
        color: var(--vz-primary);
        font-size: 1.2rem;
        margin-bottom: .9rem;
    }

    .oms-auth-card-header h5 {
        color: var(--oms-auth-sidebar);
        font-weight: 700;
    }

    .oms-input-group { position: relative; }

    .oms-input-group .form-control { padding-left: 2.7rem; }

    .oms-input-icon {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--oms-auth-muted);
        font-size: 1.05rem;
        pointer-events: none;
    }

    .oms-auth-card .form-control {
        border-radius: .6rem;
        padding-block: .62rem;
        border-color: var(--oms-auth-border-light);
        background: var(--oms-auth-bg-light);
    }

    .oms-auth-card .form-control:focus {
        border-color: var(--vz-primary);
        box-shadow: 0 0 0 .2rem rgba(67, 97, 238, .15);
    }

    .oms-auth-card .btn-success {
        border-radius: .6rem;
        padding-block: .68rem;
        font-weight: 600;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .oms-auth-card .btn-success:hover,
    .oms-auth-card .btn-success:focus {
        transform: translateY(-1px);
        box-shadow: 0 10px 20px -8px rgba(11, 163, 156, .45);
    }

    .oms-auth-below {
        text-align: center;
        font-size: .9rem;
        color: var(--oms-auth-muted);
        margin-top: 1.5rem;
    }

    .oms-auth-footer {
        text-align: center;
        font-size: .8rem;
        color: var(--oms-auth-muted-light);
        margin-top: 1.75rem;
    }
</style>
