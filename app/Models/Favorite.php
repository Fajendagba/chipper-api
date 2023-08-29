<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    public const TYPE_POST = 'post';
    public const TYPE_USER = 'user';
    public const TYPES = [
        self::TYPE_POST,
        self::TYPE_USER,
    ];


    protected $fillable = ['favoritable_id', 'favoritable_type', 'user_id'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($favorite) {
            if (is_null($favorite->user_id)) {
                $favorite->user_id = auth()->id();
            }
        });
    }

    /**
     * Define the reverse of the polymorphic relationship for favorites
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }
}
