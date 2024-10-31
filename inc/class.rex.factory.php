<?php
namespace Rex\API;

require_once 'rex/class.rex.publishedlistings.php';
require_once 'rex/class.rex.systemvalues.php';
require_once 'rex/class.rex.cache.php';

class RexFactory{
    public static function create($class_name, $user_name = '', $password = '', $region=''){

        $class = "Rex\API\Rex_{$class_name}";
        $object = new $class($user_name, $password, $region);
        
        return $object;
    }
}

class RexAPI{
    static $instances = array();
    static $cache_object = false;

    private static function get_cache_object(){

        self::$cache_object = new Rex_Cache();

        return self::$cache_object;
    }

    private static function get_instance($class_name){

        $settings = \Rex\Sync\Loader::get_settings();
        $user_name = $settings['user_login'];
        $password = $settings['user_password'];
        $region = $settings['region'];

        if( !isset(self::$instances[$class_name])){
            self::$instances[$class_name] = RexFactory::create($class_name, $user_name, $password, $region);
        }

        return self::$instances[$class_name];
    }

    static function test_credentials($user_name, $password, $region=''){
        $class_name = 'SystemValues';
        $instance = RexFactory::create($class_name, $user_name, $password, $region);

        return $instance->get_token();
    }

    static function get_listing($id){

        $hash_key = base64_encode(json_encode(array($id)));
        $cached = self::get_cache_object()->get_cache($hash_key);
        if($cached){
            return $cached;
        }

        $criteria = [
            [
                'name' => 'id',
                'value' => $id
            ]
        ];

        $instance = self::get_instance('PublishedListings');
        $list = $instance->search($criteria);

        if( $list ){
            $row = current($list->result->rows);
            self::get_cache_object()->set_cache($hash_key, $row);
            return $row;
        }

        return false;
    }

    static function listings_search($args=array()){

        $args = wp_parse_args($args, [
            'type' => [],
            'listing' => [],
            'page-size' => 50,
            'page' => 1
        ]);

        $hash_key = base64_encode(json_encode($args));
        $cached = self::get_cache_object()->get_cache($hash_key);
        if($cached){
            return $cached;
        }

        $criteria = array();

        $type = $args['type'];
        $type = is_array($type)?$type:array($type);


        $listing_categories = array();
        if(in_array('rental', $type)){
            $listing_categories += array('residential_rental','commercial_rental','holiday_rental',);
        }

        if(in_array('sale', $type)){
            $listing_categories += array('residential_sale','land_sale','business_sale', 'commercial_sale','rural_sale');
        }

        if($args['listing']){
            $listing_categories += is_array($args['listing'])?$args['listing']:array($args['listing']);
        }

        $listing_categories = array_unique($listing_categories);
        if(count($listing_categories)){
            $criteria[] = array(
                'name' => 'listing.listing_category_id',
                'type' => 'in',
                'value' => $listing_categories
            );
        }

        $listing_states = array();
        if(in_array('sold', $type)){
            $listing_states += array('sold');
        }

        if(in_array('leased', $type)){
            $listing_states[] = 'leased';
        }

        if(in_array('current', $type)){
            $listing_states[] = 'current';
        }

        if(empty($order_by)){
            $order_by['state_date'] = 'DESC';
        }

        $page_size = intval($args['page-size']);
        $page = intval($args['page']);
        $offset = $page_size * ($page-1);

        $instance = self::get_instance('PublishedListings');
        $list = $instance->search($criteria, $order_by, $offset, $page_size, false, 'active');

        $list_result = $list->result;

        $result = array(
            'rows' => $list_result->rows,
            'total' => $list_result->total
        );

        self::get_cache_object()->set_cache($hash_key, $result);

        return $result;
    }


}