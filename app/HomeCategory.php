<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HomeCategory extends Model
{
    protected $fillable = [
        'category_id',
        'color',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id');
    }
}
