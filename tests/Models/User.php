<?php

namespace BeyondCode\ErdGenerator\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    public function doSomething($foo)
    {

    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function avatar()
    {
        return $this->hasOne(Avatar::class);
    }

}