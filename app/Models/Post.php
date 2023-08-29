<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['title', 'body', 'user_id', 'image_url'];

    /**
     * The attributes that should be appended.
     *
     * @var array<int, string>
     */
    protected $appends = ['photo'];

    /**
     * Get the full url of a post's image
     *
     * @return mixed
     */
    public function getPhotoAttribute(): mixed
    {
        if ($this->image_url) {
            return config('filesystems.disks.photos.url') . '/' . $this->image_url;
        }

        return $this->image_url;
    }

    /**
    * Define the polymorphic relationship for favorites
    *
    * @return \Illuminate\Database\Eloquent\Relations\MorphMany
    */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }

    /**
    * Get the user who owns a given post
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
