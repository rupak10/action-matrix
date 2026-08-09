<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcmVisitTracker extends Model
{
    protected $table = 'acm_visit_tracker';
    protected $primaryKey = 'po_code';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['po_code', 'sl'];
}
