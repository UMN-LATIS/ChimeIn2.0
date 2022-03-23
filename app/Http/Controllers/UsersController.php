<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;


class UsersController extends Controller
{
  public function getCurrentUser() {
    return Auth::user();
  }
}
