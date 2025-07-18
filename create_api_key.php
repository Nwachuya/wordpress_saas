/**
 * Plugin Name: User API Key
 * Description: Adds a view-only API key field and shortcode with regeneration. Use [api_key] to display
 * Version: 1.3.3
 * Author: Obinna Nwachuya
 */

if (!defined('ABSPATH')) {
    exit;
}

function my_generate_api_key($user_id) {
    $random_bytes = random_bytes(32);
    $api_key = bin2hex($random_bytes);
    return $api_key;
}

function my_add_api_key_field( $user ) {
    $api_key = get_user_meta( $user->ID, 'api_key', true );
    if ( empty( $api_key ) ) {
        $api_key = my_generate_api_key( $user->ID );
        update_user_meta( $user->ID, 'api_key', $api_key );
    }

    ?>
    <h3>API Key</h3>
    <table class="form-table">
        <tr>
            <th><label for="api_key">API Key</label></th>
            <td>
                <input type="text" id="api_key" name="api_key" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" readonly />
                <p class="description">This is your unique API key. Treat it like a password.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'my_add_api_key_field' );
add_action( 'edit_user_profile', 'my_add_api_key_field' );

function my_prevent_api_key_update( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
}

add_action( 'personal_options_update', 'my_prevent_api_key_update' );
add_action( 'edit_user_profile_update', 'my_prevent_api_key_update' );

function my_admin_enqueue_styles() {
    wp_enqueue_style( 'my-admin-styles', plugin_dir_url( __FILE__ ) . 'admin-styles.css' );
}
add_action( 'admin_enqueue_scripts', 'my_admin_enqueue_styles' );


/**
 * Shortcode to display API key and regenerate it.  Includes User Points inline.
 *
 * @param array $atts Shortcode attributes (none are needed).
 * @return string HTML output of the API key and regenerate button.
 */
function my_api_key_shortcode( $atts ) {
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to view your API key and points.</p>';
    }

    $user_id = get_current_user_id();
    $api_key = get_user_meta( $user_id, 'api_key', true );

    if ( isset( $_POST['regenerate_api_key'] ) && check_admin_referer( 'regenerate_api_key' ) ) {
        // Regenerate the API key
        $api_key = my_generate_api_key( $user_id );
        update_user_meta( $user_id, 'api_key', $api_key );
    }

    if ( empty( $api_key ) ) {
        // Generate if it doesn't exist
        $api_key = my_generate_api_key( $user_id );
        update_user_meta( $user_id, 'api_key', $api_key );
    }

    // Get user points directly (using the shortcode)
    $points_output = do_shortcode('[user_points]');

    ob_start();
    ?>
    <p><strong>API Key:</strong> <?php echo esc_html($api_key); ?></p>

    <form method="post">
        <?php wp_nonce_field( 'regenerate_api_key' ); ?>
        <input type="submit" name="regenerate_api_key" value="Regenerate API Key" class="button" />
    </form>

    <?php
    $api_key_output = ob_get_clean();
    return $api_key_output;
}
add_shortcode( 'api_key', 'my_api_key_shortcode' );
