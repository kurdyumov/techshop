<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SG extends Model
{
    use HasFactory;
    protected $table = 'storagegoods';
    protected $primaryKey = 'sku';
}
