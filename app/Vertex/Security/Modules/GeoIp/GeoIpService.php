<?php

namespace App\Vertex\Security\Modules\GeoIp;

use Illuminate\Http\Request;

class GeoIpService
{
    public function locate(string $ip, ?Request $request = null): array
    {
        if ($request && config('security.geoip.trusted_headers') && $request->headers->has('CF-IPCountry')) {
            return ['ip' => $ip, 'country_code' => strtoupper((string) $request->header('CF-IPCountry')), 'source' => 'trusted-header'];
        }

        foreach ($this->records() as $record) {
            if ($this->inCidr($ip, $record['cidr'])) {
                return ['ip' => $ip, 'country_code' => $record['country_code'], 'source' => 'local-database'];
            }
        }

        return ['ip' => $ip, 'country_code' => null, 'source' => 'unknown'];
    }

    public function isAllowed(array $location): bool
    {
        $country = $location['country_code'] ?? null;
        if (! $country) {
            return true;
        }
        $allowed = (array) config('security.geoip.allowed_countries', []);

        return ! in_array($country, (array) config('security.geoip.blocked_countries', []), true)
            && ($allowed === [] || in_array($country, $allowed, true));
    }

    private function records(): array
    {
        $path = (string) config('security.geoip.local_database');
        if (! is_readable($path)) {
            return [];
        }
        $records = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            [$cidr, $country] = array_pad(array_map('trim', str_getcsv($line)), 2, null);
            if ($cidr && $country) {
                $records[] = ['cidr' => $cidr, 'country_code' => strtoupper($country)];
            }
        }

        return $records;
    }

    private function inCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = array_pad(explode('/', $cidr, 2), 2, null);
        $ipBin = @inet_pton($ip);
        $subnetBin = @inet_pton($subnet);
        if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
            return false;
        }
        $bits = $bits === null ? strlen($ipBin) * 8 : (int) $bits;
        if ($bits < 0 || $bits > strlen($ipBin) * 8) {
            return false;
        }
        $bytes = intdiv($bits, 8);
        $remainder = $bits % 8;
        if (substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
            return false;
        }

        return $remainder === 0 || ((ord($ipBin[$bytes]) ^ ord($subnetBin[$bytes])) & (0xFF << (8 - $remainder))) === 0;
    }
}
