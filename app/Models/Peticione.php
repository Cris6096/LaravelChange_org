<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Peticione extends Model
{
    use HasFactory, Notifiable;
    protected $fillable = ['titulo', 'descripcion', 'destinatario', 'firmantes', 'estado'];
    public function categoria(){
        return $this->belongsTo('App\Models\Categoria');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function firmantes(){
        return $this->belongsToMany('App\Models\User');
    }

}
