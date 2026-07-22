<style>
    .oms-auth {
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background:
            radial-gradient(1100px circle at 85% -10%, rgba(194, 65, 12, .10), transparent 60%),
            radial-gradient(900px circle at -10% 110%, rgba(194, 65, 12, .08), transparent 60%),
            radial-gradient(rgba(194, 65, 12, .09) 1px, transparent 1px) 0 0 / 24px 24px,
            #faf6f0;
    }

    .oms-auth-logo {
        display: block;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .oms-auth-logo img { height: 36px; }

    .oms-auth-tagline {
        text-align: center;
        color: #9a8a7c;
        font-size: .85rem;
        margin: -.75rem 0 1.75rem;
    }

    .oms-auth-card {
        position: relative;
        width: 100%;
        max-width: 25rem;
        background: #fff;
        border: 1px solid #f0e5da;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(60, 40, 20, .04), 0 20px 40px -18px rgba(120, 60, 20, .22);
        padding: 2.25rem 2rem 2rem;
    }

    .oms-auth-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #c2410c, #f59e0b);
    }

    .oms-auth-badge {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        background: linear-gradient(135deg, rgba(194, 65, 12, .12), rgba(245, 158, 11, .14));
        color: #c2410c;
        font-size: 1.2rem;
        margin-bottom: .9rem;
    }

    .oms-auth-card-header h5 {
        color: #7c2d12;
        font-weight: 700;
    }

    .oms-input-group { position: relative; }

    .oms-input-group .form-control { padding-left: 2.7rem; }

    .oms-input-icon {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #c2833f;
        font-size: 1.05rem;
        pointer-events: none;
    }

    .oms-auth-card .form-control {
        border-radius: .6rem;
        padding-block: .62rem;
        border-color: #e7d9cd;
    }

    .oms-auth-card .form-control:focus {
        border-color: #c2410c;
        box-shadow: 0 0 0 .2rem rgba(194, 65, 12, .15);
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
        color: #6b5e52;
        margin-top: 1.5rem;
    }

    .oms-auth-footer {
        text-align: center;
        font-size: .8rem;
        color: #9a8a7c;
        margin-top: 1.75rem;
    }
</style>
