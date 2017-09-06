<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
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

        if($exception instanceof  MethodNotAllowedHttpException){
            //Not Login or Bad Auth
            return response()->json(['error' => trans('error.UNAUTHORIZED'), 'code' => 401], 401);
        }


        if($exception instanceof NotFoundResourceException){
             return response()->json(['error' => trans('error.NOT_FOUND_RESOURCE'), 'code' => 404], 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json(['error' => trans('error.NOT_RESULTS_DB'), 'code' => 404], 404);
        }

        if($exception instanceof NotFoundHttpException)
        {
            return response()->json(['error' => trans('error.NOT_FOUND_HTTP'), 'code' => 404], 404);
        }

        if($exception instanceof AuthorizationException)
        {
            //Auth ok but Permission denied
            return response()->json(['error' => trans('error.FORBIDDEN'), 'code' => 403], 403);
        }

        if($exception instanceof FileNotFoundException)
        {
            return response()->json(['error' => trans('error.FILE_IS_MISSING'), 'code' => 404], 404);
        }

        if($exception instanceof AuthenticationException){
            return response()->json(['error' => trans('error.UNAUTHORIZED'), 'code' => 401], 401);

        }

        return response()->json(['error' => $exception->getMessage() .' in '. $exception->getFile() .' Line '.$exception->getLine(), 'code' => 500], 500);

       //return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => trans('error.UNAUTHORIZED'), 'code' => 401], 401);
        }

        return redirect()->guest('login');
    }
}
