<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'prix',
        'desc',
        'imgBG',
        'duration',
        'category_id',
    ];
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class,'course_id');
    }
    public function categories(): BelongsTo
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Purchase::class, 'course_id', 'id', 'id', 'user_id');
    }
    public function purchaseitems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class,'course_id');
    }
}
