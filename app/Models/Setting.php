<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Setting extends Model
{
    protected $fillable = ['group_name', 'setting_key', 'setting_value', 'type', 'autoload'];

    protected $casts = [
        'autoload' => 'boolean',
    ];

    public function getFullKeyAttribute(): string
    {
        return "{$this->group_name}.{$this->setting_key}";
    }

    public function castValue(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->setting_value,
            'json' => json_decode($this->setting_value ?? '[]', true),
            'encrypted' => $this->decryptValue(),
            default => $this->setting_value,
        };
    }

    private function decryptValue(): ?string
    {
        if ($this->setting_value === null) {
            return null;
        }

        try {
            return decrypt($this->setting_value);
        } catch (Throwable) {
            return null;
        }
    }
}
