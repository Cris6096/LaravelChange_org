<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeticioneController extends Controller
{
    public function index()
    {
        $peticiones = Peticione::all();
        return response()->json($peticiones, 200);

    }

    public function show($id)
    {
        $peticione = Peticione::findOrFail($id);
        return response()->json($peticione, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:25',
            'descripcion' => 'required|string|max:25',
            'destinatario' => 'required|string|max:25',
            'firmantes' => 'required|integer',
            'estado' => ['required', 'in:aceptada,pendiente'],
            'categoria_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        $peticione = new Peticione();
        $peticione->titulo = $request->input('titulo');
        $peticione->descripcion = $request->input('descripcion');
        $peticione->destinatario = $request->input('destinatario');
        $peticione->firmantes = $request->input('firmantes');
        $peticione->estado = $request->input('estado');

        $categoria = Categoria::findOrFail($request->input('categoria_id'));

        // HARDCODEAMOS EL USER CREATOR
        $user_id = 1;
        $user = User::findOrFail($user_id);

        $peticione->user()->associate($user);
        $peticione->categoria()->associate($categoria);

        $peticione->save();

        return response()->json([
            'message' => 'Petición Created',
            'data' => $peticione
        ], 202);
    }

    public function firmar(Request $request, $id,$user_id){
        $peticione = Peticione::findOrFail($id);
        $user = User::findOrFail($user_id);
        $usersfirmante = $peticione->firmantes() -> get();
        foreach ($usersfirmante as $userfirma){
            if($userfirma->user_id == $user_id){
                return response()->json(['messaje'=>'Este usuario ya ha firmado esta peticion','data'=>$peticione],409 );
            }
        }
        $peticione->firmantes()->attach($user);
        $peticione-> firmantes=$peticione->firmantes+1;
        $peticione->save();
        return response()->json(['message' => 'Peticione Created', 'data' =>$peticione], 202);

    }


    public function verCategoria($id){
        $peticiones = Peticione::findOrFail($id);
        $categorias = $peticiones->categoria()->get();
        return response()->json(['message' => 'Categoria Encontrada', 'data' => $categorias], 202);

    }

    public function verUsuariosFirmantes($id){
        $peticiones = Peticione::findOrFail($id);
        $usuariosfirmantes = $peticiones->firmantes()->get();
        return response()->json(['message' => 'Usuarios Firmantes Encontrados', 'data' => $peticiones->firmantes,$usuariosfirmantes], 202);
    }

    public function updateEstado(Request $request, $id){
        $peticione= Peticione::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'in:aceptada,pendiente'],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }else{
            $peticione->estado = $request->input('estado');
            $peticione->save();
            return response()->json(['message' => 'Peticione Estado Updated', 'data' => $peticione], 202);
        }
    }


    public function update($id, Request $request){
        $peticione = Peticione::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:25',
            'descripcion' => 'required|string|max:25',
            'destinatario' => 'required|string|max:25',
            //'firmantes' => 'required|integer', este no porque implica un cambio significante en las demás tablas
            //'estado' => ['required', 'in:aceptada,pendiente'], para eso ya tenemos updateEstado
            'categoria_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'No se pudo actualizar'], 400);
        }
        $peticione->titulo = $request->input('titulo');
        $peticione->descripcion = $request->input('descripcion');
        $peticione->destinatario = $request->input('destinatario');
        $peticione->firmantes = $request->input('categoria_id');

        $res=$peticione->update();
        if($res){
            return response()->json(['message' => 'Peticione Updated', 'data' => $peticione], 202);
        }else{
            return response()->json(['message' => 'Peticione Not Updated'], 409);
        }
    }

    public function delete($id){
        $peticione = Peticione::findOrFail($id);
        if ($peticione->firmantes > 0) {
            return response()->json(['message' => 'Error peticion firmada no se puede borrar'], 409);
        }
        $peticione->delete();
        return response()->json(['message' => 'Peticion eliminada', 'data' => $peticione], 200);
    }
}
