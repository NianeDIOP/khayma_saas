<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OtpController extends Controller
{
    /**
     * Send OTP code to a phone number.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $phone = $validated['phone'];

        // Check that a user with this phone exists
        $user = User::where('phone', $phone)->first();
        if (! $user) {
            return back()->withErrors([
                'phone' => 'Aucun compte associé à ce numéro.',
            ]);
        }

        $otp = OtpCode::generate($phone);

        $sms = new SmsService();
        $sms->send($phone, "Votre code Khayma : {$otp->code}. Valide 5 min.");

        return back()->with('otp_sent', true);
    }

    /**
     * Verify OTP code and authenticate.
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'code'  => ['required', 'string', 'size:6'],
        ]);

        $otp = OtpCode::where('phone', $validated['phone'])
            ->where('code', $validated['code'])
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid()) {
            return back()->withErrors([
                'code' => 'Code invalide ou expiré.',
            ]);
        }

        $user = User::where('phone', $validated['phone'])->first();
        if (! $user) {
            return back()->withErrors([
                'phone' => 'Aucun compte associé à ce numéro.',
            ]);
        }

        $otp->markUsed();
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Redirect logic (same as LoginController)
        if ($user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        $company = $user->companies()->where('is_active', true)->first();
        if ($company) {
            return redirect()->route('app.dashboard', ['_tenant' => $company->slug]);
        }

        return redirect()->route('register');
    }
}
