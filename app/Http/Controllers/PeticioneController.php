<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Peticione;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PeticioneController extends Controller
{
    /**
     * Muestra un listado con todas las peticiones registradas.
     * Ruta: GET /api/peticiones
     */
    public function index()
    {
        $peticiones = Peticione::all();
        // Éxito de lectura -> 200 OK
        return response()->json($peticiones, 200);
    }

    /**
     * Muestra una petición concreta buscada por su ID (lanza 404 automáticamente si no existe).
     * Ruta: GET /api/peticiones/{id}
     */
    public function show($id)
    {
        $peticione = Peticione::findOrFail($id);
        // Éxito de lectura -> 200 OK
        return response()->json($peticione, 200);
    }

    /**
     * ALMACENA UNA NUEVA PETICIÓN (Método clave explicativo):
     * 1. Valida los campos mediante Validator::make (el estado solo permite 'aceptada' o 'pendiente').
     * 2. Busca la categoría relacionada utilizando el ID proporcionado (findOrFail lanza 404 si no existe).
     * 3. Hardcodea un usuario creador por defecto ($user_id = 1) simulando la autoría.
     * 4. Utiliza el método ->associate() de Eloquent para vincular la petición al usuario y a la categoría antes de guardarla.
     * 5. Retorna la respuesta con código 201 (Created) por ser una inserción de recurso exitosa.
     * Ruta: POST /api/peticiones
     */
    public function store(Request $request)
    {
        // 1. Validación estricta de los datos de entrada
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:25',
            'descripcion' => 'required|string|max:25',
            'destinatario' => 'required|string|max:25',
            'firmantes' => 'required|integer',
            'estado' => ['required', 'in:aceptada,pendiente'],
            'categoria_id' => 'required|integer',
        ]);

        // Si la validación falla, retornamos errores con código 400 (Bad Request)
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        // 2. Instanciación y asignación de atributos básicos
        $peticione = new Peticione();
        $peticione->titulo = $request->input('titulo');
        $peticione->descripcion = $request->input('descripcion');
        $peticione->destinatario = $request->input('destinatario');
        $peticione->firmantes = $request->input('firmantes');
        $peticione->estado = $request->input('estado');

        // 3. Búsqueda de la categoría relacionada
        $categoria = Categoria::findOrFail($request->input('categoria_id'));

        // 4. Hardcodeamos el ID del usuario creador (usuario con ID 1)
        $user_id = 1;
        $user = User::findOrFail($user_id);

        // 5. Asociamos las relaciones (pertenece a User y pertenece a Categoria) mediante associate()
        $peticione->user()->associate($user);
        $peticione->categoria()->associate($categoria);

        // 6. Guardado definitivo en la base de datos
        $peticione->save();

        // 7. Retornamos éxito de creación -> 201 Created (Estándar REST correcto)
        return response()->json([
            'message' => 'Petición Created',
            'data' => $peticione
        ], 201);
    }

    /**
     * Permite a un usuario firmar una petición (Relación Muchos a Muchos / N:M).
     * Comprueba si ya la ha firmado previamente para evitar duplicados.
     * Ruta: POST /api/peticiones/{id}/firmar/{user_id}
     */
    public function firmar(Request $request, $id, $user_id)
    {
        $peticione = Peticione::findOrFail($id);
        $user = User::findOrFail($user_id);

        // Obtenemos los usuarios firmantes actuales de la relación
        $usersfirmante = $peticione->firmantes()->get();

        // Comprobación de duplicados de firma
        foreach ($usersfirmante as $userfirma) {
            if ($userfirma->user_id == $user_id) {
                // Conflicto de reglas de negocio -> 409 Conflict
                return response()->json(['messaje' => 'Este usuario ya ha firmado esta peticion', 'data' => $peticione], 409);
            }
        }

        // Adjuntamos el usuario a la tabla pivote mediante attach()
        $peticione->firmantes()->attach($user);

        // Actualizamos el contador total de firmantes en la tabla principal
        $peticione->firmantes = $peticione->firmantes + 1;
        $peticione->save();

        // Éxito al crear una relación/recurso -> 201 Created
        return response()->json(['message' => 'Peticione Created', 'data' => $peticione], 201);
    }

    /**
     * Muestra la categoría asociada a una petición específica.
     * Ruta: GET /api/peticiones/{id}/categoria
     */
    public function verCategoria($id)
    {
        $peticiones = Peticione::findOrFail($id);
        $categorias = $peticiones->categoria()->get();
        // Éxito de lectura (GET) -> Corregido a 200 OK
        return response()->json(['message' => 'Categoria Encontrada', 'data' => $categorias], 200);
    }

    /**
     * Muestra los usuarios que han firmado una petición.
     * Ruta: GET /api/peticiones/{id}/firmantes
     */
    public function verUsuariosFirmantes($id)
    {
        $peticiones = Peticione::findOrFail($id);
        $usuariosfirmantes = $peticiones->firmantes()->get();
        // Éxito de lectura (GET) -> Corregido a 200 OK
        return response()->json(['message' => 'Usuarios Firmantes Encontrados', 'data' => [$peticiones->firmantes, $usuariosfirmantes]], 200);
    }

    /**
     * Actualiza únicamente el estado de una petición mediante PATCH.
     * Ruta: PATCH /api/peticiones/{id}/estado
     */
    public function updateEstado(Request $request, $id)
    {
        $peticione = Peticione::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'estado' => ['required', 'in:aceptada,pendiente'],
        ]);

        if ($validator->fails()) {
            // Error de validación -> 400 Bad Request
            return response()->json($validator->messages(), 400);
        } else {
            $peticione->estado = $request->input('estado');
            $peticione->save();
            // Éxito de actualización -> Corregido a 200 OK
            return response()->json(['message' => 'Peticione Estado Updated', 'data' => $peticione], 200);
        }
    }

    /**
     * Actualiza los datos generales de una petición (PUT).
     * Ruta: PUT /api/peticiones/{id}
     */
    public function update($id, Request $request)
    {
        $peticione = Peticione::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:25',
            'descripcion' => 'required|string|max:25',
            'destinatario' => 'required|string|max:25',
            'categoria_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            // Error de validación -> 400 Bad Request
            return response()->json(['message' => 'No se pudo actualizar'], 400);
        }

        $peticione->titulo = $request->input('titulo');
        $peticione->descripcion = $request->input('descripcion');
        $peticione->destinatario = $request->input('destinatario');
        $peticione->firmantes = $request->input('categoria_id'); // (Mantiene la asignación tal como venía en el código original)

        $res = $peticione->update();

        if ($res) {
            // Éxito de actualización -> Corregido a 200 OK
            return response()->json(['message' => 'Peticione Updated', 'data' => $peticione], 200);
        } else {
            // Error de conflicto al actualizar -> 409 Conflict
            return response()->json(['message' => 'Peticione Not Updated'], 409);
        }
    }

    /**
     * Elimina una petición siempre y cuando no tenga firmantes activos.
     * Ruta: DELETE /api/peticiones/{id}
     */
    public function delete($id)
    {
        $peticione = Peticione::findOrFail($id);

        // Restricción de negocio: No se puede borrar si tiene firmantes asociados (> 0)
        if ($peticione->firmantes > 0) {
            // Conflicto de reglas de negocio -> 409 Conflict
            return response()->json(['message' => 'Error peticion firmada no se puede borrar'], 409);
        }

        $peticione->delete();
        // Éxito de borrado -> 200 OK
        return response()->json(['message' => 'Peticion eliminada', 'data' => $peticione], 200);
    }
}
