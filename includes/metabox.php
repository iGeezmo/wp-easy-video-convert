<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vtg_add_progress_metabox() {
    add_meta_box(
        'vtg_progress_metabox',
        'Статус конвертации видео',
        'vtg_progress_metabox_callback',
        'attachment',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes_attachment', 'vtg_add_progress_metabox' );

function vtg_progress_metabox_callback( $post ) {
    ?>
    <div id="vtg-progress-container">
        <p id="vtg-progress-text">Идет обработка видео...</p>
        <div style="width:100%; background:#eee; border:1px solid #ccc;">
            <div id="vtg-progress-bar" style="width:0%; background:#76c7c0; height:20px;"></div>
        </div>
    </div>
    <script type="text/javascript">
    (function($){
        function updateProgress() {
            $.ajax({
                url: ajaxurl,
                data: {
                    action: 'vtg_get_conversion_progress',
                    attachment_id: <?php echo intval( $post->ID ); ?>
                },
                success: function(response) {
                    if (response.success) {
                        var progress = response.data.progress;
                        $('#vtg-progress-bar').css('width', progress + '%');
                        $('#vtg-progress-text').text('Обработка: ' + progress + '%');
                        if (progress < 100) {
                            setTimeout(updateProgress, 1000);
                        } else {
                            $('#vtg-progress-text').text('Обработка завершена.');
                        }
                    }
                }
            });
        }
        updateProgress();
    })(jQuery);
    </script>
    <?php
}