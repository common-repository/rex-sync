<?php
$settings = \Rex\Sync\Loader::get_settings();
$listing_custom_fields_mapping = $settings['listing_custom_fields_mapping'];

function render_value($field_key, $value){
    echo "<li>";
    echo "<strong>".esc_html($field_key).": </strong>";
    if(is_array($value)){
        if($value){
            echo "<a href='javascript:' class='js-toggle-sub-ul'>+</a>";
            echo "<ul>";
            foreach($value as $sub_key=>$sub_value){
                render_value($sub_key, $sub_value);
            }
            echo "</ul>";
        }

    }else{
        echo esc_html($value);
    }
    echo "</li>";
}


if($listing_custom_fields_mapping) {
    echo "<ul>";
    render_value('_rsc.id', $post->{'_rsc.id'});
    foreach ($listing_custom_fields_mapping as $field_key => $map_key) {
        $value = $post->$field_key;

        render_value($field_key, $value);
    }
    echo "</ul>";
}

?>
<script>
    (function($){
        var $body = $('body');
        $body.on('click', '.js-toggle-sub-ul', function(e){
            e.preventDefault();
            $(this).parent().find('>ul').toggle();
        });
    })(jQuery);
</script>
