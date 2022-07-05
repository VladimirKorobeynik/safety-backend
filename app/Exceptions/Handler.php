<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Laravel\Passport\Exceptions\MissingScopeException;
use Illuminate\Auth\AuthenticationException;
use App\Traits\ApiHelper;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiHelper;
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception) 
    {

    }
    
    public function render($request, Throwable $exception) 
    {
        if ($exception instanceof MissingScopeException) {
            return $this->onError(403, 'Forbidden');
        }

        if ($exception instanceof AuthenticationException) {
            return $this->onError(401, 'Unauthorized');
        }

        return parent::render($request, $exception);
    }   
}