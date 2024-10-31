<?php
use Rex\Sync\Loader;

$settings = Loader::get_settings(true);

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Manual Sync', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <p>&nbsp;</p>
                    <button type="button" class="button-primary" id="rex-manual-sync"><?php _e('Start download listings', 'rex-sync') ?></button>
                    <p>This will download all listings from Rex. Existing listings on WordPress will be updated.</p>
                    <p>DO NOT refresh browser or navigate browser, the process will be stopped.</p>
                </div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div id="progress_bar"><div id="progress_bar_value"></div></div>
                <div id="preview" style="display: block; padding: 20px;border: 1px solid lightgray; max-height: 600px; overflow: auto">
                    <div id="results" ></div>
                    <div id="results-loading" style="display: none">loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($){
        let $body = $('body');
        let $el_results = $('#results');
        let $el_results_loading = $('#results-loading');
        let total_rows = 0;
        let $progress_bar = $('#progress_bar');
        let is_running = false;

        $progress_bar.bind('reset', function($e){
            let $bar_value = $progress_bar.find('#progress_bar_value');

            $bar_value.css('width', '0');
            $bar_value.text('');
            $(this).data('value', 0);

        });
        $progress_bar.bind('add_value', function($e, val, total){
            let current_value = $(this).data('value') || 0;
            current_value += val;
            let $bar_value = $progress_bar.find('#progress_bar_value');
            let percent = Math.round(current_value/total *100);

            percent = percent > 100 ? 100 : percent;

            $bar_value.css('width', percent + '%');
            $bar_value.text(percent + '%');
            $(this).data('value', current_value);
        });

        $body.on('click', '#rex-manual-sync', function (e) {
            e.preventDefault();

            $(this).attr('disabled', 'disabled');

            reset_data();
            start_log();
            start_download_listings();
        });

        function reset_data() {

        }

        function start_log(){
            $el_results.empty();
            $el_results_loading.show();
        }
        function end_log(){
            $el_results_loading.hide();
        }
        function add_log_message(msg, scroll = false) {
            let $p = $('<p></p>').html(msg);
            $el_results.append($p);

            if(scroll){
                $p.get(0).scrollIntoView(true);
            }
        }

        function start_download_listings(){

            // start download listings
            is_running = true;
            add_log_message('Start download listings, please wait...');
            download_listings(1);
        }

        function download_listings(page = 1){
            is_running = true;
            $.post(ajaxurl, {
                'action': 'rsc_download_listings',
                'pidx': page
            }, function(response){
                if(response.data){
                    let rows_count = response.data.length;
                    add_log_message(`${rows_count} listings have been downloaded, ${response.report.inserted} inserted/updated, ${response.report.failed} failed...`);

                    total_rows += rows_count;
                    $progress_bar.trigger('add_value', [rows_count, response.total]);

                    if(total_rows >= response.total) {
                        add_log_message(`All ${total_rows} listings have been downloaded. Done!!`);
                        end_log();
                        is_running = false;
                    }else{
                        setTimeout(function(){
                            download_listings(page+1);
                        }, 50);
                    }
                }else{
                    add_log_message('Cannot download listings at page ' + page);
                    end_log();
                    is_running = false;
                }
            }, "json").fail(function() {
                add_log_message('There is an error while downloading listings, please check server log for more details.');
                end_log();
            });

        }

        window.onbeforeunload = function(e) {
            if(total_rows > 0 && is_running) {
                let cfm = confirm('Reload page may stop the download process, reload now?');
                if (!cfm) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        }

    })(jQuery);
</script>