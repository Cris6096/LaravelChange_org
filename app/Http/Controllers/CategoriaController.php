<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriaController extends Controller
{

    public function index(){
        $categorias = Categoria::all();
        return response()->json($categorias, 200);

    }

    public function store(Request $request){
        $categoria = new Categoria();
        $nombre = $this->validateNombre($request);
        if($nombre->fails()){
            return response()->json($nombre->messages(), 422);
        }

        $categoria = Categoria::create($nombre->validate());
        return response()->json(['message'=>'Categoria Created','data'=>$categoria], 200);
    }

    public function show($id){
        $categoria=Categoria::findOrFail($id);
        return response()->json($categoria, 200);
    }

    public function validateNombre(Request $request){
        return Validator::make(request()->all(), [
            'nombre' => 'required|string|max:25']);
    }

    public function listPeticiones($id){
     $categoria = Categoria::findOrFail($id);
     $peticiones = $categoria->peticiones()->get();
     $cantidad = $peticiones->count();
     return response()->json(['messaje'=>'Peticiones De Categoria Encontrada','data'=>$cantidad, $peticiones], 202);
    }

}
