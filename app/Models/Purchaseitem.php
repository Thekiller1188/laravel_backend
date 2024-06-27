<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchaseitem extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase_id',
        'course_id',
    ];
    public function courses(): BelongsTo
    {
        return $this->belongsTo(Course::class,'course_id');
    }
    public function purchases(): BelongsTo
    {
        return $this->belongsTo(Purchase::class,'purchase_id');
    }
}
