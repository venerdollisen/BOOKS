<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date', 'status', 'notes'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // Methods
    public function close()
    {
        $this->status = 'closed';
        $this->save();
    }

    public function lock()
    {
        $this->status = 'locked';
        $this->save();
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }
}
