<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'vtg_add_main_menu' );
add_action( 'admin_init', 'vtg_settings_init' );

function vtg_add_main_menu() {
    // Создаём пункт верхнего уровня
    add_menu_page(
        'Video Thumbnail Generator',    // Заголовок страницы
        'Video Thumbnail Generator',    // Название пункта меню
        'manage_options',               // Права доступа
        'video_thumbnail_generator',    // Slug
        'vtg_options_page',             // Функция отображения (ниже)
        'dashicons-video-alt3',         // Иконка меню
        50                               // Порядок
    );
// Привязка функции к хуку admin_menu
add_action('admin_menu', 'vtg_add_main_menu');

function vtg_add_main_menu() {
    // Создаём пункт верхнего уровня
    add_menu_page(
        'Video Thumbnail Generator',    // Заголовок страницы
        'Video Thumbnail Generator',    // Название пункта меню
        'manage_options',               // Права доступа
        'video_thumbnail_generator',    // Slug
        'vtg_options_page',             // Функция отображения
        'dashicons-video-alt3',         // Иконка меню
        50                              // Порядок
    );

    // Добавляем подпункт «Video Upload»
    add_submenu_page(
        'video_thumbnail_generator',
        'Video Upload',
        'Video Upload',
        'upload_files',
        'vtg_video_uploader',
        'vtg_video_uploader_page'
    );

    // Добавляем подпункт «Settings»
    add_submenu_page(
        'video_thumbnail_generator',
        'Settings',
        'Settings',
        'manage_options',
        'vtg_settings',
        'vtg_settings_page' // Отдельная функция отображения
    );
}

// Основная функция отображения
function vtg_options_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('У вас нет прав на доступ к этой странице.'));
    }
    echo '<h1>Video Thumbnail Generator</h1>';
    echo '<p>Добро пожаловать в плагин для генерации превью видео!</p>';
}

// Функция отображения страницы загрузки видео
function vtg_video_uploader_page() {
    if (!current_user_can('upload_files')) {
        wp_die(__('У вас нет прав на доступ к этой странице.'));
    }
    echo '<h1>Загрузка видео</h1>';
    echo '<p>Страница для загрузки и управления видео.</p>';
}

// Функция отображения настроек
function vtg_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('У вас нет прав на доступ к этой странице.'));
    }
    echo '<h1>Настройки Video Thumbnail Generator</h1>';
    echo '<form method="post" action="options.php">';
    // Добавьте поля и настройки
    echo '<p>Настройки будут здесь.</p>';
    echo '</form>';
}
    // Добавляем подпункт «Video Upload»
    add_submenu_page(
        'video_thumbnail_generator',
        'Video Upload',
        'Video Upload',
        'upload_files',
        'vtg_video_uploader',
        'vtg_video_uploader_page'
    );
    // Добавляем подпункт «Settings»
    add_submenu_page(
        'video_thumbnail_generator',
        'Settings',
        'Settings',
        'manage_options',
        'vtg_settings',  // Новый уникальный slug
        'vtg_options_page'
    );
}

