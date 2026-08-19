<?php

namespace App\System\Services;

use PDO;
use Throwable;

class DatabaseConnectionService
{
    public function check(array $credentials): array
    {
        $host = $credentials['DB_HOST'] ?? '127.0.0.1';
        $port = $credentials['DB_PORT'] ?? '3306';
        $database = $credentials['DB_DATABASE'] ?? '';
        $username = $credentials['DB_USERNAME'] ?? '';
        $password = $credentials['DB_PASSWORD'] ?? '';

        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ],
            );

            return [
                'ok' => true,
                'message' => 'Database connection established.',
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'message' => $exception->getMessage(),
            ];
        }
    }
}

