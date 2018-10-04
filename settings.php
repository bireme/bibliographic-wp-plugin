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
                        }else{
                            echo '<tr valign="top">';
                            echo '   <th scope="row">' . __("Page title", "biblio") . ':</th>';
                            echo '   <td><input type="text" name="biblio_config[plugin_title]" value="' . $config["plugin_title"] . '" class="regular-text code"></td>';
                            echo '</tr>';
                        }

                        ?>
                        <tr valign="top">
                            <th scope="row"><?php _e('Sidebar order', 'biblio');?>:</th>

                            <?php
                              if(!isset($config['available_filter'])){
                                $config['available_filter'] = 'Main subject;Publication type;Database;Publication country;Limits;Language;Journal;Year';
                                $order = explode(';', $config['available_filter'] );

                              }else {
                                $order = explode(';', $config['available_filter'] );
                            }

                            ?>

                            <td>


                              <table border=0>
                                <tr>
                                <td >
                                    <p align="right"><?php _e('Available', 'biblio');?><br>
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

                                <td >
                                    <p align="left"><?php _e('Selected', 'biblio');?> <br>
                                      <ul id="sortable2" class="sortable-list">
                                      <?php
                                      foreach ($order as $index => $item) {
                                        $item = trim($item); // Important
                                        echo '<li class="ui-state-default" id="'.$item.'">'.translate($item ,'biblio').'</li>';
                                      }
                                      ?>
                                      </ul>
                                      <input type="hidden" id="order_aux" name="biblio_config[available_filter]" value="<?php echo trim($config['available_filter']); ?> " >

                                    </p>
                                </td>
                                </tr>
                                </table>

                            </td>
                        </tr>

                        <?php /* ?>

                        <tr valign="top">
                            <th scope="row">
                                <?php _e('Display filters', 'biblio'); ?>:
                            </th>
                            <td>
                                <fieldset>
                                    <label for="available_filter_main_subject">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="main_subject" id="available_filter_main_subject" <?php echo (!isset($config['available_filter']) || in_array('main_subject', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Main subject', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_publication_type">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="publication_type" id="available_filter_publication_type" <?php echo (!isset($config['available_filter']) || in_array('publication_type', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Publication type', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_database">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="database" id="available_filter_database" <?php echo (!isset($config['available_filter']) || in_array('database', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Database', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_publication_country">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="publication_country" id="available_filter_publication_country" <?php echo (!isset($config['available_filter']) ||  in_array('publication_country', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Publication country', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_limits">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="limit" id="available_filter_limits" <?php echo (!isset($config['available_filter']) ||  in_array('limit', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Limits', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_language">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="language" id="available_filter_language" <?php echo (!isset($config['available_filter']) ||  in_array('language', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Language', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_journal">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="journal" id="available_filter_journal" <?php echo (!isset($config['available_filter']) || in_array('journal', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Journal', 'biblio'); ?>
                                    </label>
                                    <br/>
                                    <label for="available_filter_year">
                                        <input type="checkbox" name="biblio_config[available_filter][]" value="year" id="available_filter_year" <?php echo (!isset($config['available_filter']) ||  in_array('year', $config['available_filter']) ?  " checked='true'" : '') ;?> ></input>
                                        <?php _e('Year', 'biblio'); ?>
                                    </label>

                                </fieldset>
                            </td>
                        </tr>
                      */?>

                    </tbody>
                </table>

                <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save changes') ?>" />
                </p>

            </form>
        </div>
        <script type="text/javascript">
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
