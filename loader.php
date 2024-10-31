<?php
namespace Rex\Sync;

use Rex\API\RexAPI;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Loader{

    static $page_settings_slug = 'rex-sync-settings';
    static $page_manual_sync_slug = 'rex-sync-manual';
    static $page_queues_slug = 'rex-sync-queues';
    static $page_mapping_slug = 'rex-sync-mapping';
    static $page_logs_slug = 'rex-sync-logs';
    static $option_settings_key = 'rex-sync-settings';
    static $custom_field_prefix = '_rsc';

    /**
     * @var \WP_Error
     */
    static $errors;
    /**
     * @var \WP_Error
     */
    static $messages;

    public static function load()
    {
        
        require_once __DIR__.'/inc/class.rex.factory.php';
        require_once __DIR__.'/inc/class.queue.php';
        require_once __DIR__.'/inc/class.logger.php';
        require_once __DIR__.'/inc/class.helper.php';

        self::$errors = new \WP_Error();
        self::$messages = new \WP_Error();

        add_filter('plugin_action_links_'.basename(__DIR__).DIRECTORY_SEPARATOR. basename(__DIR__).'.php', [__CLASS__, 'add_plugin_action_buttons'], 10);

        add_action('admin_init', [__CLASS__, 'admin_init']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'register_admin_scripts']);
        add_action('admin_menu', [__CLASS__, 'register_pages'], 20);
        add_action('init', [__CLASS__, 'register_post_types'], 0);
        add_action('init', [__CLASS__, 'handle_webhook']);
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes'] );


        add_action('wp_ajax_rsc_download_listings', [__CLASS__, 'ajax_rsc_download_listings']);
        add_action('wp_ajax_rsc_validate_account', [__CLASS__, 'ajax_rsc_validate_account']);

        add_action('wp_ajax_rsc_delete_log', [__CLASS__, 'ajax_delete_log']);

    }

    public static function register_post_types(){
        self::register_post_type_listing();
        self::register_post_type_listing_agent();
        self::register_taxonomy_listing_category();
        self::register_taxonomy_listing_state();
    }

    public static function admin_init(){
        self::save_settings();
        self::save_mapping_settings();
        self::delete_queues();

//        self::test_insert_listing();
    }

    public static function add_plugin_action_buttons($actions){

        $actions = array_merge([
            self::$page_settings_slug => '<a href="'.admin_url('admin.php?page='.self::$page_settings_slug).'" aria-label="Settings">Settings</a>'
        ], $actions);

        return $actions;
    }

    public static function save_settings(){
        $settings = self::get_settings();
        if(wp_verify_nonce(Helper::POST('rsc-settings-nonce'), 'rsc-settings')){
            $post_settings = Helper::POST('rsc');
            $settings = wp_parse_args($post_settings, $settings);

            if(empty($post_settings['download_featured_image']))
                $settings['download_featured_image'] = "";

            if(empty($post_settings['image_sizes']))
                $settings['image_sizes'] = [];

            update_option(self::$option_settings_key, $settings);

            self::$messages->add('summary', 'Settings have been saved successfully');
        }

    }

    private static function get_default_settings(){
        return [
            'region' => 'others',
            'user_login' => '',
            'user_password' => '',
            'download_featured_image' => '',
            'image_sizes' => ['large','medium'],
            'listing_fields_mapping' => [
                'title' => '_rsc.advert_internet.heading',
                'content' => '_rsc.advert_internet.body',
            ],
            'listing_custom_fields_mapping' => [
                '_rsc.price_match' => '_rsc.price_match',
                '_rsc.price_advertise_as' => '_rsc.price_advertise_as',
                '_rsc.attributes.bedrooms' => '_rsc.attributes.bedrooms',
                '_rsc.attributes.bathrooms' => '_rsc.attributes.bathrooms',
                '_rsc.attributes.toilets' => '_rsc.attributes.toilets',
                '_rsc.attributes.garages' => '_rsc.attributes.garages',
                '_rsc.attributes.buildarea' => '_rsc.attributes.buildarea',
                '_rsc.attributes.buildarea_m2' => '_rsc.attributes.buildarea_m2',
                '_rsc.attributes.landarea' => '_rsc.attributes.landarea',
                '_rsc.attributes.landarea_m2' => '_rsc.attributes.landarea_m2',
                '_rsc.authority_type.text' => '_rsc.authority_type',
                '_rsc.listing_agent_1.id' => '_rsc.listing_agent_1.id',
                '_rsc.listing_agent_2.id' => '_rsc.listing_agent_2.id',
                '_rsc.address.unit_number' => '_rsc.address.unit_number',
                '_rsc.address.street_number' => '_rsc.address.street_number',
                '_rsc.address.street_name' => '_rsc.address.street_name',
                '_rsc.address.suburb_or_town' => '_rsc.address.suburb_or_town',
                '_rsc.address.locality' => '_rsc.address.locality',
                '_rsc.address.state_or_region' => '_rsc.address.state_or_region',
                '_rsc.address.postcode' => '_rsc.address.postcode',
                '_rsc.address.country' => '_rsc.address.country',
                '_rsc.address.formats.full_address' => '_rsc.address.formats.full_address',
                '_rsc.images' => '_rsc.images',
                '_rsc.events' => '_rsc.events',
            ],
            'agent_fields_mapping' => [
                'title' => '_rsc.name'
            ],
            'agent_custom_fields_mapping' => [
                '_rsc.first_name' => '_rsc.first_name',
                '_rsc.last_name' => '_rsc.last_name',
                '_rsc.email_address' => '_rsc.email_address',
                '_rsc.phone_direct' => '_rsc.phone_direct',
                '_rsc.phone_mobile' => '_rsc.phone_mobile',
                '_rsc.position' => '_rsc.position',
                '_rsc.profile_image' => '_rsc.profile_image',
            ]
        ];
    }

    public static function get_settings(){
        $default = self::get_default_settings();

        $settings = get_option(self::$option_settings_key);
        $settings = wp_parse_args($settings, $default);

        $settings = apply_filters('Rex/Sync/Loader/get_settings', $settings);

        return $settings;
    }

    public static function save_mapping_settings(){
        $settings = self::get_settings();
        if(wp_verify_nonce(Helper::POST('rsc-mapping-nonce'), 'rsc-mapping')){
            $post_settings = Helper::POST('rsc');

            $settings['listing_fields_mapping'] = $post_settings['listing_fields'];

            $custom_fields = array_combine($post_settings['custom_fields']['wp'], $post_settings['custom_fields']['listing']);
            $settings['listing_custom_fields_mapping'] = $custom_fields;

            update_option(self::$option_settings_key, $settings, $post_settings);

            self::$messages->add('summary', 'Settings have been saved successfully');
        }
    }

    public static function register_pages(){

        add_menu_page(
            __('Sync My Rex', 'rex-sync'),
            __('Sync My Rex', 'rex-sync'),
            'manage_options',
            self::$page_settings_slug
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Sync My Rex Settings', 'rex-sync'),
            __('Settings', 'rex-sync'),
            'manage_options',
            self::$page_settings_slug,
            array(__CLASS__, 'render_settings_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Mapping', 'rex-sync'),
            __('Mapping', 'rex-sync'),
            'manage_options',
            self::$page_mapping_slug,
            array(__CLASS__, 'render_mapging_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Manual Sync', 'rex-sync'),
            __('Manual Sync', 'rex-sync'),
            'manage_options',
            self::$page_manual_sync_slug,
            array(__CLASS__, 'render_manual_sync_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Queues', 'rex-sync'),
            __('Queues', 'rex-sync'),
            'manage_options',
            self::$page_queues_slug,
            array(__CLASS__, 'render_queues_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Logs', 'rex-sync'),
            __('Logs', 'rex-sync'),
            'manage_options',
            self::$page_logs_slug,
            array(__CLASS__, 'render_logs_page')
        );

    }

    public static function register_meta_boxes(){
        add_meta_box( 'meta-box-rex-listing-custom-fields',
            __( 'Listing Custom Fields', 'rex-sync' ),
            [__CLASS__, 'display_meta_box_listing_custom_fields'],
            'listing'
        );

        add_meta_box( 'meta-box-rex-agent-custom-fields',
            __( 'Agent Custom Fields', 'rex-sync' ),
            [__CLASS__, 'display_meta_box_agent_custom_fields'],
            'listing_agent'
        );
    }

    public static function register_admin_scripts(){

        $version = self::get_plugin_version();

        if(Helper::GET('page') == self::$page_mapping_slug){
            wp_enqueue_style('select2', self::get_plugin_dir_url().'external/select2/css/select2.min.css');
            wp_enqueue_script('select2', self::get_plugin_dir_url().'external/select2/js/select2.full.min.js', ['jquery'], false, false);
        }

        wp_enqueue_style('rsc_style', self::get_plugin_dir_url().'css/admin.min.css', false, $version);
        wp_enqueue_script('rsc_script', self::get_plugin_dir_url().'js/admin.js', ['jquery'], $version, true);

    }

    public static function render_settings_page(){
        include __DIR__.'/templates/admin.php';
    }

    public static function render_mapging_page(){
        include __DIR__.'/templates/admin-mapping.php';
    }

    public static function render_manual_sync_page(){
        include __DIR__.'/templates/admin-manual-sync.php';
    }

    public static function render_queues_page(){
        include __DIR__.'/templates/admin-queues.php';
    }

    public static function render_logs_page(){
        include __DIR__.'/templates/admin-logs.php';
    }

    public static function display_meta_box_listing_custom_fields($post){
        include __DIR__.'/templates/meta-box-listing-custom-fields.php';
    }

    public static function display_meta_box_agent_custom_fields($post){
        include __DIR__.'/templates/meta-box-agent-custom-fields.php';
    }

    public static function get_plugin_dir_url(){
        return plugin_dir_url(__FILE__);
    }

    public static function get_plugin_version(){
        if( ! function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $meta = get_plugin_data(__DIR__. DIRECTORY_SEPARATOR. 'rex-sync.php');
        return $meta['Version'];
    }

    public static function ajax_rsc_validate_account(){
        if(!current_user_can('manage_options'))
            return;

        set_time_limit(0);

        $user_login = Helper::POST('user_login');
        $user_password = Helper::POST('user_password');
        $region = Helper::POST('region');
        if(!$user_login || !$user_password){
            wp_send_json_error('User login and password are required');
            exit;
        }

        try {
            $token = RexAPI::test_credentials($user_login, $user_password, $region);
            if($token){
                wp_send_json_success(1);
            }
        }catch (\Exception $exception){
            wp_send_json_error($exception->getMessage());
            exit;
        }

        wp_send_json_error(0);
        exit;
    }

    public static function ajax_rsc_download_listings(){
        if(!current_user_can('manage_options'))
            return;

        set_time_limit(0);

        $row_ids = [];
        $page = Helper::POST('pidx', 1);
        $page_size = 20;
        $report_inserted = 0;
        $report_failed = 0;
        $downloaded_post_ids = [];

        $listings_args = apply_filters('Rex/Sync/download_listings_args', [
            'type' => ['current', 'sold', 'leased'],
            'page-size' => $page_size,
            'page' => $page
        ]);

        try {
            $listings = RexAPI::listings_search($listings_args);
        }catch(\Exception $exception){
            Logger::info($exception->getMessage());
            wp_send_json_error();
            exit;
        }

        if($listings && $listings['rows']){
            foreach($listings['rows'] as $listing){
                $listing_id = $listing->id;
                $jsonstring = json_encode($listing);
                $row_ids[] = Queue::insert($listing_id, $jsonstring, Queue::TYPE_MANUAL);
            }

            foreach($row_ids as $queue_id){
                $listing_post_id = self::insert_listing_from_queue($queue_id);
                if($listing_post_id){
                    $report_inserted ++;
                    $downloaded_post_ids[] = $listing_post_id;
                }else{
                    $report_failed ++;
                }

                sleep(1);
            }

            if(!$downloaded_post_ids){
                Logger::info('No listings are downloaded, process stopped');
                wp_send_json_error();
                exit;
            }
        }

        do_action('Rex/Sync/download_listings', $downloaded_post_ids);

        wp_send_json([
            'data' => $row_ids,
            'report' => [
                'inserted' => $report_inserted,
                'failed' => $report_failed,
            ],
            'total' => $listings ? $listings['total'] : 0
        ]);

        exit;

    }

    /*
    public static function test_insert_listing(){
        if(Helper::GET('test-queue')){
            Queue::update(Helper::GET('test-queue'), ['status' => 'pending']);
            self::insert_listing_from_queue(Helper::GET('test-queue'));
            exit;
        }

    }
*/
    public static function get_webhook_url(){
        return add_query_arg('rschook', 'rex', home_url());
    }

    public static function handle_webhook(){
        if(Helper::GET('rschook') != 'rex')
            return;

        $body_post = file_get_contents('php://input');
        if(empty($body_post))
            return;

        $json_object = json_decode($body_post, ARRAY_A);
        if(!$json_object || !isset($json_object['data']))
            return;

        foreach($json_object['data'] as $item){
            if($item['type'] == 'listings.updated' || $item['type'] == 'listings.created'){
                $listing_id = $item['payload']['context']['record_id'];
                if(!$listing_id)
                    continue;

                $queue_id = Queue::insert($listing_id, "", Queue::TYPE_AUTO);
                if($queue_id)
                    self::insert_listing_from_queue($queue_id);
            }
        }

        exit;
    }

    public static function register_post_type_listing() {

        /**
         * Post Type: Listings.
         */

        $labels = [
            "name" => __( "Listings", "rsc" ),
            "singular_name" => __( "Listing", "rsc" ),
        ];

        $args = [
            "label" => __( "Listings", "rsc" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => [ "slug" => "listing", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title", "editor", "thumbnail", "author" ],
            "show_in_graphql" => false,
        ];

        register_post_type( "listing", $args );
    }

    public static function register_post_type_listing_agent() {

        /**
         * Post Type: Listing Agents.
         */

        $labels = [
            "name" => __( "Listing Agents", "rsc" ),
            "singular_name" => __( "Listing Agent", "rsc" ),
        ];

        $args = [
            "label" => __( "Listing Agents", "rsc" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => [ "slug" => "our-people", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title", "editor", "thumbnail" ],
            "show_in_graphql" => false,
        ];

        register_post_type( "listing_agent", $args );
    }

    public static function register_taxonomy_listing_category() {

        /**
         * Taxonomy: Listing Categories.
         */

        $labels = [
            "name" => __( "Listing Categories", "rsc" ),
            "singular_name" => __( "Listing Category", "rsc" ),
        ];


        $args = [
            "label" => __( "Listing Categories", "rsc" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => true,
            "hierarchical" => true,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "query_var" => true,
            "rewrite" => [ 'slug' => 'listing_category', 'with_front' => true, ],
            "show_admin_column" => true,
            "show_in_rest" => true,
            "show_tagcloud" => false,
            "rest_base" => "listing_category",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
            "show_in_graphql" => false,
        ];
        register_taxonomy( "listing_category", [ "listing" ], $args );
    }

    public static function register_taxonomy_listing_state() {

        /**
         * Taxonomy: Listing States.
         */

        $labels = [
            "name" => __( "Listing States", "rsc" ),
            "singular_name" => __( "Listing State", "rsc" ),
        ];


        $args = [
            "label" => __( "Listing States", "rsc" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => true,
            "hierarchical" => false,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "query_var" => true,
            "rewrite" => [ 'slug' => 'listing_state', 'with_front' => true, ],
            "show_admin_column" => true,
            "show_in_rest" => true,
            "show_tagcloud" => false,
            "rest_base" => "listing_state",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
            "show_in_graphql" => false,
        ];
        register_taxonomy( "listing_state", [ "listing" ], $args );
    }

    public static function ajax_delete_log(){
        if(!current_user_can('manage_options'))
            return;

        $file_name = Helper::POST('file');
        Logger::delete_file($file_name);

        echo 1;
        exit;
    }

    public static function intermediate_image_sizes_advanced($sizes){
        $settings = self::get_settings();

        if(is_array($settings['image_sizes'])){
            $default_sizes = wp_get_registered_image_subsizes();
            $sizes = [];
            foreach($default_sizes as $size_name=>$size){
                if(in_array($size_name, $settings['image_sizes'])){
                    $sizes[$size_name] = $size;
                }
            }
        }

        return $sizes;
    }

    static function get_supported_templates(){
        return [
            'archive' => __('Archive Listings', 'rex-sync'),
            'single' => __('Single Listing', 'rex-sync'),
            'agent' => __('Single Agent', 'rex-sync'),
        ];
    }

    static function get_supported_regions(){
        return [
            'uk' => __('United Kingdom', 'rex-sync'),
            'others' => __('Others', 'rex-sync'),
        ];
    }

    private static function delete_queues(){
        if(!current_user_can('manage_options'))
            return;

        if(wp_verify_nonce(Helper::POST('rsc-delete-queues-nonce'), 'rsc-delete-queues')){
            $delete_selected = Helper::POST('delete_selected');
            $delete_all = Helper::POST('delete_all');
            $queue_ids = Helper::POST('queue_id');

            if($delete_selected && empty($queue_ids)){
                self::$errors->add('error', 'No selected found');
            }

            if(!self::$errors->get_error_code()){
                $total_deleted = 0;

                if($delete_all){
                    $status = Helper::GET('status', \Rex\Sync\Queue::STATUS_PENDING);
                    $search_text = Helper::GET('s');

                    do{
                        $paging = Queue::get_paging(1, 100, $status, 'desc', $search_text);
                        $rows = $paging['rows'];
                        foreach($rows as $r){
                            Queue::delete($r['id']);
                            $total_deleted ++;
                        }

                        usleep(10);

                    }while($rows);
                }

                if($delete_selected){
                    foreach($queue_ids as $rid){
                        Queue::delete($rid);
                        $total_deleted ++;
                    }
                }

                self::$messages->add('summary', 'Deleted '.$total_deleted.' rows successfully');
            }

        }
    }

    private static function insert_listing_from_queue($queue_id){
        $row_queue = Queue::get($queue_id);
        if(!$row_queue || !$row_queue['listing_id'])
            return false;

        if($row_queue['status'] != Queue::STATUS_PENDING)
            return false;

        $settings = self::get_settings();
        $listing_id = $row_queue['listing_id'];

        try {
            $jsonstring = $row_queue['jsonstring'];
            if(!$jsonstring) {
                $listing = RexAPI::get_listing($listing_id);
            }else{
                $listing = json_decode($jsonstring);
            }
        }catch (\Exception $e){
            Logger::info($e->getMessage());
            Queue::update($queue_id, ['status' => Queue::STATUS_FAIL, 'status_message' => $e->getMessage()]);
            return false;
        }


        if(!$listing){
            Logger::info('Cannot retrieve listing from Rex', compact($queue_id, $listing_id));
            Queue::update($queue_id, ['status' => Queue::STATUS_FAIL, 'status_message' => 'Cannot retrieve listing from Rex']);
            return false;
        }

        $listing = apply_filters('Rex/Sync/insert_listing_data', $listing, $queue_id);
        if(!$listing) {
            Logger::info('Listing has been cancelled by developer', compact($queue_id, $listing_id));
            Queue::update($queue_id, ['status' => Queue::STATUS_CANCEL, 'status_message' => 'Listing has been cancelled by developer']);
            return false;
        }

        Queue::update($queue_id, ['jsonstring' => json_encode($listing), 'listing_system_modtime' => $listing->system_modtime]);

        do_action('Rex/Sync/before_insert_listing_from_queue', $queue_id, $listing);

        $flat_listing = Helper::squash($listing, self::$custom_field_prefix);

        $address = $listing->address->street_number.' '.$listing->address->street_name;

        $post_title = $address;
        $post_content = '';

        if($settings['listing_fields_mapping']['title']
            && isset($flat_listing[$settings['listing_fields_mapping']['title']])
            && $flat_listing[$settings['listing_fields_mapping']['title']]
        ){
            $post_title = $flat_listing[$settings['listing_fields_mapping']['title']];
        }

        if($settings['listing_fields_mapping']['content']
            && isset($flat_listing[$settings['listing_fields_mapping']['content']])
            && $flat_listing[$settings['listing_fields_mapping']['content']]
        ){
            $post_content = $flat_listing[$settings['listing_fields_mapping']['content']];
        }

        $post_args = [
            'post_title' => $post_title,
            'post_name' => sanitize_title($post_title),
            'post_content' => $post_content,
            'post_type' => 'listing',
            'post_date' => date('Y-m-d H:i:s', $listing->system_publication_timestamp)
        ];

        $is_new = true;
        $listing_post = self::find_post_by_listing_id($listing_id);
        if($listing_post){
            $post_args['ID'] = $listing_post->ID;
            $listing_post_id = wp_update_post($post_args);
            $is_new = false;
        }else{
            $post_args['post_status'] = 'draft';
            $listing_post_id = wp_insert_post($post_args);
        }

        if(is_wp_error($listing_post_id)){
            Logger::info('Cannot insert listing post: '.$listing_post_id->get_error_message(), compact($queue_id, $listing_id));
            return false;
        }

        update_post_meta($listing_post_id,'_rsc.id', $listing->id);

        $custom_fields = $settings['listing_custom_fields_mapping'];
        if($custom_fields){
            foreach($custom_fields as $field_key=>$field_map_key){
                if(isset($flat_listing[$field_map_key]))
                    update_post_meta($listing_post_id, $field_key, $flat_listing[$field_map_key]);
            }
        }

        $listing_state = $listing->system_listing_state;
        if($listing_state)
            wp_set_object_terms($listing_post_id, [ucfirst($listing_state)], 'listing_state', false);

        if($listing->listing_category_id){
            $taxonomy = 'listing_category';
            $category_name = $listing->listing_category.' '.$listing->listing_sale_or_rental;
            $category_slug = str_replace('_', '-', $listing->listing_category_id);

            /**
             * Try to find old term to update the new name
             */
            $old_term = term_exists($listing->listing_category, $taxonomy);
            if(!is_array($old_term)){
                $old_term = term_exists($category_slug, $taxonomy);
            }

            if(!is_array($old_term)){
                $term = wp_insert_term($category_name, $taxonomy, ['slug' => $category_slug]);
            }else{
                $term = wp_update_term($old_term['term_id'], $taxonomy, ['slug' => $category_slug, 'name' => $category_name]);
            }

            if(is_array($term)) {
                $term_id = intval($term['term_id']);
                wp_set_object_terms($listing_post_id, [$term_id], $taxonomy, false);
            }
        }

        if($listing->listing_agent_1){
            self::update_listing_agent($listing->listing_agent_1);
        }
        if($listing->listing_agent_2){
            self::update_listing_agent($listing->listing_agent_2);
        }

        $listing_images = isset($listing->images) ? $listing->images : false;
        if($listing_images){
            if($settings['download_featured_image']){
                $image_url =  Helper::to_url($listing_images[0]->url);
                $old_image_id = get_post_thumbnail_id($listing_post_id);
                $image_id = self::download_image($image_url, $listing_post_id, 0);
                if($image_id){
                    set_post_thumbnail($listing_post_id, $image_id);
                    wp_delete_attachment($old_image_id, true);
                }
            }

        }


        if($is_new){
            wp_update_post([
                'ID' => $listing_post_id,
                'post_status' => 'publish',
            ]);
            wp_update_post([
                'ID' => $listing_post_id,
                'post_date' => date('Y-m-d H:i:s', $listing->system_publication_timestamp)
            ]);
        }


        Queue::update($queue_id, ['status' => Queue::STATUS_DONE,'post_id'=>$listing_post_id]);

        do_action('Rex/Sync/after_insert_listing_from_queue', $listing_post_id, $queue_id, $listing);

        return $listing_post_id;
    }

    private static function find_post_by_listing_id($listing_id){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_rsc.id' and meta_value = %d", $listing_id);
        $post_id = $wpdb->get_var($sql);

        $post = false;
        if($post_id){
            $post = get_post($post_id);
        }

        $post = apply_filters('Rex/Sync/find_post_by_listing_id', $post, $listing_id);

        return $post;
    }

    private static function find_agent_post_by_agent_id($agent_id){
        global $wpdb;

        $sql = "SELECT agent_meta.post_id 
        FROM {$wpdb->postmeta} agent_meta
        INNER JOIN {$wpdb->posts} p on (agent_meta.post_id = p.ID and p.post_type = 'listing_agent' and meta_key = '_rsc.agent_id' and meta_value = %d)
        WHERE p.post_status != 'trash'
        ";
        $sql = $wpdb->prepare($sql, $agent_id);
        $post_id = $wpdb->get_var($sql);

        $post = false;
        if($post_id){
            $post = get_post($post_id);
        }

        $post = apply_filters('Rex/Sync/find_agent_post_by_agent_id', $post, $agent_id);

        return $post;
    }

    private static function update_listing_agent($agent_data){

        if(empty($agent_data->id))
            return false;

        $data = apply_filters('Rex/Sync/insert_listing_agent_data', $agent_data);
        if(!$data){
            Logger::info('Agent has been cancelled by developer', $agent_data);
            return false;
        }

        do_action('Rex/Sync/before_insert_listing_agent', $data);

        $is_new = true;
        $settings = self::get_settings();
        $agent_post = self::find_agent_post_by_agent_id($data->id);

        $flat_data = Helper::squash($data, self::$custom_field_prefix);

        $post_title = $data->name;
        if($settings['agent_fields_mapping']['title']
            && isset($flat_data[$settings['agent_fields_mapping']['title']])
            && $flat_data[$settings['agent_fields_mapping']['title']]
        ){
            $post_title = $flat_data[$settings['agent_fields_mapping']['title']];
        }

        if($agent_post){
            $post_args = [
                'ID' => $agent_post->ID,
                'post_title' => $post_title,
            ];
            $agent_post_id = wp_update_post($post_args);
            $is_new = false;
        }else{
            $post_args = [
                'post_title' => $post_title,
                'post_name' => sanitize_title($post_title),
                'post_type' => 'listing_agent',
                'post_status' => 'draft'
            ];

            $agent_post_id = wp_insert_post($post_args);
        }

        update_post_meta($agent_post_id, '_rsc.agent_id', $data->id);

        $agents_custom_fields_mapping = $settings['agent_custom_fields_mapping'];
        if($agents_custom_fields_mapping){
            foreach($agents_custom_fields_mapping as $field_key=>$map_key){
                update_post_meta($agent_post_id, $field_key, $flat_data[$map_key]);
            }
        }

        if($is_new){
            wp_update_post(['ID' => $agent_post_id, 'post_status' => 'publish']);
        }

        do_action('Rex/Sync/after_insert_listing_agent', $agent_post_id, $data);

        return $agent_post_id;
    }

    private static function get_listing_demo(){
        $file_content = file_get_contents(__DIR__.'/data/listing-demo.json');
        return json_decode($file_content);
    }

    public static function get_listing_demo_fields(){
        $listing = self::get_listing_demo();
        $field_keys = Helper::squash($listing, self::$custom_field_prefix);
        $field_keys = apply_filters('Rex/Sync/get_listing_demo_fields', $field_keys);
        return $field_keys;
    }

    private static function download_image($image_url, $post_id = 0, $file_name_suffix = ''){
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attach_id = false;
        $tmpfile = download_url($image_url);

        if($tmpfile && !is_wp_error($tmpfile)){
            $upload_dir = wp_upload_dir();
            $image_name = basename($image_url);
            $image_name_parts = explode('.', $image_name);
            $ext = end($image_name_parts);
            $micro_time = microtime(true);
            $filename = trailingslashit($upload_dir['path'])."rex{$post_id}-".sanitize_title($micro_time).$file_name_suffix.'.'.$ext;
            copy($tmpfile, $filename);
            unlink($tmpfile);

            if(!file_exists($filename)){
                Logger::info('Cannot move temporary image to upload folder', [$filename]);
                return false;
            }

            $filetype = wp_check_filetype( basename( $filename ), null );

            $attachment = array(
                'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type' => $filetype['type'],
                'post_title'     => $image_name_parts[0],
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
            if(is_wp_error($attach_id)){
                Logger::info( "Cannot insert image to media, ". $tmpfile->get_error_message());
            }else{
                add_filter('intermediate_image_sizes_advanced', [__CLASS__, 'intermediate_image_sizes_advanced'], 10, 1);
                $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                remove_filter('intermediate_image_sizes_advanced', [__CLASS__, 'intermediate_image_sizes_advanced'], 10);
            }
        }else{
            if(!$tmpfile) {
                Logger::info("Cannot download image, " . $tmpfile->get_error_message());
            }else{
                Logger::info("Cannot download image");
            }
        }

        return $attach_id;
    }


}