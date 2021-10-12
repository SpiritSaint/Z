<?php

namespace App\Models;

use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory, SpatialTrait;

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
     * @var array
     */
    protected $spatialFields = [
        'coords'
    ];

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
     * @return BelongsTo
     */
    public function device() : BelongsTo {
        return $this->belongsTo(Device::class, 'device_uuid', 'uuid');
    }

    /**
     * @return BelongsTo
     */
    public function geofence() : BelongsTo {
        return $this->belongsTo(Geofence::class, 'geofence_uuid', 'uuid');
    }
}
