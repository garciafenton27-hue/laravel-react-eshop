<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'image_path', 'is_primary'];
    public $timestamps = false; // Usually images table doesn't need timestamps, but migration didn't have them? Wait, migration didn't specify images table actually?
    // Checking migration... "product_images" was in the plan but I might have missed creating the migration file for it explicitly?
    // Task 9 says "Products (products, categories, product_images)". I created categories and products migrations.
    // I missed product_images migration! I need to create it.

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
