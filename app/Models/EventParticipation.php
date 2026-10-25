<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventParticipation extends Model
{
    use HasFactory;

    protected $table = 'event_participations';

    protected $fillable = [
        'event_id',
        'voter_id',
        'has_voted',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'has_voted' => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function voter()
    {
        return $this->belongsTo(Voter::class, 'voter_id');
    }
}
