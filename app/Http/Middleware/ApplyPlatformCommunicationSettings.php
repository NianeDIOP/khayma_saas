<?php

namespace App\Http\Middleware;

use App\Services\MailConfigService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyPlatformCommunicationSettings
{
    public function __construct(private readonly MailConfigService $mailConfigService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->mailConfigService->apply();

        return $next($request);
    }
}
