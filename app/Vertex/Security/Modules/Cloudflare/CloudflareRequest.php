<?php

namespace App\Vertex\Security\Modules\Cloudflare;

use Illuminate\Http\Request;

class CloudflareRequest
{
    public function visitorIp(Request $request): string
    {
        if (! config('security.cloudflare.trust_visitor_headers') || ! $this->isTrustedProxy((string) $request->server('REMOTE_ADDR'))) {
            return (string) $request->ip();
        }

        return filter_var($request->header('CF-Connecting-IP'), FILTER_VALIDATE_IP)
            ? (string) $request->header('CF-Connecting-IP') : (string) $request->ip();
    }

    public function isTrustedProxy(string $ip): bool
    {
        foreach ((array) config('security.cloudflare.trusted_proxies', []) as $cidr) {
            if ($this->inCidr($ip, $cidr)) {
                return true;
            }
        }

        return false;
    }

    private function inCidr(string $ip, string $cidr): bool
    {
        [$network, $prefix] = array_pad(explode('/', $cidr, 2), 2, null);
        $address = @inet_pton($ip);
        $base = @inet_pton($network);
        if ($address === false || $base === false || strlen($address) !== strlen($base)) {
            return false;
        }
        $prefix = $prefix === null ? strlen($address) * 8 : (int) $prefix;
        if ($prefix < 0 || $prefix > strlen($address) * 8) {
            return false;
        }
        $bytes = intdiv($prefix, 8);
        $bits = $prefix % 8;
        if (substr($address, 0, $bytes) !== substr($base, 0, $bytes)) {
            return false;
        }

        return $bits === 0 || ((ord($address[$bytes]) ^ ord($base[$bytes])) & (0xFF << (8 - $bits))) === 0;
    }
}
