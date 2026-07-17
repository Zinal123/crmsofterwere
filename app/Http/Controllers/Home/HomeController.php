<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Services\Home\DashboardService;
use App\Services\Home\UserProfileService;
use App\Traits\HandlesAvatarUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

    public function root()
    {
        return view('index', $this->dashboardService->getDashboardViewData());
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
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            return redirect()->back();
        } else {
            Session::flash('message', self::ERROR_MESSAGE);
            Session::flash('alert-class', 'alert-danger');
            return redirect()->back();
        }
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
