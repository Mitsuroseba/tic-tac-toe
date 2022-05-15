<?php

namespace App\Models;

use App\Enum\Term;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameResource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'board',
        'score_x',
        'score_y',
        'current_turn',
        'victory',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'current_turn' => Term::class,
        'victory' => Term::class,
    ];

    public function setBoardAttribute($value)
    {
        $this->attributes['board'] = serialize($value);
    }

    public function getBoardAttribute()
    {
        return unserialize($this->attributes['board'], [Term::class]);
    }
}
