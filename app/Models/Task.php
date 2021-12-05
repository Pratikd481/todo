<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'due_date',
        'parent_id',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function status()
    {
        return $this->hasOne(TaskStatus::class, 'status');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($tasks) { // before delete() method call this
            $tasks->children()->delete();
            // do the rest of the cleanup...
        });
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', '==', '1')->orWhere('status', '==', '0')->orWhereNull('status')->orderBy('due_date', 'DESC');
    }

    public function scopeMainTask(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function scopeDueToday(Builder $query): void
    {
        $query->where('due_date', '==', Carbon::now()->toDateTimeString());
    }

    public function scopeDueThisWeek(Builder $query): void
    {
        $query->where('due_date', '>=', Carbon::now()->subWeek(1)->toDateTimeString());
    }

    public function scopeDueNextWeek(Builder $query): void
    {
        $query->where('due_date', '<=', Carbon::now()->subWeek(1)->toDateTimeString())->where('due_date', '>=', Carbon::now()->subWeek(2)->toDateTimeString());
    }



    public function scopeOverDue(Builder $query): void
    {
        $query->where('due_date', '>', Carbon::now()->toDateTimeString());
    }
}
