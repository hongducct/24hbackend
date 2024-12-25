<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
    ];
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}