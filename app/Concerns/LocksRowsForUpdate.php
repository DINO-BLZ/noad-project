<?php

namespace App\Concerns;

trait LocksRowsForUpdate
{
    public function withRowLock($query)
    {
        if (config('database.default') === 'sqlite') {
            return $query;
        }

        return $query->lockForUpdate();
    }
}
