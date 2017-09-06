<?php

namespace App\Http\Middleware;

use Closure;

class CheckInstance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    public function handle($request, Closure $next)
    {
        $instance=$request->header('theland-instance');

        if ($instance==false || $instance == ''){
            return response()->json(['error' => trans('error.INSTANCE_IS_REQUIRED'), 'code' => 400], 400);
        }


        $List_instances = ['theland','caetani','tondat'];

        if (!in_array($instance,$List_instances)) {
            return response()->json(['error' => trans('error.INSTANCE_IS_REQUIRED'), 'code' => 400], 400);
        }


         $request->merge(['instance' => $instance, 'device' => 'API']);

        return $next($request);
    }
}
