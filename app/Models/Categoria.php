<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function peticiones(){
        return $this->hasMany('App\Models\Peticione');
    }

    public function users(){
        return $this->belongsToMany('App\Models\User');
    }
}

/*
one to one
(user=hasOne
address belongsTo),

one to many
    (event=belongsTo
    typeEvent=belongsToMny)

many to many
(user= belongsToMany
event=belongsToMany)

*/
