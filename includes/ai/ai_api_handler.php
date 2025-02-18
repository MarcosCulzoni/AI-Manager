
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

    public function enviar_consulta($mensaje, $modelo = 'gpt-3.5-turbo')
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

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
