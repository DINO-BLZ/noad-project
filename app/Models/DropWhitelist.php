<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DropWhitelist extends Model
{
    protected $fillable = ['drop_id', 'user_id', 'status', 'drop_opened_notified_at'];

    protected function casts(): array
    {
        return [
            'drop_opened_notified_at' => 'datetime',
        ];
    }

    public function drop()
    {
        return $this->belongsTo(Drop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
