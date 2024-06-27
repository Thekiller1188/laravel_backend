<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "path",
        "course_id",
        "position",
    ];
    public function courses(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
