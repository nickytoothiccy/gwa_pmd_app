<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PmdCnetFfu extends Model
{
    protected $table = 'pmd_cnet_ffu';
    public $timestamps = false;
    protected $primaryKey = 'Equipment';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'Equipment',
        'Parent',
        'Network',
        'Port',
        'CNX_Sequence',
        'Comment'
    ];
}
