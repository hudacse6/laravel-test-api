<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var string[]
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
        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
    }

    public function handleException(Throwable $exception)
    {
        $request = request();
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            $errors = collect($errors)->mapWithKeys(function ($error, $key) {
                return [$key => $error[0]];
            });
            return response()->json($errors, 422);
        }
        if ($exception instanceof NotFoundHttpException) { // url wrong
            return response()->json([
                "success" => false,
                "message" => "The specified url can not be found"
            ], 404);
        }
        if ($exception instanceof AuthenticationException) // who does not register user in our system
        {
            return $this->unauthenticated($request, $exception);
        }
        if ($exception instanceof AuthorizationException) // who is register user but does not permission some system
        {
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 403);
        }
        if ($exception instanceof TokenInvalidException) {
            return response()->json([
                "success" => false,
                "message" => "Token is Invalid"
            ], 400);
        } elseif ($exception instanceof TokenExpiredException) {
            return response()->json([
                "success" => false,
                "message" => "Token is Expired"
            ], 400);
        } elseif ($exception instanceof JWTException) {
            return response()->json([
                "success" => false,
                "message" => "There is problem with your token"
            ], 400);
        }
    }
}
