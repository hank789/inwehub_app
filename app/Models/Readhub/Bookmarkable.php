<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/11/14 下午4:46
 * @email: hank.huiwang@gmail.com
 */

trait Bookmarkable
{
    /**
     * Fetch all bookmarks for the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookmarks()
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    /**
     * Scope a query to records bookmarked by the given user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $user_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBookmarkedBy($query, $user_id)
    {
        return $query->whereHas('bookmarks', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        });
    }

    /**
     * Determine if the model is bookmarked by the given user.
     *
     * @param $user_id
     *
     * @return bool
     */
    public function isBookmarkedBy($user_id)
    {
        return $this->bookmarks()
            ->where('user_id', $user_id)
            ->exists();
    }

    /**
     * Have the authenticated user bookmark the model.
     * If the authenticated user has already bookmarked the model, un-bookmarks it.
     *
     * @return void
     */
    public function bookmark($user_id)
    {
        if ($this->isBookmarkedBy($user_id)) {
            $this->bookmarks()->where(['user_id' =>$user_id])->delete();

            return 'unbookmarked';
        }

        $this->bookmarks()->save(
            new Bookmark(['user_id' => $user_id])
        );

        return 'bookmarked';
    }
}
