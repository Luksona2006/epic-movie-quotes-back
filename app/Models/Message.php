<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Message extends Pivot
{
    /**
    * Indicates if the IDs are auto-incrementing.
    *
    * @var bool
    */
    public $incrementing = true;

    protected $guarded = ['id'];
    protected $table = 'messages';
}
