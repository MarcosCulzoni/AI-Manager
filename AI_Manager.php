<?php
/*
Plugin Name: AI Manager
Description: Gestión de claves API de IA con funciones avanzadas para consultas y generación de texto.
Version: 1.0
Author: Marcos Culzoni
Icon: /wp-content/plugins/mi-plugin/assets/mi_logo.webp
*/

if (!defined('ABSPATH')) {
    exit; // Evita el acceso directo al archivo para proteger el sistema
}



// Función para registrar y cargar el archivo CSS en el frontend del sitio
function mi_plugin_registrar_estilos() {
    wp_enqueue_style(
        'mi-plugin-estilos', // Identificador único para evitar conflictos con otros estilos
        plugin_dir_url(__FILE__) . 'assets/css/IA_Manager.css', // Ruta al archivo CSS dentro del plugin
        array(), // Lista de dependencias (vacía si no depende de otros estilos)
        '1.0.0', // Versión del archivo CSS para control de caché
        'all' // Tipo de medio (all = todos los dispositivos)
    );
}

// Conectar la función anterior al hook que encola estilos y scripts en el frontend
add_action('wp_enqueue_scripts', 'mi_plugin_registrar_estilos');






/*================================== INSTALACION =========================================================================
No existe un hook específico para la instalación de un plugin en WordPress porque, lógicamente, el código del plugin no se 
ejecuta hasta que está activado. Sin embargo, puedes usar el hook register_activation_hook para simular tareas de instalación,
 ya que este se ejecuta cuando el plugin se activa por primera vez. Con este hook, puedes realizar tareas como crear tablas en 
 la base de datos o configurar valores iniciales necesarios para el funcionamiento del plugin.                                                      */





/*================================== ACTIVACION ==========================================================================
- Crear la base de datos (tablas u opciones en wp_options).
- Crear la página del formulario con wp_insert_post().
- Guardar claves API y email en la base de datos si no existen.
- Establecer valores por defecto en wp_options.                                                                         */



/*cuaneo  activo el  plugin y recupero opciones hay dos posivilidades, que sea la primera vez que se active en cuyo caso
pondre valores por defecto o que ya esten los datos porque el plugin se ha desactivado pero no borrado en ese caso los valores ya 
estan y tengo que recuperarlos */

// Estoy activand el código del handler, que crea el hook que se ejecuta cuando se preciona el boton del formulairo
include_once plugin_dir_path(__FILE__) . 'includes/api_key_form_handler.php';



function crear_pagina_configuracion()
{
    // Función para crear la página automáticamente cuando se activa el plugin

    $titulo = 'WP IA Manager Configuración General'; // Nombre largo y específico para evitar coincidencias
    $slug   = 'wp_ia_manager_configuracion_general';   // Slug único para evitar duplicados

    // Si ya existe una página con este slug se eliminará
    $pagina_existente = get_page_by_path($slug, OBJECT, 'page');
    if ($pagina_existente) {
        wp_delete_post($pagina_existente->ID, true); // true: Elimina permanentemente
    }

    // Crear la página con el nombre, slug y contenido definido
    $pagina_id = wp_insert_post([
        'post_title'   => $titulo,
        'post_name'    => $slug,
        'post_content' => '[mi_plugin_formulario]', // Shortcode para el contenido
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ]);

    // Comprobar si hubo error al insertar el post
    if (is_wp_error($pagina_id)) {
        // Manejar el error (podrías registrarlo en un log en lugar de hacer echo)
        error_log('Error al insertar el post: ' . $pagina_id->get_error_message());
    } else {
        // Guardar el ID de la página en las opciones de WordPress
        update_option('IA_Manager_Config_ID', $pagina_id);
    }
}


// Registrar el shortcode para mostrar el formulario
function mi_plugin_mostrar_formulario()
{
    ob_start();
    include plugin_dir_path(__FILE__) . 'includes/api_keys_form.php';// Se icluye el formulario API_key_form.php
    include plugin_dir_path(__FILE__) . 'includes/modal.php';        // Se incluye la pantalla modal de confirmación modal.php                                                             // Se incluye Panalla modal
    
    return ob_get_clean();
}
add_shortcode('mi_plugin_formulario', 'mi_plugin_mostrar_formulario');





function IA_manager_activation()
{
    crear_pagina_configuracion();
}



register_activation_hook(__FILE__, 'IA_manager_activation'); //hook para crear la pagina al activar el plugin




/*================================== DESACTIVACION ======================================================================================
- Eliminar la página del formulario con wp_delete_post().
- No borrar la base de datos, solo detener la funcionalidad del plugin.       */

function eliminar_pagina_configuracion()
{
    // Obtener el ID de la página de configuración almacenado en las opciones de WordPress
    $pagina_id = get_option('IA_Manager_Config_ID');

    // Verificar que se haya obtenido un ID válido
    if ($pagina_id) {
        // Comprobar si la página existe
        if (get_post($pagina_id)) {
            // Eliminar la página de forma permanente
            wp_delete_post($pagina_id, true);
        } else {
            // Si la página no existe, registrar un mensaje en el log para depuración
            error_log("No se encontró la página de configuración con ID: $pagina_id.");
        }
        // Eliminar la opción para limpiar la base de datos
        delete_option('IA_Manager_Config_ID');
    } else {
        // Si la opción no existe, registrar un mensaje en el log
        error_log("No existe la opción 'IA_Manager_Config_ID' en la base de datos.");
    }
}



register_deactivation_hook(__FILE__, 'eliminar_pagina_configuracion');



/* ================================== DESINSTALACION =============================
-Borrar claves API y email de la base de datos.
-Borrar cualquier tabla o dato creado por el plugin.
-Eliminar opciones guardadas en wp_options.
-Asegurar que no quede rastro del plugin en WordPress.   */
