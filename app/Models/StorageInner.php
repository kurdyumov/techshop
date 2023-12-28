<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageInner extends Model
{
    use HasFactory;
    protected $table = 'StorageInner';
    protected $primaryKey = 'SIID';
}
