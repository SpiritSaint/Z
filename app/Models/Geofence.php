<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Geofence extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @return void
     */
    protected static function boot() {
        parent::boot();

        static::creating(function (Model $model) {
            $model->setAttribute($model->getKeyName(), Str::uuid());
        });
    }

    /**
     * @return HasMany
     */
    public function locations() : HasMany {
        return $this->hasMany(Location::class, 'geofence_uuid', 'uuid');
    }

    /**
     * @return BelongsToMany
     */
    public function devices() : BelongsToMany {
        return $this->belongsToMany(Device::class, 'device_geofence', 'geofence_uuid', 'device_uuid', 'uuid', 'uuid');
    }
}
