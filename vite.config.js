import { defineConfig } from 'vite';

// Deliberately scoped: compiles ONLY resources/scss/custom.scss to the
// fixed path build/css/custom.min.css, which head-css.blade.php already
// links last. Does NOT touch app.min.css/bootstrap.min.css/icons.min.css -
// those remain the static, one-time-imported vendor build (see
// build/manifest.json's single "Initial project import" commit) with no
// resources/js source to rebuild them from and no headless browser
// available in this project to verify a full recompile didn't regress
// something visually. See resources/scss/custom.scss's BUILD NOTE.
//
// emptyOutDir MUST stay false: public/build is a symlink to ../build,
// which holds every other static asset (fonts, images, third-party libs,
// hand-maintained JS) this config knows nothing about - the default
// "clean the output dir first" behavior would delete all of it.
export default defineConfig({
    // Vite defaults publicDir to "public" and copies its entire contents into
    // outDir on every build. Since outDir here (public/build) is a symlinked
    // subdirectory of that same "public" folder, that copy step dumped the
    // whole Laravel public/ tree - index.php, sw.js, storage/, uploaded
    // images, and a manifest.json that clobbered the vendor theme's own -
    // into build/ the first time this ran. Disabling it entirely: this
    // config compiles one SCSS file, nothing else, and must never touch
    // anything else in public/.
    publicDir: false,
    build: {
        outDir: 'public/build',
        emptyOutDir: false,
        cssCodeSplit: false,
        rollupOptions: {
            input: 'resources/scss/custom.scss',
            output: {
                assetFileNames: 'css/custom.min.css',
            },
        },
    },
});
