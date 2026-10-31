<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    protected static function booted(): void
    {
        static::creating(function (Model $activity): void {
            if (request()->hasSession() && request()->ip()) {
                $activity->ip_address = request()->ip();
            }
        });
    }
}
