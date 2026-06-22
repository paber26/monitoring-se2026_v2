<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'level_5_full_code',
        'nama_sls',
        'total_assignment_fasih',
        'ppl',
        'pml',
    ];
}
