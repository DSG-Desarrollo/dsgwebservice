<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class WialonService
{
    private $sid = null;
    private $base_api_url = '';
    private $default_params = [];

    public function __construct($scheme = 'http', $host = 'hst-api.wialon.com', $port = '', $sid = '', $extra_params = [])
    {
        $this->sid = '';
        $this->default_params = array_replace([], (array)$extra_params);
        $this->base_api_url = sprintf('%s://%s%s/wialon/ajax.html?', $scheme, $host, mb_strlen($port) > 0 ? ':' . $port : '');
    }

    public function setSid($sid)
    {
        $this->sid = $sid;
    }

    public function getSid()
    {
        return $this->sid;
    }

    public function updateExtraParams($params)
    {
        $this->default_params = array_replace($this->default_params, $params);
    }

    /**
     * Makes an API call to Wialon.
     *
     * @param string $action
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    public function call($action, $args)
    {
        try {
            $url = $this->base_api_url;
            $svc = str_replace('_', '/', $action);

            $params = [
                'svc' => $svc,
                'params' => $args,
                'sid' => $this->sid
            ];

            $all_params = array_replace($this->default_params, $params);
            $str = '';
            foreach ($all_params as $k => $v) {
                if (mb_strlen($str) > 0) {
                    $str .= '&';
                }
                $str .= $k . '=' . urlencode(is_object($v) || is_array($v) ? json_encode($v) : $v);
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $str,
                CURLOPT_TIMEOUT => 30 // Set timeout
            ]);

            $result = curl_exec($ch);
            if ($result === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);
            
            // Asegúrate de que el resultado es una cadena JSON
            if (is_array($result)) {
                return json_encode($result);
            }

            return $this->parseResponse($result);
        } catch (Exception $e) {
            Log::error('Wialon API call failed', ['error' => $e->getMessage()]);
            return json_encode(['error' => -1, 'message' => $e->getMessage()]);
        }
    }

    public function login($token)
    {
        $data = ['token' => urlencode($token)];
        $result = $this->call('token_login', $data);
    
        // Verificar si el resultado es un array o una cadena JSON
        if (is_array($result)) {
            return $result; // Devuelve el array directamente
        }
    
        $json_result = json_decode($result, true);
    
        // Manejar posible error en la respuesta
        if (isset($json_result['error']) && $json_result['error'] !== 0) {
            return ['error' => $json_result['error']];
        }
    
        // Establecer el SID si se obtuvo correctamente
        if (isset($json_result['eid'])) {
            $this->sid = $json_result['eid'];
        }
    
        return $json_result; // Retornar el resultado JSON completo
    }    

    public function logout()
    {
        $result = $this->call('core_logout', []);
        $json_result = json_decode($result, true);

        if ($json_result && $json_result['error'] === 0) {
            $this->sid = '';
        }

        return $json_result;
    }

    /**
     * Parses the API response and handles errors.
     *
     * @param string $response
     * @return mixed
     */
    private function parseResponse($response)
    {
        Log::info('Parsing response', ['response' => $response]); // Loguea la respuesta

        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON response', ['response' => $response]);
            return ['error' => -1, 'message' => 'Invalid JSON response'];
        }

        if (isset($json['error']) && $json['error'] !== 0) {
            Log::error('Wialon API returned an error', ['response' => $json]);
        }

        return $json;
    }
}
