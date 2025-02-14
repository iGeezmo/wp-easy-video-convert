<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Определяем константы для логирования
define('PLUGIN_LOGS_DIR', plugin_dir_path( __FILE__ ) . '../logs');
define('PLUGIN_LOG_FILE', PLUGIN_LOGS_DIR . '/my_plugin.log');

/**
 * Записывает сообщение в лог плагина.
 *
 * @param string $message Текст сообщения.
 */
function writePluginLog( $message ) {
    if ( ! file_exists( PLUGIN_LOGS_DIR ) ) {
        mkdir( PLUGIN_LOGS_DIR, 0755, true );
    }
    $time  = date( 'Y-m-d H:i:s' );
    $entry = "[$time] " . $message . "\n";
    file_put_contents( PLUGIN_LOG_FILE, $entry, FILE_APPEND );
}

/**
 * Регистрирует страницу логов в меню плагина.
 */
function registerPluginLogsPage() {
    add_submenu_page(
        'video_thumbnail_generator',  // Родительский slug
        'Conversion Logs',            // Заголовок страницы
        'Conversion Logs',            // Название пункта меню
        'manage_options',             // Права доступа
        'vtg_conversion_logs',        // Уникальный slug страницы
        'renderPluginLogsPage'        // Функция отображения страницы
    );
}
add_action( 'admin_menu', 'registerPluginLogsPage' );

/**
 * Отображает страницу логов плагина.
 */
function renderPluginLogsPage() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'У вас недостаточно прав для доступа к этой странице.' );
    }
    ?>
    <div class="wrap">
        <h1>Conversion Logs</h1>
        <?php
        if ( file_exists( PLUGIN_LOG_FILE ) ) {
            echo '<pre style="background: #000; color: #0f0; padding: 10px; overflow: auto; max-height: 600px;">';
            echo esc_html( file_get_contents( PLUGIN_LOG_FILE ) );
            echo '</pre>';
        } else {
            echo '<p>Лог-файл пуст или отсутствует.</p>';
        }
        ?>
    </div>
    <?php
}
?>