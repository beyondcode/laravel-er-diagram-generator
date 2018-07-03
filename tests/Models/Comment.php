<?php

namespace BeyondCode\ErdGenerator\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}