<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FD extends Model
{
    use HasFactory;
    protected $table = 'filtdistrib';
    protected $primaryKey = 'distribid';
}
