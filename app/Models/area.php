<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Model;

class area extends Model
{
    //
    protected $table= 'areas';
    protected $fillable = [
        'nombre'
    ];

    public function personal(): HasMany
    {
        return $this->hasMany(Personal::class);
    }
}
