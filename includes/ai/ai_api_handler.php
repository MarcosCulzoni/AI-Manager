
<?php

/*Este archivo se encarga del manejo  de las conexiones a la API de IA 
Esta funcion tendria que recibir absolutamente todos los parametros  que se usan en la consulta
de esa manera se podrian "ajustar y personalizar" mas los shortcodes*/

// includes/ia/ia_api_handler.php
if (!defined('ABSPATH')) {
    exit;
}

class IA_API_Handler
{
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        // Recupera la clave API almacenada en WP
        $IA_manager_option = get_option('ia_manager_options');
        $this->api_key = $IA_manager_option['api_primary_key'];
    }









    public function enviar_consulta($params)

    {
        // Verifica si existe la clave API
        if (!$this->api_key) {
            return ['error' => 'No hay clave API configurada.'];
        }


        // Asignación de valores a partir del array $params
        $mensaje = isset($params['mensaje']) ? $params['mensaje'] : '';
        $modelo = isset($params['modelo']) ? $params['modelo'] : 'gpt-3.5-turbo';
        $role = isset($params['role']) ? $params['role'] : 'user';
        $temperature = isset($params['temperature']) ? $params['temperature'] : 0.7;
        $max_tokens = isset($params['max_tokens']) ? $params['max_tokens'] : 150;
        $timeout = isset($params['timeout']) ? $params['timeout'] : 30;



        // Construye los datos de la consulta, usando los valores pasados o los valores por defecto
        $data = [
            'model' => $modelo, // Modelo de IA
            'messages' => [['role' => $role, 'content' => $mensaje]], // El mensaje y el rol (user, assistant, etc.)
            'temperature' => $temperature, // Controla la aleatoriedad de la respuesta
            'max_tokens' => $max_tokens // Limita la cantidad de tokens en la respuesta
        ];

        // Realiza la solicitud POST a la API de OpenAI
        $response = wp_remote_post($this->api_url, [
            'body' => json_encode($data), // El cuerpo de la solicitud
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            // Definir el timeout (tiempo máximo de espera para la respuesta)
            'timeout'   => $timeout, // Timeout definido como parámetro
        ]);

        // Verifica si hay algún error en la solicitud HTTP
        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        // Recupera el cuerpo de la respuesta de la API
        $body = wp_remote_retrieve_body($response); //se utiliza para extraer el cuerpo de la respuesta que se obtuvo al  hacer una solicitud HTTP 

        // Devuelve la respuesta decodificada (array asociativo)
        return json_decode($body, true);
    }


















    public function enviar_consultaDEPRECATED($mensaje, $modelo = 'gpt-3.5-turbo')
    {
        if (!$this->api_key) {
            return ['error' => 'No hay clave API configurada.'];
        }

        $data = [
            'model' => $modelo,
            'messages' => [['role' => 'user', 'content' => $mensaje]],
            'temperature' => 0.7,
            'max_tokens' => 150
        ];

        $response = wp_remote_post($this->api_url, [
            'body' => json_encode($data),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            // Definir el timeout en 30 segundos
            'timeout'   => 30, // 30 segundos
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response); //se utiliza para extraer el cuerpo de la respuesta que se obtuvo al  hacer una solicitud HTTP 
        return json_decode($body, true);
    }
}
