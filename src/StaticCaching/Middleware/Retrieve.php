<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;

class Retrieve
{
    /**
     * @var Cacher
     */
    private $cacher;

    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->canBeCached($request) && ($cached = $this->cacher->getCachedPage($request))) {
            return response($cached);
        }

        return $next($request);
    }

    protected function canBeCached($request)
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if (Statamic::isCpRoute()) {
            return false;
        }

        return true;
    }
}
