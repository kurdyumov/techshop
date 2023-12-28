<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSH extends Model
{
    use HasFactory;
    // история состояния жалобы
    protected $table = 'CSHistory';
}
