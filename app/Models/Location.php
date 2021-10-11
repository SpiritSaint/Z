<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasFactory;

    public function device() : BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_uuid', 'uuid');
    }

    public function geofence() : BelongsTo {
        return $this->belongsTo(Geofence::class, 'geofence_uuid', 'uuid');
    }
}
