<?php

namespace App\Exceptions;

use Exception;
use Closure;
use Auth;
use App\User;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof PostTooLargeException) {
            return response()->json(["error" => "maxSizeExceeded"]);
        }
        return parent::render($request, $exception);
    }

    protected function unauthenticated(
            $request, AuthenticationException $exception) {
        $user = User::create(['email' => str_random(10), 'userType' => 'guest']);

        if (Auth::attempt(array('email' => $user->email, 'password' => $user->password))) {
            return redirect($request->path());
        } else {
            return redirect('/login');
        }
    }
}
