<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\Home\DashboardService;
use App\Services\Home\UserProfileService;
use App\Traits\HandlesAvatarUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    use HandlesAvatarUpload;
    private const ERROR_MESSAGE = 'Something went wrong!';

    public function __construct(
        private DashboardService $dashboardService,
        private UserProfileService $profileService,
    ) {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

    /**
     * The dashboard at '/', rendered per role. Every staff role now has
     * dashboard.view and lands here; each sees a view scoped to what its job
     * actually is (owner = strategic, manager = operational, account =
     * finance, worker = my jobs) rather than one shared page.
     */
    public function root(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->hasRole('Owner') => view('index', $this->dashboardService->ownerViewData(...$this->dashboardRange($request))),
            $user->hasRole('Manager') => view('dashboard.manager', $this->dashboardService->managerViewData()),
            $user->hasRole('Account') => view('dashboard.account', $this->dashboardService->accountViewData()),
            $user->hasRole('Worker') => view('dashboard.worker', $this->dashboardService->workerViewData($user)),
            // Any other role that has been granted dashboard.view still gets a
            // safe, data-light landing rather than a 500.
            default => view('dashboard.worker', $this->dashboardService->workerViewData($user)),
        };
    }

    /**
     * Parse the Owner dashboard's date-range filter, defaulting to the current
     * month. Returns [from, to] Carbon instances; an invalid or inverted range
     * falls back to sensible values rather than erroring.
     */
    private function dashboardRange(Request $request): array
    {
        $parse = function ($value, \Illuminate\Support\Carbon $default) {
            try {
                return is_string($value) && $value !== '' ? \Illuminate\Support\Carbon::parse($value) : $default;
            } catch (\Exception) {
                return $default;
            }
        };

        $from = $parse($request->query('from'), \Illuminate\Support\Carbon::now()->startOfMonth())->startOfDay();
        $to = $parse($request->query('to'), \Illuminate\Support\Carbon::now()->endOfMonth())->endOfDay();

        return $from->lte($to) ? [$from, $to] : [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
    }

    /** The signed-in user's own profile / account settings page. */
    public function profile()
    {
        return view('profile', ['user' => Auth::user()]);
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $avatarPath = $request->file('avatar') ? $this->storeAvatar($request->file('avatar')) : null;
        $user = $this->profileService->updateProfile($id, $request->get('name'), $request->get('email'), $avatarPath);

        if ($user) {
            return redirect()->back()->with('success', 'Your profile has been updated.');
        }

        return redirect()->back()->with('error', self::ERROR_MESSAGE);
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!$this->profileService->isCurrentPasswordCorrect($request->get('current_password'))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200);
        } else {
            $user = $this->profileService->updatePassword($id, $request->get('password'));
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200);
            } else {
                Session::flash('message', self::ERROR_MESSAGE);
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => self::ERROR_MESSAGE
                ], 200);
            }
        }
    }
}
