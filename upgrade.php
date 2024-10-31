<?php
namespace Rex\Sync;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Upgrade{
    private static $db_updates = array(
        '2.2.0' => array(
            'update_220',
            'update_db_version',
        ),
    );

    static function load(){
        add_action('init', [__CLASS__, 'do_db_update']);
    }

    static function do_db_update(){
        $db_version = self::get_db_version();
        $is_changed = false;

        foreach(self::$db_updates as $version=>$update_callbacks){
            if(version_compare($db_version, $version, '<')){
                foreach($update_callbacks as $callback){
                    call_user_func([__CLASS__, $callback]);
                }

                $is_changed = true;
            }
        }

        if($is_changed)
            self::update_db_version();
    }

    static function is_new_install(){
        $saved_settings = get_option(Loader::$option_settings_key);

        return empty($saved_settings);
    }

    static function get_db_version(){
        return get_option('rex_sync_lite_version');
    }

    static function update_db_version($version = false){
        update_option( 'rex_sync_lite_version', $version ? $version : Loader::get_plugin_version() );
    }

    static function update_220(){
        if(self::is_new_install())
            return;

        $settings = Loader::get_settings(true);

        $key_remap = [
            '_rsc.property.attr_bedrooms' => '_rsc.attributes.bedrooms',
            '_rsc.property.attr_bathrooms' => '_rsc.attributes.bathrooms',
            '_rsc.property.attr_toilets' => '_rsc.attributes.toilets',
            '_rsc.property.attr_garages' => '_rsc.attributes.garages',
            '_rsc.property.attr_buildarea' => '_rsc.attributes.buildarea',
            '_rsc.property.attr_buildarea_m2' => '_rsc.attributes.buildarea_m2',
            '_rsc.property.attr_landarea' => '_rsc.attributes.landarea',
            '_rsc.property.attr_landarea_m2' => '_rsc.attributes.landarea_m2',
            '_rsc.authority_type.text' => '_rsc.authority_type',
            '_rsc.listing_agent_1.id' => '_rsc.listing_agent_1.id',
            '_rsc.listing_agent_2.id' => '_rsc.listing_agent_2.id',
            '_rsc.property.adr_unit_number' => '_rsc.address.unit_number',
            '_rsc.property.adr_street_number' => '_rsc.address.street_number',
            '_rsc.property.adr_street_name' => '_rsc.address.street_name',
            '_rsc.property.adr_suburb_or_town' => '_rsc.address.suburb_or_town',
            '_rsc.property.adr_locality' => '_rsc.address.locality',
            '_rsc.property.adr_state_or_region' => '_rsc.address.state_or_region',
            '_rsc.property.adr_postcode' => '_rsc.address.postcode',
            '_rsc.property.adr_country' => '_rsc.address.country',
            '_rsc.property.system_search_key' => '_rsc.address.formats.full_address',
            '_rsc.related.listing_images' => '_rsc.images',
            '_rsc.related.listing_events' => '_rsc.events',

            '_rsc.related.listing_adverts.0.advert_heading' => '_rsc.advert_internet.heading',
            '_rsc.related.listing_adverts.0.advert_body' => '_rsc.advert_internet.body',
        ];

        foreach($settings['listing_fields_mapping'] as $custom_key=>$map_key){
            if(array_key_exists($map_key, $key_remap)){
                $settings['listing_fields_mapping'][$custom_key] = $key_remap[$map_key];
            }
        }

        foreach($settings['listing_custom_fields_mapping'] as $custom_key=>$map_key){
            if(array_key_exists($map_key, $key_remap)){
                $settings['listing_custom_fields_mapping'][$custom_key] = $key_remap[$map_key];
            }
        }

        update_option(Loader::$option_settings_key, $settings);
    }

}

Upgrade::load();