<?php
namespace Rex\API;

use Rex\Sync\Logger;

require_once 'class.rex.cache.php';

class Rex{
    protected $host = '';
    protected $user_name = '';
    protected $password = '';
    private static $token = '';
    protected $is_decoded = true;
    protected $cache_object = null;
    protected $is_cached = true;
    protected $last_response = false;

    public function __construct($user_name, $password, $region = ''){
        $this->user_name = $user_name;
        $this->password = $password;

        if($region == 'uk'){
            $this->host = 'https://api.uk.rexsoftware.com/v1/rex';
        }else{
            $this->host = 'https://api.rexsoftware.com/v1/rex';
        }
    }

    public function get_token(){

        if(self::$token)
            return self::$token;

        $args = array(
            'method'=>'Authentication::login',
            'args' => array(
                'email'=>$this->user_name,
                'password'=>$this->password
            ),
            'token'=>''
        );


        $results = $this->request($args);

        if($results) {
            $result_object = $this->decode($results);
            self::$token = $result_object->result;
        }

        if(!self::$token)
            throw new \Exception('Cannot get token from Rex');

        return self::$token;
    }

    protected function request($args=array()){

        $remote_args = [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-App-Identifier' => 'Integration:RexSyncPlugin'
            ]
        ];
        $remote_url = $this->host."/".$args['method'];
        $remote_args['body'] = json_encode($args['args']);
        if(isset($args['token']) && $args['token']){
            $remote_args['headers']['Authorization'] = 'Bearer '.$args['token'];
        }

        $remote_post = wp_remote_post( $remote_url, $remote_args );
        $http_code = wp_remote_retrieve_response_code($remote_post);
        $response_body = wp_remote_retrieve_body($remote_post);

        if($http_code != 200) {
            $decode = $this->decode($response_body);
            if(isset($decode->error->message)) {
                Logger::info('API ERROR: ' . $decode->error->message);
            }else{
                Logger::info('API ERROR: ' . $response_body);
            }
            return false;
        }

        $this->last_response = $response_body;
        return $this->last_response;
    }

    protected function read($id, $fields = false, $extra_fields = false){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'id' => $id,
        );

        if($extra_fields){
            $args['extra_fields'] = $extra_fields;
        }

        if($fields){
            $args['fields'] = $fields;
        }

        $request_args = array(
            'method' => $method,
            'token' => $token,
            'args' => $args
        );

        $results = $this->request($request_args);
        $results = $this->decode($results);

        return $results;
    }

    public function search($criteria=array(), $order_by=false, $offset=0, $limit=50, $create_viewstate=false, $search_state='active', $result_format='default', $extra_options = false){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array();
        if($criteria){
            $args['criteria'] = $criteria;
        }

        if($order_by){
            $args['order_by'] = $order_by;
        }

        $extra_options = wp_parse_args($extra_options, [
            'extra_fields' => [
                "events",
                "images",
                "advert_internet"
            ]
        ]);

        $args['extra_options'] = $extra_options;

        $args['offset'] = $offset;
        $args['limit'] = $limit;
        $args['create_viewstate'] = $create_viewstate;
        $args['search_state'] = $search_state;
        $args['result_format'] = $result_format;


        $request_args = array(
            'method' => $method,
            'token' => $token,
            'args' => $args
        );

        $results = $this->request($request_args);
        $results = $this->decode($results);

        return $results;
    }

    protected function set_cache_object(Rex_Cache $object){
        $this->cache_object = $object;
    }

    protected function get_request_method($class_name, $method){
        return "{$class_name}::{$method}";
    }

    protected function decode($result){
        if($this->is_decoded && !is_object($result)){
            return json_decode($result);
        }

        return $result;
    }

    protected function set_cache($key, $value){
        if( $this->is_cached && $this->cache_object){
            $this->cache_object->set_cache($key, $value);
        }
    }

    protected function get_cache($key){
        if( $this->is_cached && $this->cache_object ){
            return $this->cache_object->get_cache($key);
        }

        return false;
    }



}