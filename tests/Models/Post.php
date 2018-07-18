<?php

namespace BeyondCode\ErdGenerator\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use BeyondCode\ErdGenerator\Tests\Traits\HasComments;

class Post extends Model
{
    use HasComments;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
