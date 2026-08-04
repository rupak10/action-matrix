<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoInfo extends Model
{
    public $timestamps = false;

    protected $table = 'po_info';

    protected $fillable = [
        'po_code',
        'po_short_name',
        'po_name',
        'is_active',
        'created_by',
        'updated_by',
    ];
}
