<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['po_code', 'sl'])]
class AcmTracker extends Model
{
    protected $table = 'acm_tracker';
    public $timestamps = false;
    protected $primaryKey = ['po_code', 'sl'];
    public $incrementing = false;
}
