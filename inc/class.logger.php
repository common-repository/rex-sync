<?php
namespace Rex\Sync;

class Logger{

    private static function get_dir(){
        $path = wp_upload_dir();
        $path = $path['basedir'].DIRECTORY_SEPARATOR.'rsc-logs';
        if(!is_dir($path)){
            wp_mkdir_p($path);
        }

        return $path;
    }

    private static function get_path(){

        $path = self::get_dir();

        $current_time = current_time('timestamp');

        $file_name = 'log-'.date('Y-m-d', $current_time).'.txt';

        return $path.DIRECTORY_SEPARATOR.$file_name;

    }

    static function info($mesage, $more_info = []){

        $current_time = current_time('timestamp');

        $log = "[".date('c', $current_time)."] ".$mesage;
        if( $more_info )
            $log .= ". More info: ".json_encode($more_info);

        $file_path = self::get_path();
        $file_handle = fopen($file_path, 'a+');
        if($file_handle){
            fwrite($file_handle, $log);
            fwrite($file_handle, PHP_EOL);
        }


        fclose($file_handle);
    }

    static function get_files(){
        $path = self::get_dir();
        $files = glob($path.'/log-*.txt');
        $sort = [];
        foreach($files as $f){
            $sort[] = filemtime($f);
        }

        array_multisort($sort, SORT_NUMERIC | SORT_DESC, $files);

        return $files;
    }

    static function get_file_urls(){
        $files = self::get_files();
        $urls = [];

        $path = wp_upload_dir();
        $base_url = $path['baseurl'].'/rsc-logs/';

        foreach($files as $f){
            $name = basename($f);
            $urls[] = $base_url.$name;

        }

        return $urls;

    }

    static function delete_file($file_name){
        $path = wp_upload_dir();
        $path = $path['basedir'].DIRECTORY_SEPARATOR.'rsc-logs';

        unlink($path.DIRECTORY_SEPARATOR.$file_name);
    }
}