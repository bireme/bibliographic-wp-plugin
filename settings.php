<?php
function lildbi_page_admin() {

    $config = get_option('lildbi_config');

    ?>
    <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('LILDBI-WEB Plugin Options', 'lildbi'); ?></h2>

            <form method="post" action="options.php">

                <?php settings_fields('lildbi-settings-group'); ?>

                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><?php _e('Plugin page', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[plugin_slug]" value="<?php echo ($config['plugin_slug'] != '' ? $config['plugin_slug'] : 'lildbi'); ?>" class="regular-text code"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Filter query', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[initial_filter]" value='<?php echo $config['initial_filter'] ?>' class="regular-text code"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('AddThis profile ID', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[addthis_profile_id]" value="<?php echo $config['addthis_profile_id'] ?>" class="regular-text code"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Google Analytics code', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[google_analytics_code]" value="<?php echo $config['google_analytics_code'] ?>" class="regular-text code"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('About URL', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[about]" value="<?php echo $config['about'] ?>" class="regular-text code"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e('Tutorials URL', 'lildbi'); ?>:</th>
                            <td><input type="text" name="lildbi_config[tutorials]" value="<?php echo $config['tutorials'] ?>" class="regular-text code"></td>
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
