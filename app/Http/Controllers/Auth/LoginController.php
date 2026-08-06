<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        // Drives EnforceIdleTimeout: an unchecked "Remember this device" box
        // means this could be a shared shop-floor tablet, so the session
        // gets a short idle timeout. Checked means a trusted personal
        // device - no forced logout, same as Laravel's own remember-me cookie.
        $request->session()->put('device_trusted', $request->boolean('remember'));

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Your account has been deactivated. Contact an administrator.');
        }

        // The Worker role has no dashboard.view permission (by design - it's
        // scoped to just its own jobs), but $redirectTo always points at '/'.
        // Without this, a Worker's very first page after a real login (not a
        // deep link caught by intended()) is a 403, before they ever see the
        // app. Send anyone who can't view the dashboard to their jobs list
        // instead - the one page every authenticated staff role can reach.
        if (!$user->can('dashboard.view') && $user->can('jobs.view-own')) {
            return redirect()->intended(route('jobs.index'));
        }

        return redirect()->intended($this->redirectTo);
    }
}
