<?php

namespace App\System\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogService
{
    public function record(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        array $metadata = [],
        ?Request $request = null,
    ): ActivityLog {
        $request ??= request();

        return ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata_json' => $metadata,
            'created_at' => now(),
        ]);
    }
}

