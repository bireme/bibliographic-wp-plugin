<?php
function biblio_page_admin() {

    $config = get_option('biblio_config');

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
                        <th scope="row"><?php _e('Related documents filter', 'biblio'); ?>:</th>
                        <td>
                            <input type="text" name="biblio_config[default_filter_db]" value='<?php echo $config['default_filter_db']; ?>' class="regular-text code">
                            <small style="display: block;">* <?php _e('The filters must be separated by commas.', 'biblio'); ?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('More related documents filter', 'biblio'); ?>:</th>
                        <td>
                            <input type="text" name="biblio_config[extra_filter_db]" value='<?php echo $config['extra_filter_db']; ?>' class="regular-text code">
                            <small style="display: block;">* <?php _e('The filters must be separated by commas.', 'biblio'); ?></small>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e('Search filters', 'biblio');?>:</th>
                        <?php
                            if (!isset($config['available_filter'])) {
                                $config['available_filter'] = 'Main subject;Publication type;Database;Publication country;Limits;Language;Journal;Year';
                                $order = explode(';', $config['available_filter'] );
                            } else {
                                $order = array_filter(explode(';', $config['available_filter']));
                            }
                        ?>
                        <td>
                            <table border=0>
                                <tr>
                                    <td>
                                        <p align="left"><?php _e('Available', 'biblio');?><br>
                                            <ul id="sortable1" class="droptrue">
                                                <?php
                                                    if(!in_array('Main subject', $order) && !in_array('Main subject ', $order) ){
                                                      echo '<li class="ui-state-default" id="Main subject">'.translate('Main subject','biblio').'</li>';
                                                    }
                                                    if(!in_array('Publication type', $order) && !in_array('Publication type ', $order) ){
                                                      echo '<li class="ui-state-default" id="Publication type">'.translate('Publication type','biblio').'</li>';
                                                    }
                                                    if(!in_array('Database', $order) && !in_array('Database ', $order) ){
                                                      echo '<li class="ui-state-default" id="Database">'.translate('Database','biblio').'</li>';
                                                    }
                                                    if(!in_array('Publication country', $order) && !in_array('Publication country ', $order) ){
                                                      echo '<li class="ui-state-default" id="Publication country">'.translate('Publication country','biblio').'</li>';
                                                    }
                                                    if(!in_array('Limits', $order) && !in_array('Limits ', $order) ){
                                                      echo '<li class="ui-state-default" id="Limits">'.translate('Limits','biblio').'</li>';
                                                    }
                                                    if(!in_array('Language', $order) && !in_array('Language ', $order) ){
                                                      echo '<li class="ui-state-default" id="Language">'.translate('Language','biblio').'</li>';
                                                    }
                                                    if(!in_array('Journal', $order) && !in_array('Journal ', $order) ){
                                                      echo '<li class="ui-state-default" id="Journal">'.translate('Journal','biblio').'</li>';
                                                    }
                                                    if(!in_array('Year', $order) && !in_array('Year ', $order) ){
                                                      echo '<li class="ui-state-default" id="Year">'.translate('Year','biblio').'</li>';
                                                    }
                                                ?>
                                            </ul>
                                        </p>
                                    </td>
                                    <td>
                                        <p align="left"><?php _e('Selected', 'biblio');?> <br>
                                          <ul id="sortable2" class="sortable-list">
                                              <?php
                                                  foreach ($order as $index => $item) {
                                                      $item = trim($item); // Important
                                                      echo '<li class="ui-state-default" id="'.$item.'">'.translate($item ,'biblio').'</li>';
                                                  }
                                              ?>
                                          </ul>
                                          <input type="hidden" id="order_aux" name="biblio_config[available_filter]" value="<?php echo trim($config['available_filter']); ?>" >
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
            $j( "ul.droptrue" ).sortable({
                connectWith: "ul"
            });

            $j('.sortable-list').sortable({
                connectWith: 'ul',
                update: function(event, ui) {
                    var changedList = this.id;
                    var order = $j(this).sortable('toArray');
                    var positions = order.join(';');
                    $j('#order_aux').val(positions);
                }
            });
        } );
    </script>

    <?php
}
?>
