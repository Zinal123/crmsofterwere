<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * PHP throws this before Laravel's request pipeline (and therefore any
     * controller-level $request->validate()) ever runs, since the POST body
     * already exceeded post_max_size by the time PHP parsed it - so it can't
     * be caught by adding validation to an upload endpoint. Render it as a
     * clean message here instead of letting the default handler's debug page
     * (which leaks server file paths whenever APP_DEBUG is true) show through.
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof PostTooLargeException) {
            $message = 'The uploaded file is too large. Please choose a smaller file and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->withErrors(['file' => $message]);
        }

        return parent::render($request, $e);
    }
}
