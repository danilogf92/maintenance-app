<?php

namespace App\Modules\Workflow\UI;

class SetLocale
{
    public function handle($request, \Closure $next)
    {
        app()->setLocale('es');

        return $next($request);
    }
}
