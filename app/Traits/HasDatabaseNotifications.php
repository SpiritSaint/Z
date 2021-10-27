<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Notifications\DatabaseNotification;

trait HasDatabaseNotifications
{
    /**
     * Get the entity's notifications.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    /**
     * Get the entity's read notifications.
     *
     * @return Builder
     */
    public function readNotifications()
    {
        return $this->notifications()->read();
    }

    /**
     * Get the entity's unread notifications.
     *
     * @return Builder
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }
}
