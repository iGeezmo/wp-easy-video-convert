<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function vtg_video_uploader_page() {
    ?>
    <div class="wrap">
        <h1>Video Uploader</h1>
        <form id="vtg-upload-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'vtg_upload_action', 'vtg_upload_nonce' ); ?>
            <input type="file" name="vtg_video_file" id="vtg-video-file" accept="video/*" multiple />
            <?php submit_button('Upload Video', 'primary', 'vtg_upload_button', false); ?>
        </form>
        <div id="vtg-upload-progress" style="display:none; margin-top:20px;">
            <p>Загрузка: <span id="vtg-upload-percent">0%</span></p>
            <div style="width:100%; background:#eee; border:1px solid #ccc;">
                <div id="vtg-upload-bar" style="width:0%; background:#76c7c0; height:20px;"></div>
            </div>
        </div>
        <div id="vtg-upload-result" style="margin-top:20px;"></div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('#vtg-upload-form').on('submit', function(e){
                e.preventDefault();
                var form_data = new FormData(this);
                form_data.append('action', 'vtg_upload_video');

                $('#vtg-upload-progress').show();
                $.ajax({
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $('#vtg-upload-percent').text(percentComplete + '%');
                                $('#vtg-upload-bar').css('width', percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    url: ajaxurl,
                    type: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            $('#vtg-upload-result').html('<p style="color:green;">Видео успешно загружено. Attachment ID: ' + response.data.attachment_id + '</p>');
                        } else {
                            $('#vtg-upload-result').html('<p style="color:red;">Ошибка: ' + response.data + '</p>');
                        }
                        $('#vtg-upload-progress').hide();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $('#vtg-upload-result').html('<p style="color:red;">Ошибка загрузки: ' + textStatus + '</p>');
                        $('#vtg-upload-progress').hide();
                    }
                });
            });
        });
    </script>
    <?php
}
