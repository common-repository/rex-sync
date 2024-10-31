<?php
use Rex\Sync\Loader;

$settings = Loader::get_settings(true);
$image_sub_sizes = wp_get_registered_image_subsizes();

$templates = Loader::get_supported_templates();
$regions = Loader::get_supported_regions();

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Sync My Rex - Settings', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <form method="post">
                        <?php wp_nonce_field('rsc-settings', 'rsc-settings-nonce') ?>
                        <p>&nbsp;</p>
                        <h3><?php _e('Rex API', 'rex-sync') ?></h3>

                        <table>
                            <tr>
                                <th valign="top" style="width:200px;"><?php _e('Region', 'rex-sync') ?></th>
                                <td valign="top">
                                    <select name="rsc[region]" class="widefat" id="rex-sync-region">
                                        <?php foreach($regions as $key=>$name): ?>
                                        <option value="<?php echo esc_attr($key) ?>" <?php selected($settings['region'], $key) ?>><?php echo esc_html($name) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top" style="width:200px;"><?php _e('User Login', 'rex-sync') ?></th>
                                <td valign="top">
                                    <input type="text" name="rsc[user_login]" class="widefat" value="<?php esc_attr_e($settings['user_login']) ?>" id="rex-sync-user-login">
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('User Password', 'rex-sync') ?></th>
                                <td valign="top">
                                    <input type="password" name="rsc[user_password]" class="widefat" value="<?php esc_attr_e($settings['user_password']) ?>" id="rex-sync-user-password">
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Account/Agency ID', 'rex-sync') ?><p>(<a href="https://rex-sync.com" target="_blank">Only Pro version</a>)</p></th>
                                <td valign="top">
                                    <input type="text" class="widefat" id="rex-sync-account-id" disabled>
                                    <p><?php _e('If you have multiple accounts, enter your account ID, otherwise leave it blank.', 'rex-sync') ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th>&nbsp;</th>
                                <td>
                                    <button type="button" class="button-secondary" id="rex-sync-test-account"><?php _e('Validate account', 'rex-sync') ?></button>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Download Featured Image', 'rex-sync') ?></th>
                                <td valign="top">
                                    <label>
                                        <input type="checkbox" name="rsc[download_featured_image]" class="widefat" value="1" <?php checked($settings['download_featured_image'], 1) ?>><span> Yes</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Download Gallery Images', 'rex-sync') ?></th>
                                <td valign="top">
                                    <label>
                                        <input type="checkbox" class="widefat" value="1" checked disabled><span> Yes (<a href="https://rex-sync.com" target="_blank">Only Pro version</a>)</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Download Agent Image', 'rex-sync') ?></th>
                                <td valign="top">
                                    <label>
                                        <input type="checkbox" class="widefat" value="1" checked disabled><span> Yes (<a href="https://rex-sync.com" target="_blank">Only Pro version</a>)</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Available Listing Image Sizes', 'rex-sync') ?></th>
                                <td valign="top">
                                    <p><?php _e('Avoid flood disk space, select image sizes necessary', 'rex-sync') ?></p>
                                    <?php
                                    foreach($image_sub_sizes as $size_name => $size):
                                    ?>
                                    <p><label><input type="checkbox" name="rsc[image_sizes][]" value="<?php esc_attr_e($size_name); ?>" <?php checked(in_array($size_name, $settings['image_sizes']), 1) ?>> <?php esc_html_e($size_name.": {$size['width']}x{$size['height']}, ".($size['crop']?'cropped':'non-cropped') ) ?></label></p>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Webhook URL', 'rex-sync') ?></th>
                                <td valign="top">
                                    <strong><?php esc_html_e( Loader::get_webhook_url() ) ?></strong>
                                    <br/>
                                    <?php _e('From Rex Dashboard, go to Settings -> Webhooks -> Add New Webhook.', 'rex-sync') ?>
                                    <br/>
                                    <?php _e('Use webhook format: Changes Details (ID only).', 'rex-sync') ?>
                                </td>
                            </tr>
                            <tr>
                                <th valign="top"><?php _e('Templates', 'rex-sync') ?></th>
                                <td valign="top">
                                    <p><?php _e('Supports below templates', 'rex-sync'); ?></p>
                                    <?php
                                    foreach($templates as $template_key => $template_name):
                                        ?>
                                        <p><label><input type="checkbox" value="<?php esc_attr_e($template_key); ?>" checked disabled> <?php echo esc_html( $template_name ) ?>  (<a href="https://rex-sync.com" target="_blank">Only Pro version</a>)</label></p>
                                    <?php endforeach; ?>
                                    <p><?php _e('Templates also can be overridden entire or partial by copy them to your theme, please see description at top of template code files', 'rex-sync') ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <p>&nbsp;</p>
                                    <button type="submit" class="button-primary"><?php _e('Save settings', 'rex-sync') ?></button>
                                </td>
                            </tr>
                        </table>

                    </form>
                </div>
                <div class="col-2"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($){
        let $body = $('body');
        $body.on('click', '#rex-sync-test-account', function (e){
            let $button = $(this);
            let region = $('#rex-sync-region').val();
            let user_login = $('#rex-sync-user-login').val();
            let user_password = $('#rex-sync-user-password').val();
            if(!user_login || !user_password){
                alert('<?php _e('Please input user login and password', 'rex-sync') ?>');
                return false;
            }

            $button.text('<?php _e('Loading...','rex-sync') ?>');
            $button.attr('disabled', 'disabled');
            $.post(ajaxurl, {
                'action': 'rsc_validate_account',
                'user_login': user_login,
                'user_password': user_password,
                'region': region
            }, function(response){

                $button.text('Validate account');
                $button.attr('disabled', false);

                if(response.success){
                    alert('<?php _e('Validating account successfully!', 'rex-sync') ?>');
                }else{
                    alert('<?php _e('Validating account failed:', 'rex-sync') ?> ' + response.data);
                }
            }).fail(function() {
                alert('<?php _e('There is an error while validating account, please check server log for more details.', 'rex-sync') ?>');
            });
        });
    })(jQuery);
</script>