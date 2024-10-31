<?php
use Rex\Sync\Loader;
use Rex\Sync\Helper;

$settings = Loader::get_settings();

$page = Helper::GET('pidx', 1);
$page_size = 20;
$status = Helper::GET('status', \Rex\Sync\Queue::STATUS_PENDING);
$search_text = Helper::GET('s');

$paging = \Rex\Sync\Queue::get_paging($page, $page_size, $status, 'desc', $search_text);

$rows = $paging['rows'];
$total = $paging['total'];
$total_pages = ceil($total/$page_size);

$list_statuses = [
    '' => __('All', 'rex-sync'),
    \Rex\Sync\Queue::STATUS_PENDING => __('Pending', 'rex-sync'),
    \Rex\Sync\Queue::STATUS_CANCEL => __('Cancel', 'rex-sync'),
    \Rex\Sync\Queue::STATUS_FAIL => __('Fail', 'rex-sync'),
    \Rex\Sync\Queue::STATUS_DONE => __('Done', 'rex-sync')
];

add_thickbox();;

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Queues', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-queues">
            <p>&nbsp;</p>
            <div class="row" style="max-width: 500px;">
                <form method="get">
                    <input type="hidden" name="page" value="<?php esc_attr_e(Helper::GET('page')) ?>">
                    <table>
                        <tr>
                            <th><?php _e('View status', 'rex-sync') ?></th>
                            <td>
                                <select name="status">
                                    <?php foreach($list_statuses as $key=>$val): ?>
                                    <option value="<?php esc_attr_e($key) ?>" <?php selected($key, $status) ?>><?php esc_html_e($val) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><?php  _e('Search text', 'rex-sync') ?></th>
                            <td>
                                <input type="text" class="widefat" name="s" value="<?php esc_attr_e(stripslashes($search_text)) ?>">
                            </td>
                        </tr>
                        <tr>
                            <th></th>
                            <td>
                                <button type="submit"><?php _e('Search', 'rex-sync') ?></button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <p>&nbsp;</p>
            <div class="row">
                <form method="post">
                    <?php
                    if($total)
                        wp_nonce_field('rsc-delete-queues', 'rsc-delete-queues-nonce')
                    ?>
                    <table>
                        <thead>
                        <tr>
                            <th colspan="3">
                                <strong><?php _e('Total rows: ', 'rex-sync') ?>: </strong><?php esc_html_e($total) ?>
                            </th>
                            <th align="right" colspan="6">
                                <?php if($total): ?>
                                <button name="delete_selected" value="selected" class="button-secondary js-delete-selected"><?php _e('Delete Selected', 'rex-sync') ?></button>
                                <button name="delete_all" value="all" class="button-secondary js-delete-all"><?php _e('Delete All Queues', 'rex-sync') ?></button>
                                <?php endif; ?>
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 2%"></th>
                            <th style="width: 10%"><?php _e('Listing ID', 'rex-sync') ?></th>
                            <th><?php _e('JSON String', 'rex-sync') ?></th>
                            <th style="width: 8%"><?php _e('Type', 'rex-sync') ?></th>
                            <th style="width: 10%"><?php _e('Status', 'rex-sync') ?></th>
                            <th style="width: 12%"><?php _e('Date', 'rex-sync') ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($rows as $r): ?>
                            <tr>
                                <td><input type="checkbox" name="queue_id[]" value="<?php esc_attr_e($r['id']) ?>"></td>
                                <td><?php esc_html_e($r['listing_id']) ?></td>
                                <td>
                                    <?php if($r['status'] == \Rex\Sync\Queue::STATUS_FAIL): ?>
                                        <?php echo esc_html($r['status_message']) ?>
                                    <?php elseif($r['jsonstring']): ?>
                                        <a href="#TB_inline?&width=600&height=550&inlineId=view-json-<?php esc_attr_e($r['id']) ?>" class="thickbox">View JSON</a>
                                        <div id="view-json-<?php esc_attr_e($r['id']) ?>" style="display:none;">
                                            <div>
                                                <?php if($r['post_id']): ?>
                                                    <p><strong>Post ID:</strong> <a href="<?php esc_attr_e(get_permalink($r['post_id'])) ?>" target="_blank"><?php esc_attr_e($r['post_id']) ?></a></p>
                                                <?php endif ?>
                                                <?php if($r['status_message']): ?>
                                                    <p><strong>Message:</strong> <?php esc_html_e($r['status_message']) ?></p>
                                                <?php endif ?>
                                                <p><?php esc_html_e($r['jsonstring']) ?></p>

                                            </div>
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td><?php esc_html_e( $r['type'] ) ?></td>
                                <td>
                                    <?php esc_html_e($list_statuses[$r['status']]) ?>
                                </td>
                                <td><?php esc_html_e($r['date_created']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>

                <div class="pagination">
                    <?php
                    echo paginate_links([
                        'base' => add_query_arg('pidx', false).'%_%',
                        'format' => '&pidx=%#%',
                        'total' => $total_pages,
                        'current' => $page
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($){
        var $body = $('body');
        $body.on('click', '.js-delete-all', function(){
            let cfm  = confirm('Are you sure to delete all viewing queues?');
            if(!cfm)
                return false;

            return true;
        });
    })(jQuery);
</script>