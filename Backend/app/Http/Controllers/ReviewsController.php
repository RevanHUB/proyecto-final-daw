<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewsRequest;
use App\Http\Requests\UpdateReviewsRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Http\Scripts\Utils;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewsController extends Controller
{


    /**
     * Almacena una nueva review.
     *
     * Este método crea una nueva review utilizando los datos proporcionados en la solicitud.
     * Si la review se crea con éxito, devuelve una respuesta JSON con un mensaje de éxito y los detalles de la review creada.
     * Si ocurre algún error durante el proceso de creación de la review, devuelve una respuesta JSON con un mensaje de error.
     *
     * @param \Illuminate\Http\Request $request - La solicitud HTTP que contiene los datos de la review a crear.
     *
     * @return \Illuminate\Http\JsonResponse - Una respuesta JSON que indica el resultado de la operación de almacenamiento.
     *
     * @response 201 {
     *   "status": true,
     *   "message": "Review creada",
     *   "data": {
     *     "commerce_id": "ID_del_comercio",
     *     "content": "Contenido_del_comentario",
     *     "note": "Nota_de_la_review"
     *   }
     * }
     *
     * @response 403 {
     *   "status": false,
     *   "message": "Usuario inexistente"
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "El usuario $request->commerce_username no es un comercio"
     * }
     *
     * @response 403 {
     *   "status": false,
     *   "message": "Usuario inexistente"
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "El usuario $request->commerce_username no es un comercio"
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Error al crear la review: mensaje_de_error"
     * }
     */
    public function store(StoreReviewsRequest $request)
    {
        try {


            try {
                $id = User::where('username', '=', $request->commerce_username)->firstOrFail()->id;
            } catch (ModelNotFoundException $e) {
                return response()->json(['status' => false, 'message' => 'Usuario inexistente'], 404);
            }
            try {
                Commerce::where('user_id', '=', $id)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                return response()->json(['status' => false, 'message' => "El usuario $request->commerce_username no es un comercio"], 404);
            }

            // Crear una nueva Review
            $review = new Review([
                'user_id' => auth()->user()->id,
                'commerce_id' => $id,
                'comment' => $request->comment,
                'note' => $request->note,
            ]);

            $review->save();

            Utils::AVG_Reviews($id);

            return response()->json([
                'status' => true,
                'message' => 'Review creada',
                'data' => [
                    'commerce_id' => $review->commerce_id,
                    'content' => $review->comment,
                    'note' => $review->note,
                    'id' => $review->id
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error al crear la review: No puedes tener dos reviews de un mismo sitio '], 500);
        }
    }

    /**
     * Muestra las reviews relacionadas con un comercio.
     *
     * Esta función obtiene y formatea las reviews asociadas con un comercio específico.
     * Si no se encuentran reviews para el comercio, el usuario no existe o no es un comercio devuelve un mensaje de error.
     * Si no se encuentran reviews para el comercio, el usuario no existe o no es un comercio devuelve un mensaje de error.
     *
     * @param string $username - El username del comercio para el que se desean obtener las reviews.
     *
     * @return \Illuminate\Http\JsonResponse - Respuesta JSON que contiene las reviews formateadas.
     *
     * @response 200 {
     *   "status": true,
     *   "reviews": [
     *     {
     *       "commerce_id": "identificador_del_comercio",
     *       "user_id": "identificador_del_usuario",
     *       "username": "nombre_de_usuario",
     *       "avatarUsuario": "avatar_del_usuario",
     *       "commerce_username": "nombre_de_usuario_del_comercio",
     *       "avatarComercio": "avatar_del_comercio",
     *       "comment": "comentario_de_la_revision",
     *       "note": "nota_de_la_revision"
     *     },
     *     ...
     *   ]
     * }
     *
     * @response 401 {
     *   "status": false,
     *   "message": "No se encontraron reviews para este comercio"
     * }
     */
    public function show(string $username)
    {
        // Obtener todas las reviews para el comercio con el username dado

        try {

        try {

            $user = User::where('username', $username)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => "Usuario inexistente."], 403);
            } elseif (!(Commerce::where('commerces.user_id', '=', $user->id)->first())) {
                $userRol = $user->getRoleNames()[0];
                return response()->json(['status' => false, 'message' => "Este usuario no es un comercio.",  'rol' => $userRol], 403);
            }
            $user = User::where('username', $username)->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => "Usuario inexistente."], 403);
            } elseif (!(Commerce::where('commerces.user_id', '=', $user->id)->first())) {
                $userRol = $user->getRoleNames()[0];
                return response()->json(['status' => false, 'message' => "Este usuario no es un comercio.",  'rol' => $userRol], 403);
            }

            $reviews = Review::where('commerce_id', $user->id)->get();
            $reviews = Review::where('commerce_id', $user->id)->get();

            if ($reviews->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No se encontraron reviews para el comercio',], 401);
            }
            // Crear un array para almacenar los datos de las reviews
            $reviewsArray = [];
            if ($reviews->isEmpty()) {
                return response()->json(['status' => false, 'message' => 'No se encontraron reviews para el comercio',], 401);
            }
            // Crear un array para almacenar los datos de las reviews
            $reviewsArray = [];

            // Iterar sobre cada reviews
            foreach ($reviews as $review) {
                // Obtener los datos necesarios para cada reviews
                $reviewData = [
                    'username' => $review->user->username,
                    'avatarUsuario' => $review->user->avatar,
                    'commerce_username' => $review->commerce->username, // Obtener el nombre de usuario del comercio
                    'avatarComercio' => $review->commerce->avatar, // Obtener el avatar del comercio
                    'comment' => $review->comment,
                    'note' => $review->note,
                ];
            // Iterar sobre cada reviews
            foreach ($reviews as $review) {
                // Obtener los datos necesarios para cada reviews
                $reviewData = [
                    'username' => $review->user->username,
                    'avatarUsuario' => $review->user->avatar,
                    'commerce_username' => $review->commerce->username, // Obtener el nombre de usuario del comercio
                    'avatarComercio' => $review->commerce->avatar, // Obtener el avatar del comercio
                    'comment' => $review->comment,
                    'note' => $review->note,
                ];

                // Agregar los datos de la reviews al array
                $reviewsArray[] = $reviewData;
            }
                // Agregar los datos de la reviews al array
                $reviewsArray[] = $reviewData;
            }

            // Verificar si se encontraron reviews para el comercio
            if (count($reviewsArray) > 0) {
                // Devolver respuesta con las reviews formateadas
                return response()->json(['status' => true, 'reviews' => $reviewsArray,], 200);
            }
            // Verificar si se encontraron reviews para el comercio
            if (count($reviewsArray) > 0) {
                // Devolver respuesta con las reviews formateadas
                return response()->json(['status' => true, 'reviews' => $reviewsArray,], 200);
            }

            return response()->json(['status' => false, 'message' => 'Reviews no encontradas',], 404);
        } catch (\Exception $e) {
            // En caso de excepción, devolver una respuesta de error
            return response()->json(['status' => false, 'error' => 'Error al mostrar las reviews: ' . $e->getMessage(),], 500);
        }
            return response()->json(['status' => false, 'message' => 'Reviews no encontradas',], 404);
        } catch (\Exception $e) {
            // En caso de excepción, devolver una respuesta de error
            return response()->json(['status' => false, 'error' => 'Error al mostrar las reviews: ' . $e->getMessage(),], 500);
        }
    }



    /**
     * Actualiza una review existente.
     *
     * Este método busca una review por su ID y actualiza sus datos con los proporcionados en la solicitud.
     * Si la review se actualiza con éxito, devuelve una respuesta JSON con un mensaje de éxito.
     * Si la review no se encuentra, devuelve una respuesta JSON con un mensaje de error.
     * Si ocurre algún error durante el proceso de actualización de la review, devuelve una respuesta JSON con un mensaje de error.
     *
     * @param \Illuminate\Http\Request $request - La solicitud HTTP que contiene los datos actualizados de la review.
     * @param string $id - El ID de la review a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse - Una respuesta JSON que indica el resultado de la operación de actualización.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Review actualizada exitosamente"
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "La review no existe"
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Error al actualizar la review: mensaje_de_error"
     * }
     */
    public function update(UpdateReviewsRequest $request, string $id)
    {
        try {

            $user = Auth::user();
            // Buscar la review por su ID
            $review = Review::find($id);

            if ($review->user->id != $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Review no actualizada. No tienes permisos sobre esta review.',
                ], 403);
            }

            // Verificar si la review existe
            if (!$review) {
                return response()->json(['status' => false, 'message' => 'La review no existe',], 404);
            }

            // Guardar el commerce_id antes de actualizar la revisión
            $commerce_id = $review->commerce_id;

            // Actualizar la review con los datos proporcionados en la solicitud
            $review->update([
                'comment' => $request->comment,
                'note' => $request->note,
            ]);

            // Calcular y actualizar la puntuación media
            Utils::AVG_Reviews($commerce_id);

            // Devolver una respuesta de éxito
            return response()->json([
                'status' => true, 'message' => 'Review actualizada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            // En caso de excepción, devolver una respuesta de error
            return response()->json(['status' => false, 'message' => 'Error al actualizar la review: ' . $e->getMessage(),], 500);
        }
    }


    /**
     * Elimina una review.
     *
     * Este método busca una review por su ID y la elimina de la base de datos.
     * Si la review se elimina con éxito, devuelve una respuesta JSON con un mensaje de éxito.
     * Si la review no se encuentra, devuelve una respuesta JSON con un mensaje de error.
     * Si ocurre algún error durante el proceso de eliminación de la review, devuelve una respuesta JSON con un mensaje de error.
     *
     * @param string $id - El ID de la review a eliminar.
     *
     * @return \Illuminate\Http\JsonResponse - Una respuesta JSON que indica el resultado de la operación de eliminación.
     *
     * @response 200 {
     *   "status": true,
     *   "message": "review eliminada exitosamente"
     * }
     *
     * @response 404 {
     *   "status": false,
     *   "message": "La review no existe"
     * }
     *
     * @response 500 {
     *   "status": false,
     *   "message": "Error al eliminar la review: mensaje_de_error"
     * }
     */
    public function destroy(string $id)
    {
        try {

            $user = Auth::user();
            // Buscar la review por su ID
            $review = Review::find($id);

            if ($review->user->id != $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Review no eliminada. No tienes permisos sobre esta review.',
                ], 403);
            }

            // Verificar si la review existe
            if (!$review) {
                return response()->json(['status' => false, 'message' => 'La review no existe',], 404);
            }

            // Guardar el commerce_id antes de eliminar la revisión
            $commerce_id = $review->commerce_id;

            //Guardar la review en la tabla deleted_reviews

            $now = Carbon::now();
            $nowFormatted = $now->format('Y-m-d H:i:s');

            $reviewData = [
                'id' => $review->id,
                'user_id' => $review->user_id,
                'commerce_id' => $review->commerce_id,
                'comment' => $review->comment,
                'note' => $review->note,
                'deleted_date' => $nowFormatted
            ];

            DB::table('deleted_reviews')->insert($reviewData);

            // Eliminar la review
            $review->delete();

            Utils::AVG_Reviews($commerce_id);

            // Devolver una respuesta de éxito
            return response()->json([
                'status' => true, 'message' => 'review eliminada exitosamente',
            ], 200);
        } catch (\Exception $e) {
            // En caso de excepción, devolver una respuesta de error
            return response()->json(['status' => false, 'message' => 'Error al eliminar la review: ' . $e->getMessage(),], 500);
        }
    }
}
