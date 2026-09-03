<?php

namespace App\Http\Controllers\Office;

use App\Constants\OfficeScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sign-in for the PowerPoint add-in, driven by the Office dialog API.
 *
 * The add-in opens `/office/auth/start` in an Office dialog. That runs through the
 * normal browser login, then `finish` hands a browse token back to the add-in with
 * `Office.context.ui.messageParent` and the dialog closes.
 */
class OfficeAuthController extends Controller
{
    public function start(Request $request)
    {
        // AuthIfNecessary hands us a throwaway guest rather than a 401, so check explicitly.
        if (! Auth::check() || Auth::user()->guest_user) {
            return redirect()->guest(route('login'));
        }

        return redirect()->route('office.auth.finish');
    }

    public function finish(Request $request)
    {
        if (! Auth::check() || Auth::user()->guest_user) {
            return view('office.auth-callback', [
                'payload' => ['status' => 'error', 'message' => 'Sign in did not complete.'],
            ]);
        }

        $user = Auth::user();

        $presentsAnything = $user->chimes()
            ->wherePivot('permission_number', '>=', CHIMEIN_PRESENTER)
            ->exists();

        if (! $presentsAnything && ! $user->global_admin) {
            return view('office.auth-callback', [
                'payload' => [
                    'status' => 'error',
                    'message' => 'You are not a presenter for any chime, so there is nothing to insert.',
                ],
            ]);
        }

        $user->tokens()->where('name', OfficeScope::BROWSE_TOKEN_NAME)->delete();

        $token = $user->createToken(
            OfficeScope::BROWSE_TOKEN_NAME,
            [OfficeScope::BROWSE],
            now()->addDays(config('office.browse_token_days'))
        );

        return view('office.auth-callback', [
            'payload' => [
                'status' => 'ok',
                'token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
}
