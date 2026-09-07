<?php

namespace App\Concerns;

trait LocksRowsForUpdate
{
    public function withRowLock($query)
    {
        return $query->lockForUpdate();
    }
}