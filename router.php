<?php
// Static assets (build/, storage/) live under public/ - check there first so
// requests like /storage/job-photos/... resolve locally the same way a real
// Apache/Nginx docroot pointed at public/ would serve them via the
// storage:link symlink, instead of falling through to Laravel's {any}
// catch-all and 404ing.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri) && !is_dir(__DIR__.'/public'.$uri)) {
    return false;
}
if ($uri !== '/' && file_exists(__DIR__.$uri) && !is_dir(__DIR__.$uri)) {
    return false;
}
require_once __DIR__.'/index.php';
