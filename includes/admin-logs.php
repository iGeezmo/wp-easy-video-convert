<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Вспомогательная функция для записи логов в отдельный файл плагина.
 */
function vtg_log( $message ) {
    // Определяем каталог для логов: ../logs от каталога includes
    $log_dir = plugin_dir_path( __FILE__ ) . '../logs';
    if ( ! file_exists( $log_dir ) ) {
        mkdir( $log_dir, 0755, true );
    }
    $log_file = $log_dir . '/my_plugin.log';
    $time = date( 'Y-m-d H:i:s' );
    $entry = "[$time] " . $message . "\n";
    file_put_contents( $log_file, $entry, FILE_APPEND );
}

/**
 * Регистрируем страницу логов в меню плагина.
 */
function vtg_add_logs_page() {
    add_submenu_page(
        'video_thumbnail_generator',   // Родительский slug верхнего уровня
        'Conversion Logs',             // Заголовок страницы
        'Conversion Logs',             // Название пункта меню
        'manage_options',              // Права доступа
        'vtg_conversion_logs',         // Уникальный slug страницы
        'vtg_conversion_logs_page'     // Функция отображения страницы
    );
}
add_action( 'admin_menu', 'vtg_add_logs_page' );

/**
 * Функция отображения страницы логов.
 */
function vtg_conversion_logs_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'У вас недостаточно прав для доступа к этой странице.' );
    }
    
    // Путь к файлу логов нашего плагина
    $log_file = plugin_dir_path( __FILE__ ) . '/../logs/my_plugin.log';
    ?>
    <div class="wrap">
        <h1>Conversion Logs</h1>
        <?php
        if ( file_exists( $log_file ) ) {
            echo '<pre style="background: #000; color: #0f0; padding: 10px; overflow: auto; max-height: 600px;">';
            echo esc_html( file_get_contents( $log_file ) );
            echo '</pre>';
        } else {
            echo '<p>Лог-файл пуст или отсутствует.</p>';
        }
        ?>
    </div>
    <?php
}
