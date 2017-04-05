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
