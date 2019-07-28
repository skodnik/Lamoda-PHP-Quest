<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany(Item::class);
    }

}
