<?php

namespace Vertex\Forms\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Form extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'settings',
        'sort_order',
        'is_active',
        'require_login',
        'entry_limit',
        'daily_limit',
        'available_from',
        'available_to',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_login' => 'boolean',
        'available_from' => 'datetime',
        'available_to' => 'datetime',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class)->orderBy('version_number', 'desc');
    }

    public function settingsWithoutSecrets(bool $mask = false): array
    {
        $settings = $this->settings ?? [];

        foreach ($settings['webhooks'] ?? [] as $index => $webhook) {
            if (array_key_exists('secret', $webhook)) {
                $settings['webhooks'][$index]['secret'] = $mask ? '********' : '';
            }
        }

        return $settings;
    }

    protected function settings(): Attribute
    {
        return Attribute::make(
            get: function ($value): array {
                $settings = json_decode($value ?: '[]', true) ?: [];

                foreach ($settings['webhooks'] ?? [] as $index => $webhook) {
                    $secret = (string) ($webhook['secret'] ?? '');
                    if (! str_starts_with($secret, 'encrypted:')) {
                        continue;
                    }

                    try {
                        $settings['webhooks'][$index]['secret'] = Crypt::decryptString(substr($secret, 10));
                    } catch (DecryptException) {
                        $settings['webhooks'][$index]['secret'] = '';
                    }
                }

                return $settings;
            },
            set: function ($value): string {
                $settings = is_array($value) ? $value : [];

                foreach ($settings['webhooks'] ?? [] as $index => $webhook) {
                    $secret = (string) ($webhook['secret'] ?? '');
                    if ($secret !== '' && ! str_starts_with($secret, 'encrypted:')) {
                        $settings['webhooks'][$index]['secret'] = 'encrypted:'.Crypt::encryptString($secret);
                    }
                }

                return json_encode($settings, JSON_THROW_ON_ERROR);
            },
        );
    }
}
