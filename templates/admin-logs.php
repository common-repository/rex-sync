<?php
use Rex\Sync\Logger;

$file_urls = Logger::get_file_urls();

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Logs', 'rex-sync') ?></h1>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <?php
                    if(!$file_urls):
                    ?>
                    <p>No files found</p>
                    <?php else: ?>
                    <ul>
                        <?php
                        foreach($file_urls as $f):
                            $name = basename($f);
                            ?>
                        <li><a href="<?php esc_attr_e($f); ?>" target="_blank"><?php esc_html_e($name) ?></a> [<a href="javascript:;" class="js-delete-log" data-file="<?php esc_attr_e($name); ?>">Delete</a>]</li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif ?>
                </div>
                <div class="col-2"></div>
            </div>
        </div>
    </div>
</div>
<script>
    (function($){
        var $body = $('body');
        $body.on('click', '.js-delete-log', function(e){
            e.preventDefault();

            var cfm = confirm('Delete this log file?');
            if(!cfm)
                return false;

            $(this).closest('li').fadeOut();

            var filename = $(this).data('file');
            $.post(ajaxurl, {
                action: 'rsc_delete_log',
                file: filename
            });

            return false;
        });
    })(jQuery);
</script>