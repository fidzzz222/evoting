<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Candidate $candidate) {
            $candidate->deletePhoto();
        });
    }

    /**
     * Upload and store candidate photo, cleaning up prior photo if present.
     */
    public function savePhoto($file): string
    {
        $this->deletePhoto();

        $path = $file->store('candidates', 'public');
        $this->photo_url = 'storage/' . $path;

        if ($this->exists) {
            $this->save();
        }

        return $this->photo_url;
    }

    /**
     * Delete existing candidate photo from public storage.
     */
    public function deletePhoto(): void
    {
        if (! $this->photo_url) {
            return;
        }

        if (str_starts_with($this->photo_url, 'http://') || str_starts_with($this->photo_url, 'https://')) {
            return;
        }

        $relativePath = preg_replace('#^/?storage/#', '', $this->photo_url);
        $relativePath = ltrim($relativePath, '/');

        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

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

    public function getVotesCountAttribute(): int
    {
        if (array_key_exists('votes_count', $this->attributes)) {
            return (int) $this->attributes['votes_count'];
        }

        return (int) ($this->relationLoaded('votes') ? $this->votes->count() : $this->votes()->count());
    }
}
