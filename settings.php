<?php
function biblio_page_admin() {
    global $biblio_texts;

    $config = get_option('biblio_config');

    if ($biblio_texts['filter']){
        $available_filter_list = $biblio_texts['filter'];
    }else{
        $available_filter_list = array(
                                    'descriptor_filter' => translate('Main subject','biblio') ,
                                    'publication_type' =>  translate('Publication type', 'biblio'),
                                    'database' =>  translate('Database','biblio'),
                                    'publication_country' =>  translate('Publication country', 'biblio'),
                                    'publication_language' =>  translate('Language','biblio'),
                                    'publication_year' =>  translate('Year','biblio'),
                                    'journal' =>  translate('Journal','biblio')
        );
        $biblio_texts['filter'] = $available_filter_list;
    }

    if ( $config['available_filter'] ){
        $config_filter_list = explode(';', $config['available_filter']);
    }else{
        $config_filter_list = array_keys($available_filter_list);
    }
?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h2><?php _e('Bibliographic record settings', 'biblio'); ?></h2>
        <form method="post" action="options.php">

            <?php settings_fields('biblio-settings-group'); ?>

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><?php _e('Plugin page', 'biblio'); ?>:</th>
                        <td><input type="text" name="biblio_config[plugin_slug]" value="<?php echo ($config['plugin_slug'] != '' ? $config['plugin_slug'] : 'biblio'); ?>" class="regular-text code"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Filter query', 'biblio'); ?>:</th>
                        <td><input type="text" name="biblio_config[initial_filter]" value='<?php echo $config['initial_filter'] ?>' class="regular-text code"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('AddThis profile ID', 'biblio'); ?>:</th>
                        <td><input type="text" name="biblio_config[addthis_profile_id]" value="<?php echo $config['addthis_profile_id'] ?>" class="regular-text code"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Google Analytics code', 'biblio'); ?>:</th>
                        <td><input type="text" name="biblio_config[google_analytics_code]" value="<?php echo $config['google_analytics_code'] ?>" class="regular-text code"></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Fulltext', 'biblio'); ?>:</th>
                        <td>
                            <label for="present_alternative_links">
                                <input type="checkbox" name="biblio_config[alternative_links]" value="true" id="present_alternative_links" <?php echo (isset($config['alternative_links']) ?  " checked='true'" : '') ;?> ></input>
                                <?php _e('Present alternative fulltext links', 'biblio'); ?>
                            </label>
                        </td>
                    </tr>
                    <?php
                        if ( function_exists( 'pll_the_languages' ) ) {
                            $available_languages = pll_languages_list();
                            $available_languages_name = pll_languages_list(array('fields' => 'name'));
                            $count = 0;

                            foreach ($available_languages as $lang) {
                                $key_name = 'plugin_title_' . $lang;
                                $home_url = 'home_url_' . $lang;

                                echo '<tr valign="top">';
                                echo '    <th scope="row"> ' . __("Home URL", "biblio") . ' (' . $available_languages_name[$count] . '):</th>';
                                echo '    <td><input type="text" name="biblio_config[' . $home_url . ']" value="' . $config[$home_url] . '" class="regular-text code"></td>';
                                echo '</tr>';

                                echo '<tr valign="top">';
                                echo '    <th scope="row"> ' . __("Page title", "biblio") . ' (' . $available_languages_name[$count] . '):</th>';
                                echo '    <td><input type="text" name="biblio_config[' . $key_name . ']" value="' . $config[$key_name] . '" class="regular-text code"></td>';
                                echo '</tr>';
                                $count++;
                            }
                        } else {
                            echo '<tr valign="top">';
                            echo '   <th scope="row">' . __("Page title", "biblio") . ':</th>';
                            echo '   <td><input type="text" name="biblio_config[plugin_title]" value="' . $config["plugin_title"] . '" class="regular-text code"></td>';
                            echo '</tr>';
                        }
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php _e('Related Documents filter', 'biblio'); ?>:</th>
                        <td>
                            <input type="text" name="biblio_config[default_filter_db]" value='<?php echo $config['default_filter_db']; ?>' class="regular-text code">
                            <small style="display: block;">* <?php _e('The filters must be separated by commas.', 'biblio'); ?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('More Related filter', 'biblio'); ?>:</th>
                        <td>
                            <input type="text" name="biblio_config[extra_filter_db]" value='<?php echo $config['extra_filter_db']; ?>' class="regular-text code">
                            <small style="display: block;">* <?php _e('The filters must be separated by commas.', 'biblio'); ?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Search filters', 'biblio');?>:</th>
                        <td>
                            <table border=0>
                                <tr>
                                    <td>
                                        <p align="left"><?php _e('Available', 'biblio');?><br>
                                            <ul id="sortable1" class="connectedSortable">
                                                <?php
                                                    foreach ($available_filter_list as $filter_field => $filter_title){
                                                        if ( !in_array($filter_field, $config_filter_list) ) {
                                                            echo '<li class="ui-state-default" id="' .  $filter_field .'">' . $filter_title . '</li>';
                                                        }
                                                    }
                                                ?>
                                            </ul>
                                        </p>
                                    </td>
                                    <td>
                                        <p align="left"><?php _e('Selected', 'biblio');?> <br>
                                          <ul id="sortable2" class="connectedSortable">
                                              <?php
                                                foreach ($config_filter_list as $selected_filter) {
                                                    $filter_title = $biblio_texts['filter'][$selected_filter];
                                                    if ($filter_title != ''){
                                                        echo '<li class="ui-state-default" id="' . $selected_filter . '">' . $filter_title . '</li>';
                                                    }
                                                }
                                              ?>
                                          </ul>
                                          <input type="hidden" id="available_filter_aux" name="biblio_config[available_filter]" value="<?php echo $config['available_filter']; ?>" >
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save changes') ?>" />
            </p>
        </form>
    </div>
    <script type="text/javascript">
        var $j = jQuery.noConflict();

        $j( function() {
            $j("#sortable1, #sortable2").sortable({
                connectWith: ".connectedSortable"
            });

            $j("#sortable2").sortable({
                update: function(event, ui) {
                    var changedList = this.id;
                    var selected_filter = $j(this).sortable('toArray');
                    var selected_filter_list = selected_filter.join(';');
                    $j('#available_filter_aux').val(selected_filter_list);
                }
            });

        } );
    </script>

    <?php
}
?>
