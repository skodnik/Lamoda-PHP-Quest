<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public $timestamps = false;

    public function name()
    {
        return $this->belongsTo(Name::class);
    }
}
