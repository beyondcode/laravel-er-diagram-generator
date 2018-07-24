<?php

namespace BeyondCode\ErdGenerator\Tests\Traits;

use BeyondCode\ErdGenerator\Tests\Models\Comment;

trait HasComments
{

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
