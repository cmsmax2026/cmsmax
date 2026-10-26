<?php

namespace Modules\Main\Http\Middleware;

use App\Support\Security\IpAccessRuleService;
use Closure;

class CheckIpBlacklist {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $result = IpAccessRuleService::inspect((string) get_ip(), IpAccessRuleService::SCOPE_ADMIN);
        if ($result['blocked']) return redirect('admin/blacklist');
        return $next($request);
    }
}
