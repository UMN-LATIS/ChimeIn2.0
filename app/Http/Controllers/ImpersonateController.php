<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Contracts\ImpersonatesUsers;

class ImpersonateController extends Controller
{
    public function stop(Request $request, ImpersonatesUsers $impersonator)
    {
        if ($impersonator->impersonating($request)) {
            $impersonator->stopImpersonating($request, Auth::guard(), User::class);
        }

        return redirect()->route('home');
    }
}
