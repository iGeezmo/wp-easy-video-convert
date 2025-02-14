<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . '/common.php';

/**
 * Проверяет, была ли уже обработка, чтобы избежать дублирования.
 *
 * @param int $post_ID Идентификатор видео.
 */
function vtg_process_video( $post_ID ) {
    writePluginLog("=== НАЧАЛО ОБРАБОТКИ ===");

    if ( get_post_meta( $post_ID, 'vtg_processed', true ) === 'yes' ) {
        writePluginLog("Файл с ID {$post_ID} уже обработан, пропускаем.");
        return;
    }

    $options = get_option( 'vtg_settings' );
    $original_file_path = get_attached_file( $post_ID );

    if ( isset( $options['vtg_enable_thumbnail'] ) && $options['vtg_enable_thumbnail'] == 1 ) {
        vtg_generate_video_thumbnail( $post_ID );
        writePluginLog("Миниатюра успешно создана для файла с ID: {$post_ID}");
    } else {
        writePluginLog("Создание миниатюры отключено.");
    }

    if ( isset( $options['vtg_enable_conversion'] ) && $options['vtg_enable_conversion'] == 1 ) {
        vtg_convert_video( $post_ID, $original_file_path );
        writePluginLog("Видео успешно конвертировано для файла с ID: {$post_ID}");
    } else {
        writePluginLog("Конвертация видео отключена.");
    }

    update_post_meta( $post_ID, 'vtg_processed', 'yes' );
    writePluginLog("Флаг vtg_processed установлен для файла с ID: {$post_ID}");
    writePluginLog("=== ОКОНЧАНИЕ ОБРАБОТКИ ===");
}

/**
 * Получает длительность видео в секундах с помощью ffprobe.
 *
 * @param string $file_path Путь к видеофайлу.
 * @return float Длительность видео.
 */
function vtg_get_video_duration( $file_path ) {
    $command = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg( $file_path );
    $duration = shell_exec( $command );
    return floatval( $duration );
}

/**
 * Генерация миниатюры для видео с обновлением прогресса.
 *
 * @param int $post_ID Идентификатор видео.
 */
function vtg_generate_video_thumbnail( $post_ID ) {
    if ( ! function_exists( 'proc_open' ) || ! is_callable( 'proc_open' ) ) {
        writePluginLog('Easy Light Video Converter: функция proc_open недоступна.');
        return;
    }

    $ffmpeg_check = shell_exec( 'ffmpeg -version 2>&1' );
    if ( stripos( $ffmpeg_check, 'ffmpeg version' ) === false ) {
        writePluginLog('Easy Light Video Converter: FFmpeg не установлен или недоступен.');
        return;
    }

    $file_path = get_attached_file( $post_ID );
    if ( ! file_exists( $file_path ) || strpos( mime_content_type( $file_path ), 'video' ) === false ) {
        return;
    }

    update_post_meta( $post_ID, 'vtg_thumbnail_status', 'processing' );
    update_post_meta( $post_ID, 'vtg_thumbnail_progress', 0 );

    $file_info = pathinfo( $file_path );
    $thumbnail_filename = $file_info['filename'] . '-thumb.jpg';
    $thumbnail_path = $file_info['dirname'] . '/' . $thumbnail_filename;

    $options = get_option( 'vtg_settings' );
    $time_position = isset( $options['vtg_time_offset'] ) && ! empty( $options['vtg_time_offset'] )
        ? $options['vtg_time_offset']
        : "00:00:05";

    $duration = vtg_get_video_duration( $file_path );
    if ( $duration <= 0 ) {
        $duration = 1;
    }

    $cmd = 'ffmpeg -i ' . escapeshellarg( $file_path ) .
        ' -ss ' . escapeshellarg( $time_position ) .
        ' -vframes 1 ' . escapeshellarg( $thumbnail_path ) .
        ' -progress pipe:1';

    // Записываем команду для отладки
    writePluginLog("VTG Thumbnail Command: " . $cmd);

    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    $process = proc_open( $cmd, $descriptorspec, $pipes );
    if ( is_resource( $process ) ) {
        fclose( $pipes[0] );
        while ( ! feof( $pipes[1] ) ) {
            $line = fgets( $pipes[1] );
            if ( $line === false ) {
                break;
            }
            if ( strpos( $line, 'out_time_ms=' ) === 0 ) {
                $out_time_ms = floatval( trim( str_replace( 'out_time_ms=', '', $line ) ) );
                $progress = min( 100, round( ( $out_time_ms / ( $duration * 1000000 ) ) * 100, 2 ) );
                update_post_meta( $post_ID, 'vtg_thumbnail_progress', $progress );
            }
        }
        fclose( $pipes[1] );
        fclose( $pipes[2] );
        proc_close( $process );
        update_post_meta( $post_ID, 'vtg_thumbnail_status', 'completed' );
        writePluginLog("Миниатюра создана успешно для файла с ID: {$post_ID}");
    }
}

/**
 * Конвертация видео с заданными настройками и обновлением прогресса.
 */
function vtg_convert_video( $post_ID, $original_file_path ) {
    $file_path = $original_file_path;  // Используем переданный путь к исходному видео

    if ( ! shell_exec('which ffmpeg') ) {
        vtg_log('Ошибка: FFmpeg не найден на сервере.');
        return;
    }

    if ( ! file_exists( $file_path ) ) {
        vtg_log("Ошибка: Исходный файл не найден: " . $file_path);
        return;
    }

    $file_info = pathinfo( $file_path );
    $converted_filename = $file_info['filename'] . '-converted.mp4';
    $converted_path = $file_info['dirname'] . '/' . $converted_filename;

    // Удаляем существующий выходной файл перед началом конвертации
    if ( file_exists( $converted_path ) ) {
        unlink( $converted_path );
        vtg_log("Удален существующий файл: " . $converted_path);
    }

    $options = get_option( 'vtg_settings' );
    $resolution = $options['vtg_resolution'] ?? '1280x720';
    $crf = $options['vtg_crf'] ?? 23;
    $preset = $options['vtg_preset'] ?? 'medium';
    $faststart = $options['vtg_faststart'] === 'enabled' ? '-movflags +faststart' : '';

    $cmd = 'ffmpeg -y -i ' . escapeshellarg( $file_path ) . 
           ' -s ' . escapeshellarg( $resolution ) . 
           ' -c:v libx264 -preset ' . escapeshellarg( $preset ) . 
           ' -crf ' . escapeshellarg( $crf ) . 
           ' -c:a aac ' . $faststart . ' ' . 
           escapeshellarg( $converted_path ) . ' -progress pipe:1';
    
    vtg_log("VTG Convert Command: " . $cmd);
    shell_exec($cmd);

    if ( file_exists( $converted_path ) ) {
        update_post_meta( $post_ID, 'vtg_conversion_status', 'completed' );
        update_post_meta( $post_ID, 'vtg_conversion_progress', 100 );
        vtg_log("Конвертация завершена: " . $converted_path);
    } else {
        vtg_log("Ошибка: Конвертированный файл не создан.");
    }
}