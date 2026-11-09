<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'address',
        'status',
        'employees_count',
        'progress',
        'period_end',
    ];

    protected $casts = [
        'period_end' => 'date',
    ];

    public function assignedShifts(): HasMany
    {
        return $this->hasMany(AssignedShift::class);
    }
}
