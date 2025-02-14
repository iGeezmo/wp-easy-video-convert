<?php
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