<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'rate', 'fetched_at'];

    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'fetched_at' => 'datetime',
        ];
    }
}