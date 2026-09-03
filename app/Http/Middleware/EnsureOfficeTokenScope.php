<?php

namespace App\Http\Middleware;

use App\Chime;
use App\Constants\OfficeScope;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfficeTokenScope
{
    /**
     * Assert the bearer token is scoped to the chime in the route, and that the
     * token's owner is still a presenter for it.
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return response()->json(['message' => 'A ChimeIn add-in token is required.'], 401);
        }

        $chime = $request->route('chime');

        if (! $chime instanceof Chime) {
            return response()->json(['message' => 'Route is missing a chime.'], 500);
        }

        if (! $token->can(OfficeScope::forChime($chime->id, $scope))) {
            return response()->json(['message' => 'This token is not scoped to that chime.'], 403);
        }

        // The token outlives the deck it is embedded in, so re-check access every request.
        $membership = $user->chimes()->where('chime_id', $chime->id)->first();

        if ($membership === null || $membership->pivot->permission_number < CHIMEIN_PRESENTER) {
            return response()->json(['message' => 'You are no longer a presenter for this chime.'], 403);
        }

        $request->attributes->set('office_chime', $membership);

        return $next($request);
    }
}
