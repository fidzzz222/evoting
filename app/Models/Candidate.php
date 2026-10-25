<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'candidate_number',
        'name',
        'photo_url',
        'description',
        'vision',
        'mission',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class, 'candidate_id');
    }

    public function getChairmanNameAttribute()
    {
        if (str_contains($this->name ?? '', '&')) {
            $parts = explode('&', $this->name);
            return trim($parts[0]);
        }
        return $this->name;
    }

    public function getViceChairmanNameAttribute()
    {
        if (str_contains($this->name ?? '', '&')) {
            $parts = explode('&', $this->name);
            return isset($parts[1]) ? trim($parts[1]) : null;
        }
        return null;
    }

    public function getDisplayNameAttribute()
    {
        return $this->name;
    }

    public function getPhotoAttribute()
    {
        return $this->photo_url;
    }
}
