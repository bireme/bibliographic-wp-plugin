<?php
function biblio_page_admin() {

    $config = get_option('biblio_config');

    ?>
    <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('Bibliographic Plugin Options', 'biblio'); ?></h2>

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
                            <td align="left">
                                <span><?php _e('Available filters', 'biblio'); ?></span>
                            </td>
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

                    </tbody>
                </table>

                <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>

            </form>
        </div>

        <?php
}
?>