function vtg_settings_init() {
    register_setting( 'vtgPluginPage', 'vtg_settings' );

    add_settings_section(
        'vtg_plugin_page_section',
        __( 'Настройки генерации миниатюр и конвертации видео', 'vtg' ),
        'vtg_settings_section_callback',
        'vtgPluginPage'
    );

    add_settings_field(
        'vtg_enable_thumbnail',
        __( 'Создание миниатюр', 'vtg' ),
        'vtg_enable_thumbnail_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    add_settings_field(
        'vtg_enable_conversion',
        __( 'Конвертация видео', 'vtg' ),
        'vtg_enable_conversion_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // Время для извлечения кадра (миниатюра)
    add_settings_field(
        'vtg_time_offset',
        __( 'Время для извлечения кадра', 'vtg' ),
        'vtg_time_offset_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // Разрешение видео
    add_settings_field(
        'vtg_resolution',
        __( 'Разрешение видео', 'vtg' ),
        'vtg_resolution_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // CRF (качество)
    add_settings_field(
        'vtg_crf',
        __( 'Качество видео (CRF)', 'vtg' ),
        'vtg_crf_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // Пресет кодирования (dropdown + custom)
    add_settings_field(
        'vtg_preset',
        __( 'Пресет кодирования', 'vtg' ),
        'vtg_preset_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // Опция Fast Start
    add_settings_field(
        'vtg_faststart',
        __( 'Fast Start', 'vtg' ),
        'vtg_faststart_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );

    // Обработка субтитров
    add_settings_field(
        'vtg_subtitle',
        __( 'Обработка субтитров', 'vtg' ),
        'vtg_subtitle_render',
        'vtgPluginPage',
        'vtg_plugin_page_section'
    );
}

// Функции отображения
function vtg_enable_thumbnail_render() {
    $options = get_option( 'vtg_settings' );
    ?>
    <input type='checkbox' name='vtg_settings[vtg_enable_thumbnail]' <?php checked( $options['vtg_enable_thumbnail'], 1 ); ?> value='1'>
    <p class="description">Включить создание миниатюр при загрузке видео.</p>
    <?php
}

function vtg_enable_conversion_render() {
    $options = get_option( 'vtg_settings' );
    ?>
    <input type='checkbox' name='vtg_settings[vtg_enable_conversion]' <?php checked( $options['vtg_enable_conversion'], 1 ); ?> value='1'>
    <p class="description">Включить конвертацию видео при загрузке.</p>
    <?php
}

function vtg_time_offset_render() {
    $options = get_option( 'vtg_settings' );
    ?>
    <input type='text' name='vtg_settings[vtg_time_offset]' value='<?php echo isset( $options['vtg_time_offset'] ) ? esc_attr( $options['vtg_time_offset'] ) : '00:00:05'; ?>' placeholder="00:00:05">
    <p class="description">Укажите время (ЧЧ:ММ:СС) для извлечения кадра миниатюры. Пример: "00:00:05" – через 5 секунд от начала видео.</p>
    <?php
}

function vtg_resolution_render() {
    $options = get_option( 'vtg_settings' );
    ?>
    <input type='text' name='vtg_settings[vtg_resolution]' value='<?php echo isset( $options['vtg_resolution'] ) ? esc_attr( $options['vtg_resolution'] ) : '1280x720'; ?>' placeholder="1280x720">
    <p class="description">Введите разрешение выходного видео, например, "1280x720" для HD или "1920x1080" для Full HD.</p>
    <?php
}

function vtg_crf_render() {
    $options = get_option( 'vtg_settings' );
    ?>
    <input type='number' name='vtg_settings[vtg_crf]' value='<?php echo isset( $options['vtg_crf'] ) ? esc_attr( $options['vtg_crf'] ) : '23'; ?>' min="18" max="28">
    <p class="description">CRF определяет качество сжатия: от 18 (высокое качество) до 28 (низкое качество). Рекомендуется 23.</p>
    <?php
}

function vtg_preset_render() {
    $options = get_option( 'vtg_settings' );
    $preset = isset( $options['vtg_preset'] ) ? $options['vtg_preset'] : 'slow';
    $custom = isset( $options['vtg_preset_custom'] ) ? $options['vtg_preset_custom'] : '';
    $default_presets = array(
        'ultrafast' => 'ultrafast',
        'superfast' => 'superfast',
        'veryfast'  => 'veryfast',
        'faster'    => 'faster',
        'fast'      => 'fast',
        'medium'    => 'medium',
        'slow'      => 'slow',
        'slower'    => 'slower',
        'veryslow'  => 'veryslow',
        'custom'    => 'Custom'
    );
    ?>
    <select id="vtg_preset_select" name="vtg_settings[vtg_preset]">
        <?php foreach ( $default_presets as $key => $label ): ?>
            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $preset, $key ); ?>>
                <?php echo esc_html( ucfirst( $label ) ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br>
    <label for="vtg_preset_custom">Или введите своё значение:</label>
    <input type="text" id="vtg_preset_custom" name="vtg_settings[vtg_preset_custom]" value="<?php echo esc_attr( $custom ); ?>" placeholder="Введите пресет">
    <p class="description">Выберите предустановленный набор параметров для конвертации или выберите "Custom" для ввода собственного значения.</p>
    <script type="text/javascript">
    (function($){
        $('#vtg_preset_select').change(function(){
            if($(this).val() === 'custom'){
                $('#vtg_preset_custom').show();
            } else {
                $('#vtg_preset_custom').hide();
            }
        });
        if($('#vtg_preset_select').val() === 'custom'){
            $('#vtg_preset_custom').show();
        } else {
            $('#vtg_preset_custom').hide();
        }
    })(jQuery);
    </script>
    <?php
}

function vtg_faststart_render() {
    $options = get_option( 'vtg_settings' );
    $faststart = isset( $options['vtg_faststart'] ) ? $options['vtg_faststart'] : 'enabled';
    ?>
    <select name="vtg_settings[vtg_faststart]">
        <option value="enabled" <?php selected( $faststart, 'enabled' ); ?>>Enabled</option>
        <option value="disabled" <?php selected( $faststart, 'disabled' ); ?>>Disabled</option>
    </select>
    <p class="description">Включите Fast Start для перемещения метаданных (moov atom) в начало файла – это ускоряет начало воспроизведения видео.</p>
    <?php
}

function vtg_subtitle_render() {
    $options = get_option( 'vtg_settings' );
    $subtitle = isset( $options['vtg_subtitle'] ) ? $options['vtg_subtitle'] : 'none';
    ?>
    <select name="vtg_settings[vtg_subtitle]">
        <option value="none" <?php selected( $subtitle, 'none' ); ?>>None</option>
        <option value="mov_text" <?php selected( $subtitle, 'mov_text' ); ?>>Embed Subtitles (mov_text)</option>
    </select>
    <p class="description">Выберите способ обработки субтитров. Опция "Embed Subtitles (mov_text)" встроит субтитры в видео (если они имеются).</p>
    <?php
}

function vtg_settings_section_callback() {
    echo __( 'Укажите все параметры для конвертации видео. "Время для извлечения кадра" используется для создания миниатюры, остальные параметры – для перекодировки видео.', 'vtg' );
}

function vtg_options_page() {
    ?>
    <div class="wrap">
        <h1>Video Thumbnail Generator Settings</h1>
        <?php echo vtg_get_status(); ?>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'vtgPluginPage' );
            do_settings_sections( 'vtgPluginPage' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function vtg_get_status() {
    $status_html = '';
    
    if ( ! function_exists( 'exec' ) || ! is_callable( 'exec' ) ) {
        $status_html .= '<div style="background-color: #fdd; padding: 10px; margin-bottom: 15px;"><strong>Ошибка:</strong> Функция <code>exec</code> недоступна.</div>';
        return $status_html;
    }
    
    $ffmpeg_check = shell_exec( 'ffmpeg -version 2>&1' );
    if ( stripos( $ffmpeg_check, 'ffmpeg version' ) === false ) {
        $status_html .= '<div style="background-color: #fdd; padding: 10px; margin-bottom: 15px;"><strong>Ошибка:</strong> FFmpeg не установлен или недоступен.</div>';
    } else {
        $lines = explode( "\n", $ffmpeg_check );
        $version_info = isset( $lines[0] ) ? trim( $lines[0] ) : 'Версия неизвестна';
        $status_html .= '<div style="background-color: #dfd; padding: 10px; margin-bottom: 15px;"><strong>Статус:</strong> FFmpeg установлен. ' . esc_html( $version_info ) . '</div>';
    }
    
    return $status_html;
}