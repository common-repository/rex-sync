<?php
namespace Rex\Sync;

class Queue{
    const TYPE_MANUAL = 'manual';
    const TYPE_AUTO = 'auto';

    const STATUS_PENDING = 'pending';
    const STATUS_CANCEL = 'cancel';
    const STATUS_FAIL = 'fail';
    const STATUS_DONE = 'done';

    static $table_name = 'rsc_queues';

    static function insert($listing_id, $jsonstring, $type, $status = Queue::STATUS_PENDING){
        global $wpdb;

        $wpdb->insert($wpdb->prefix.self::$table_name, [
            'listing_id' => $listing_id,
            'jsonstring' => $jsonstring,
            'type' => $type,
            'status' => $status
        ]);

        return $wpdb->insert_id;
    }

    static function update($row_id, $data){
        global $wpdb;


        $wpdb->update($wpdb->prefix.self::$table_name, $data, ['id' => $row_id]);
    }

    static function get($id){
        global $wpdb;

        $table_name = $wpdb->prefix.self::$table_name;

        $sql = $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $id );

        return $wpdb->get_row($sql, ARRAY_A);
    }

    static function get_paging($page = 1, $page_size = 50, $status = false, $order = 'desc', $search_text = ''){
        global $wpdb;

        $table_name = $wpdb->prefix.self::$table_name;

        $offset = $page_size * ($page - 1);

        $sql = "SELECT * FROM {$table_name} WHERE 1 = 1 ";
        $count_sql = "SELECT count(1) FROM {$table_name} WHERE 1 = 1 ";
        if($status){
            $sql .=  $wpdb->prepare(" AND `status` = %s", $status);
            $count_sql .=  $wpdb->prepare(" AND `status` = %s", $status);
        }

        if($search_text){
            $sql .= $wpdb->prepare(" AND (listing_id = %s", $search_text) . " or jsonstring like '%".$wpdb->esc_like($search_text)."%' )";
            $count_sql .= $wpdb->prepare(" AND (listing_id = %s", $search_text) . " or jsonstring like '%".$wpdb->esc_like($search_text)."%' )";
        }

        $order = $order == 'desc' ? 'desc' : 'asc';

        $sql .= $wpdb->prepare(" order by id {$order} limit %d,%d", $offset, $page_size );


        $results = $wpdb->get_results($sql, ARRAY_A);
        $total = $wpdb->get_var($count_sql);

        return [
            'rows' => $results,
            'total' => $total
        ];

    }

    static function delete($row_id){
        global $wpdb;

        $table_name = $wpdb->prefix.self::$table_name;

        $wpdb->delete($table_name, ['id' => $row_id]);
    }

    static function cancel_all_pendings($type = Queue::TYPE_MANUAL){
        global $wpdb;

        $table_name = $wpdb->prefix.self::$table_name;

        $wpdb->update($table_name, [
            'status' => self::STATUS_CANCEL
        ], [
            'status' => self::STATUS_PENDING,
            'type' => $type
        ]);
    }

    static function get_last_system_modtime(){
        global $wpdb;

        $table_name = $wpdb->prefix.self::$table_name;

        $sql = "SELECT listing_system_modtime FROM {$table_name} ORDER BY listing_system_modtime DESC";

        $listing_system_modtime = $wpdb->get_var($sql);

        return intval($listing_system_modtime);
    }
}