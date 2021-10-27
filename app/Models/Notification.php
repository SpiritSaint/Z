<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Str;

class Notification extends Model
{
    /**
     * @var string
     */
    protected $primaryKey = 'uuid';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
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
     * Get the notifiable entity that the notification belongs to.
     *
     * @return MorphTo
     */
    public function notifiable() : MorphTo {
        return $this->morphTo('notifiable', 'notifiable_type', 'notifiable_uuid');
    }

    /**
     * Mark the notification as read.
     *
     * @return void
     */
    public function markAsRead() : void {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     *
     * @return void
     */
    public function markAsUnread() : void {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     *
     * @return bool
     */
    public function read() : bool {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     *
     * @return bool
     */
    public function unread() : bool {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include read notifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeRead(Builder $query) : Builder {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnread(Builder $query) : Builder {
        return $query->whereNull('read_at');
    }

    /**
     * Create a new database notification collection instance.
     *
     * @param  array  $models
     * @return DatabaseNotificationCollection
     */
    public function newCollection(array $models = []) : DatabaseNotificationCollection {
        return new DatabaseNotificationCollection($models);
    }
}
