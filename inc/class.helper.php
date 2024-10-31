<?php

namespace Rex\Sync;

class Helper{


    static function GET($name, $default = NULL)
    {
        return isset($_GET[$name]) ? self::recursive_sanitize_text_field($_GET[$name]) : $default;
    }

    static function POST($name, $default = NULL)
    {
        return isset($_POST[$name]) ? self::recursive_sanitize_text_field($_POST[$name]) : $default;
    }

    static function REQUEST($name, $default = NULL)
    {
        return isset($_REQUEST[$name]) ? self::recursive_sanitize_text_field($_REQUEST[$name]) : $default;
    }


    static function recursive_sanitize_text_field($array) {
        if ( is_array( $array ) ) {
            foreach ( $array as $key => &$value ) {
                $value = self::recursive_sanitize_text_field($value);
            }
        }
        else {
            $array = sanitize_text_field( $array );
        }

        return $array;
    }

    static function display_errors(\WP_Error $error){
        $messages = $error->get_error_messages();
        if(!$messages)
            return;
        ?>
        <div class="error">
            <?php foreach($messages as $m): ?>
            <p><?php esc_html_e($m) ?></p>
            <?php endforeach; ?>
        </div>
        <?php
    }

    static function display_messages(\WP_Error $error){
        $messages = $error->get_error_messages();
        if(!$messages)
            return;
        ?>
        <div class="updated">
            <?php foreach($messages as $m): ?>
                <p><?php esc_html_e($m) ?></p>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Flat object or multi-dimensions array into one array (key,value)
     * @param $array array or object
     * @param string $prefix
     * @return array
     */
    static function squash($array, $prefix = '')
    {
        $flat = array();
        $sep = ".";

        if(is_object($array)) {
            $array = json_decode(json_encode($array), ARRAY_A);
        }

        if (!is_array($array))
            $array = (array)$array;

        foreach($array as $key => $value)
        {
            $_key = ltrim($prefix.$sep.$key, ".");

            if (is_array($value) || is_object($value))
            {
                // Iterate this one too
                $flat[$_key] = $value;
                $flat = array_merge($flat, self::squash($value, $_key));
            }
            else
            {
                $flat[$_key] = $value;
            }
        }

        return $flat;
    }

    static function to_url($url, $protocol = 'https'){
        if(str_starts_with($url, '//')){
            $url = $protocol.':'.$url;
        }elseif(!str_starts_with($url, 'http')){
            $url = $protocol.'://'.$url;
        }

        return $url;
    }
}