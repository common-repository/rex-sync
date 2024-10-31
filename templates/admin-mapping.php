<?php

use Rex\Sync\Loader;
use Rex\Sync\Helper;

$settings = Loader::get_settings();

$listing_fields_mapping = $settings['listing_fields_mapping'];
$listing_custom_fields_mapping = $settings['listing_custom_fields_mapping'];

$listing_demo_fields = Loader::get_listing_demo_fields();

function render_fields_dropdown($html_name, $listing_demo_fields, $selected_value){
    echo '<select name="'.esc_attr($html_name).'" class="dropdown-rex-field">';
    echo "<option value=''>".__('Please select', 'rex-sync')."</option>";
    foreach($listing_demo_fields as $key=>$text){
        echo "<option ".($key == $selected_value ? 'selected':'').">".esc_html($key)."</option>";
    }
    echo "</select>";
}

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Mapping fields', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <form method="post">
                        <input type="hidden" name="page" value="<?php esc_attr_e(Helper::GET('page')) ?>">
                        <?php echo wp_nonce_field('rsc-mapping', 'rsc-mapping-nonce') ?>

                        <h3><?php _e('Listing fields', 'rex-sync') ?></h3>
                        <table>
                            <tbody>
                            <tr>
                                <th><?php _e('Listing Title', 'rex-sync') ?></th>
                                <td><?php render_fields_dropdown('rsc[listing_fields][title]', $listing_demo_fields, $listing_fields_mapping['title']); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Listing Content', 'rex-sync') ?></th>
                                <td><?php render_fields_dropdown('rsc[listing_fields][content]', $listing_demo_fields, $listing_fields_mapping['content']); ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <h3><?php _e('Custom fields', 'rex-sync') ?></h3>
                        <p><?php _e('Mapping local custom fields to Rex Listing fields. If the field doesn\'t exist, just type the new one.',  'rex-sync') ?></p>
                        <table>
                            <thead>
                            <tr>
                                <th style="width: 40%"><?php _e('Custom field', 'rex-sync') ?></th>
                                <th style="width: 40%"><?php _e('Map to Rex', 'rex-sync') ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($listing_custom_fields_mapping as $custom_field=>$listing_key): ?>
                            <tr>
                                <td><input type="text" name="rsc[custom_fields][wp][]" value="<?php esc_attr_e($custom_field); ?>" class="widefat"></td>
                                <td><?php render_fields_dropdown('rsc[custom_fields][listing][]', $listing_demo_fields, $listing_key); ?></td>
                                <td><button class="button-secondary js-remove-custom-field"><?php _e('Delete', 'rex-sync') ?></button></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td colspan="3">
                                    <button class="button-secondary js-add-custom-field"><?php _e('+ Add New', 'rex-sync') ?></button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <p>&nbsp;</p>
                        <button class="button-primary" type="submit">Save settings</button>
                        <p>&nbsp;</p>
                    </form>
                </div>
                <div class="col-2"></div>
            </div>

        </div>
    </div>
</div>
<script type="text/template" id="row-template">
<tr>
    <td><input type="text" name="rsc[custom_fields][wp][]" value="" class="widefat"></td>
    <td><?php render_fields_dropdown('rsc[custom_fields][listing][]', $listing_demo_fields, ''); ?></td>
    <td><button class="button-secondary js-remove-custom-field"><?php _e('Delete', 'rex-sync') ?></button></td>
</tr>
</script>
<script>
    (function($){
        var $body = $('body');

        if($.fn.select2){
            $('.dropdown-rex-field').select2({
                tags: true,
                allowClear: true,
                placeholder: 'Please select'
            });
        }

        $body.on('click', '.js-remove-custom-field', function(e){
            e.preventDefault();

            let cfm = confirm('Are you sure to remove this field?');
            if(!cfm)
                return false;

            var $tr = $(this).closest('tr');
            $tr.fadeOut(function(){
                $tr.remove();
            });
        });

        $body.on('click', '.js-add-custom-field', function(e){
            e.preventDefault();

            var row_template_html = $('#row-template').html();
            var $tr = $(this).closest('tr');
            var $row = $(row_template_html);
            $tr.before($row);
            $row.find('.dropdown-rex-field').select2({
                tags: true,
                allowClear: true,
                placeholder: 'Please select'
            });
        });
    })(jQuery);
</script>