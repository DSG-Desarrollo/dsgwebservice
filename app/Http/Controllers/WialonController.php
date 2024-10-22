<?php

namespace App\Http\Controllers;

use App\Facades\Wialon;
use Illuminate\Http\Request;

class WialonController extends Controller
{
    /**
     * Realiza el login en Wialon con el token y guarda el SID.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $token = $request->input('token');

        // Llamada al servicio Wialon usando el facade
        $response = Wialon::login($token);

        // Manejar respuesta y error
        if (isset($response['error']) && $response['error'] !== 0) {
            return response()->json(['message' => 'Login fallido', 'error' => $response['error']], 500);
        }

        // Retornar el SID correctamente
        return response()->json(['message' => 'Login exitoso', 'sid' => $response['eid'] ?? null], 200);
    }

    /**
     * Cierra la sesión de Wialon.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $response = Wialon::logout();

        if (isset($response['error']) && $response['error'] !== 0) {
            return response()->json(['message' => 'Logout fallido', 'error' => $response['error']], 500);
        }

        return response()->json(['message' => 'Logout exitoso'], 200);
    }

    /**
     * Realiza una llamada genérica a Wialon.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callWialon(Request $request)
    {
        $action = $request->input('action');
        $params = $request->input('params');

        $response = Wialon::call($action, $params);

        $json_response = json_decode($response, true);

        if (isset($json_response['error']) && $json_response['error'] !== 0) {
            return response()->json(['message' => 'Error en la llamada a Wialon', 'error' => $json_response['error']], 500);
        }

        return response()->json(['data' => $json_response], 200);
    }
}
