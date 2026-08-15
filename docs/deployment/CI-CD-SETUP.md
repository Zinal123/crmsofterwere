# CI/CD Setup — one-time steps

The pipeline itself is committed at `.github/workflows/deploy.yml` — nothing
more to write. What's below is the one-time setup only you can do (it needs
your Hostinger access and your GitHub repo settings), before the pipeline can
actually reach the server. Do these once; every push to `meet-update` after
that deploys itself.

## What the pipeline does

Two jobs, always in this order:

1. **test** — on every push and PR to `meet-update`, and on a manual
   trigger. Installs dependencies, copies the repo's own `.env.testing`
   (already committed, sqlite in-memory), runs `php artisan test`.
2. **deploy** — only on a direct push to `meet-update` (never a PR), only if
   `test` passed. SSHes into the server and runs, in order: maintenance
   mode on, `git fetch` + `git reset --hard` to the pushed commit,
   `composer install --no-dev`, `migrate --force`, re-run
   `RolesAndPermissionsSeeder` (idempotent — safe every time), `storage:link`,
   cache config/routes/views, maintenance mode off.

It deliberately never touches `public/build/` (that's a symlink outside git
on your machine, and almost certainly the same on the server — the pipeline
leaves it alone) and never touches the server's `.env` (so production
secrets aren't managed through GitHub at all — see Step 3).

## Step 1 — Generate a deploy-only SSH key

Don't reuse your personal key. On your own machine:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/deploy_key -N ""
```

This makes two files: `~/deploy_key` (private — goes into a GitHub secret,
never committed anywhere) and `~/deploy_key.pub` (public — goes on the
server).

## Step 2 — Authorize that key on Hostinger

hPanel → Advanced → SSH Access → manage authorized keys, and paste the
contents of `~/deploy_key.pub`. (If hPanel doesn't expose that UI, SSH in
with your normal credentials and append it by hand:
`echo "<contents of deploy_key.pub>" >> ~/.ssh/authorized_keys`.)

## Step 3 — Confirm the server directory is a real git clone

The deploy script runs `git fetch` + `git reset --hard` in place — that only
works if the live directory is already a git working copy on the
`meet-update` branch pointed at this GitHub repo. SSH in and check:

```bash
cd /path/to/your/laravel/app
git remote -v      # should show https://github.com/Zinal123/crmsofterwere.git
git branch         # should show meet-update checked out
```

If it's NOT a git clone (e.g. the app was uploaded as plain files), this
needs a one-time conversion before the pipeline can run: back up `.env` and
`storage/` (uploads), `git clone` the repo fresh into a new directory,
restore `.env` and `storage/` into it, confirm `public/build/` is present
(it won't come from git — copy it over from the old directory if needed),
then point your web server's document root at the new directory.

Also confirm production `.env` is already correct on the server —
`APP_ENV=production`, `APP_DEBUG=false`, real DB credentials, a real
`APP_KEY`. The pipeline never writes `.env`, on purpose, so this has to
already be right before the first deploy runs.

## Step 4 — Add the GitHub repo secrets

Repo → Settings → Secrets and variables → Actions → New repository secret.
Add all five:

| Secret | Value |
|---|---|
| `DEPLOY_SSH_HOST` | `62.72.28.228` |
| `DEPLOY_SSH_PORT` | `65002` |
| `DEPLOY_SSH_USERNAME` | `u411614341` |
| `DEPLOY_SSH_PRIVATE_KEY` | the full contents of `~/deploy_key` (the private key, `-----BEGIN...-----` through `-----END...-----`) |
| `DEPLOY_PATH` | the absolute path from Step 3's `cd` (e.g. `/home/u411614341/domains/oraclemachinetech.com/public_html`) |

(Host/port/username above are from prior session notes — double-check them
against your current hPanel → Advanced → SSH Access page before adding, in
case anything changed since.)

## Step 5 — Optional: require a manual approval before deploy

The pipeline is set to deploy automatically on every push, per your choice.
If you ever want a manual "are you sure" click before it touches
production without editing the workflow file: Repo → Settings →
Environments → `production` → Deployment protection rules → add yourself as
a required reviewer. The `deploy` job already targets an environment named
`production`, so this toggle is available whenever you want it — no code
change needed either way.

## Step 6 — Test it

Once Steps 1–4 are done: Repo → Actions → "CI/CD — test and deploy" → Run
workflow → pick `meet-update` → Run. Watch both jobs. If `deploy` fails on
the SSH step, it's almost always Step 2 (key not authorized) or Step 4
(wrong path/host) — the error message names which. After a clean manual run,
every future push to `meet-update` deploys itself the same way.
