<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    public function locations() : HasMany {
        return $this->hasMany(Location::class, 'device_uuid', 'uuid');
    }

    public function geofences() : BelongsToMany {
        return $this->belongsToMany(Geofence::class, 'device_geofence', 'device_uuid', 'geofence_uuid', 'uuid', 'uuid');
    }
}
