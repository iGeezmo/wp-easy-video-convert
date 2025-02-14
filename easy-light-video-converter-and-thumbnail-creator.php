<?php
/*
Plugin Name: Easy Light Video Converter and Thumbnail Creator
Description: При загрузке видео запускается процесс конвертации и генерации миниатюры через FFmpeg с обновлением прогресса. На странице редактирования вложения отображается прогресс обработки, а на странице настроек – статус (наличие функции exec, FFmpeg и его версия) и параметры конвертации. Также добавлена собственная страница загрузки видео в админке.
Version: 1.7.0
Author: Wize Digital
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Предотвращаем прямой доступ.
}

// Подключаем модули плагина
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-uploader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/conversion.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/ajax.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/metabox.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-logs.php';
