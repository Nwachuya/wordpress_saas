<?php
/**
 * Custom WordPress Core Utilities Template
 *
 * This file serves as a utilities template, containing essential modifications
 * to default WordPress behavior, custom access controls, and a custom user flow.
 * It includes checks to notify administrators if required pages for the custom
 * user flow are missing, and manages user API keys with an inline JavaScript
 * "click to copy" feature.
 */

// --- Post Revisions Configuration ---
// Limits WordPress to keeping only 3 revisions for each post/page if not already defined.
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}


// --- User Registration Redirect ---
/**
 * Redirects users to the '/welcome/' page after successful registration.
 *
 * @return string The URL to redirect to.
 */
function wps_registration_redirect() {
    return home_url( '/welcome/' );
}
add_filter( 'registration_redirect', 'wps_registration_redirect' );


// --- Enable Shortcodes in Text Widgets ---
/**
 * Adds support for shortcodes within default WordPress Text widgets.
 * By default, shortcodes are not processed in widget content.
 */
add_filter( 'widget_text', 'do_shortcode' );


// --- Customize Comment Form Fields ---
/**
 * Removes the 'url' field (website input) from the default WordPress comment form.
 *
 * @param array $fields An array of default comment form fields.
 * @return array The modified array of comment form fields.
 */
function remove_comment_fields($fields) {
    unset($fields['url']);
    return $fields;
}
add_filter('comment_form_default_fields','remove_comment_fields');


// --- Custom Login/Admin Area Access Control ---
/**
 * Intercepts requests to default WordPress login and admin URLs
 * and redirects unauthenticated users to a custom '/start/' page,
 * while allowing password reset flows to proceed.
 */
function wps_redirect_default_wp_pages() {
    // Do not redirect for logged-in users (especially admins)
    if (is_user_logged_in()) {
        return;
    }

    $request_uri = $_SERVER['REQUEST_URI'];
    $home_url = home_url('/'); // Get the site's home URL for redirection

    // 1. Handle wp-login.php requests
    if (strpos($request_uri, 'wp-login.php') !== false) {
        // Allow password reset actions to proceed
        $allowed_actions = array('lostpassword', 'rp', 'resetpass');
        if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions)) {
            return; // Don't redirect if it's a password reset action
        }

        // For all other wp-login.php requests (login, register, logout), redirect to /start/
        wp_redirect($home_url . 'start/');
        exit;
    }

    // 2. Handle wp-admin/ requests (except admin-ajax.php)
    // This catches /wp-admin/, /wp-admin/index.php, /wp-admin/options-general.php, etc.
    if (strpos($request_uri, '/wp-admin/') !== false) {
        // Crucial: Do NOT redirect admin-ajax.php as it's often used by front-end scripts
        if (strpos($request_uri, '/wp-admin/admin-ajax.php') !== false) {
            return;
        }

        // Redirect all other /wp-admin/ access for non-logged-in users to /start/
        wp_redirect($home_url . 'start/');
        exit;
    }

    // 3. Handle Multisite Registration/Activation (if applicable)
    if (is_multisite()) {
        if (strpos($request_uri, 'wp-signup.php') !== false || strpos($request_uri, 'wp-activate.php') !== false) {
            wp_redirect($home_url . 'start/');
            exit;
        }
    }
}
add_action('init', 'wps_redirect_default_wp_pages'); // Use 'init' for early redirection


// --- Hide Admin Bar for Non-Administrator Users ---
/**
 * Hides the WordPress admin bar for any user who is not an administrator.
 *
 * @param bool $show_admin_bar Whether to show the admin bar.
 * @return bool Modified value.
 */
function wps_hide_admin_bar_for_non_admins($show_admin_bar) {
    // 'manage_options' is a capability typically exclusive to Administrator roles.
    if (!current_user_can('manage_options')) {
        return false; // Hide the admin bar
    }
    return $show_admin_bar; // Show it for administrators (or default behavior)
}
add_filter('show_admin_bar', 'wps_hide_admin_bar_for_non_admins');


// --- Control Access to the '/welcome/' Page ---
/**
 * Manages access to the '/welcome/' page:
 * - Non-logged-in users are redirected away.
 * - Logged-in users are redirected away if they have viewed the page before.
 * - Logged-in users viewing for the first time after registration will be allowed
 *   to see the page and have a user meta flag set.
 */
function wps_handle_welcome_page_access() {
    // Check if the current page is the 'welcome' page.
    if (is_page('welcome')) {
        // 1. If user is NOT logged in, redirect them to the home page.
        if (!is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // 2. If user IS logged in, check if they've viewed it before.
        $user_id = get_current_user_id();
        // Custom user meta key to track if the welcome page has been viewed.
        $has_viewed_welcome = get_user_meta($user_id, '_wps_has_viewed_welcome_page', true);

        if ($has_viewed_welcome) {
            // User has viewed it before, redirect them to the home page (or another appropriate page).
            wp_redirect(home_url()); // You might change this to a profile page etc.
            exit;
        } else {
            // This is the first time the logged-in user is viewing the welcome page.
            // Mark it as viewed for this user.
            update_user_meta($user_id, '_wps_has_viewed_welcome_page', 1);
            // Allow the page to load for this first view.
        }
    }
}
add_action('template_redirect', 'wps_handle_welcome_page_access');


// --- Redirect Logged-In Users from '/start/' to '/portal/' ---
/**
 * Redirects logged-in users attempting to access the '/start/' page
 * to the '/portal/' page, as '/start/' is intended for login/registration only.
 */
function wps_redirect_start_for_logged_in_users() {
    // Check if the current page is the 'start' page.
    if (is_page('start')) {
        // Check if the user is currently logged in.
        if (is_user_logged_in()) {
            // If both conditions are true, redirect to the '/portal/' page.
            wp_redirect(home_url('/portal/'));
            exit; // Important: Stop further script execution after redirection.
        }
        // If the user is NOT logged in, allow them to stay on the '/start/' page
        // so they can use the login/registration forms.
    }
}
add_action('template_redirect', 'wps_redirect_start_for_logged_in_users');


// --- Restrict '/portal/' Page Access to Logged-In Users Only ---
/**
 * Ensures that only logged-in users can access the '/portal/' page.
 * Non-logged-in users attempting to access it are redirected to '/start/'.
 */
function wps_restrict_portal_access() {
    // Check if the current page is the 'portal' page.
    if (is_page('portal')) {
        // If the user is NOT logged in, redirect them to the '/start/' page.
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/start/'));
            exit; // Stop further script execution after redirection.
        }
        // If the user IS logged in, allow them to see the page.
    }
}
add_action('template_redirect', 'wps_restrict_portal_access');


// --- User API Key Management ---
/**
 * Generates a cryptographically secure random API key.
 *
 * @param int $user_id The ID of the user for whom to generate the key.
 * @return string The generated API key.
 */
function wps_generate_api_key($user_id) {
    // Generate 32 random bytes (256 bits) and convert to a hexadecimal string.
    $random_bytes = random_bytes(32);
    $api_key = bin2hex($random_bytes);
    return $api_key;
}

/**
 * Adds a read-only API Key field to the user's profile page in the admin.
 * Generates a key if one doesn't exist for the user.
 *
 * @param WP_User $user The user object being edited.
 */
function wps_add_api_key_profile_field( $user ) {
    $api_key = get_user_meta( $user->ID, 'api_key', true );
    // Generate an API key if it's missing for the user.
    if ( empty( $api_key ) ) {
        $api_key = wps_generate_api_key( $user->ID );
        update_user_meta( $user->ID, 'api_key', $api_key );
    }
    ?>
    <h3>API Key Management</h3>
    <table class="form-table">
        <tr>
            <th><label for="wps_api_key_admin">API Key</label></th>
            <td>
                <input type="text" id="wps_api_key_admin" name="wps_api_key_admin" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" readonly />
                <p class="description">This is the user's unique API key. It is managed automatically and cannot be manually edited here.</p>
            </td>
        </tr>
    </table>
    <?php
}
// Add the field to both user profile screens (personal and other users' profiles).
add_action( 'show_user_profile', 'wps_add_api_key_profile_field' );
add_action( 'edit_user_profile', 'wps_add_api_key_profile_field' );

/**
 * Prevents the API key from being manually updated via the user profile screen.
 * The key should only be generated/regenerated programmatically.
 *
 * @param int $user_id The ID of the user being updated.
 */
function wps_prevent_api_key_manual_update( $user_id ) {
    // This hook fires after validation, so simply returning false here
    // would halt the update process for other fields.
    // The key is already read-only in the input, so no direct update is possible via the form.
    // This function acts as a safeguard.
    if ( isset( $_POST['wps_api_key_admin'] ) ) {
        // Unset the value from $_POST to prevent WordPress from trying to save it.
        // The API key should only be updated via regeneration.
        unset( $_POST['wps_api_key_admin'] );
    }
}
// Apply this safeguard when user profile options are updated.
add_action( 'personal_options_update', 'wps_prevent_api_key_manual_update' );
add_action( 'edit_user_profile_update', 'wps_prevent_api_key_manual_update' );


/**
 * Includes inline JavaScript for the "Click to Copy" functionality.
 * This script is loaded only on the '/settings' page in the site footer.
 *
 * Note: While convenient for a single-file template, for larger projects
 * it's generally better practice to enqueue separate .js files for caching.
 */
function wps_add_api_key_copy_inline_script() {
    // Only add the script if we are on the '/settings' page.
    if (is_page('settings')) {
        // Ensure jQuery is loaded as the script relies on it.
        wp_enqueue_script('jquery');
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Find the copy button and target input field
                var $copyButton = $('.wps-copy-api-key-button');
                var $copyFeedback = $('.wps-copy-feedback');

                if ($copyButton.length && $copyFeedback.length) {
                    $copyButton.on('click', function() {
                        var targetSelector = $(this).data('copy-target');
                        var $targetInput = $(targetSelector);

                        if ($targetInput.length) {
                            // Select the text in the input field
                            $targetInput.select();
                            $targetInput[0].setSelectionRange(0, 99999); // For mobile devices

                            // Attempt to copy using modern Clipboard API first
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText($targetInput.val())
                                    .then(function() {
                                        // Success feedback
                                        $copyFeedback.fadeIn(200).delay(1500).fadeOut(400);
                                    })
                                    .catch(function(err) {
                                        console.error('Failed to copy text using Clipboard API: ', err);
                                        // Fallback to deprecated execCommand if Clipboard API fails
                                        try {
                                            document.execCommand('copy');
                                            $copyFeedback.fadeIn(200).delay(1500).fadeOut(400);
                                        } catch (err) {
                                            console.error('Failed to copy text using execCommand: ', err);
                                            alert('Failed to copy. Please copy the API key manually.');
                                        }
                                    });
                            } else {
                                // Fallback for older browsers
                                try {
                                    document.execCommand('copy');
                                    $copyFeedback.fadeIn(200).delay(1500).fadeOut(400);
                                } catch (err) {
                                    console.error('Failed to copy text using execCommand: ', err);
                                    alert('Failed to copy. Please copy the API key manually.');
                                }
                            }
                        }
                    });
                }
            });
        </script>
        <?php
    }
}
// Add the script to the footer.
add_action('wp_footer', 'wps_add_api_key_copy_inline_script');


/**
 * Shortcode to display user's API key, with regeneration and a "Click to Copy" button.
 * Place [api_key] on your /settings page.
 *
 * @param array $atts Shortcode attributes (none are needed).
 * @return string HTML output.
 */
function wps_api_key_shortcode( $atts ) {
    // Ensure user is logged in to view API key.
    if ( ! is_user_logged_in() ) {
        return '<p>You must be logged in to view your API key.</p>';
    }

    $user_id = get_current_user_id();
    $api_key = get_user_meta( $user_id, 'api_key', true );

    // Handle API key regeneration request.
    if ( isset( $_POST['wps_regenerate_api_key'] ) && check_admin_referer( 'wps_regenerate_api_key_nonce' ) ) {
        $api_key = wps_generate_api_key( $user_id );
        update_user_meta( $user_id, 'api_key', $api_key );
        // Add a query arg to indicate success for a transient message after redirect.
        wp_redirect( add_query_arg('api_key_regenerated', 'true', get_permalink()) );
        exit;
    }

    // Generate API key if it's currently empty for the user.
    if ( empty( $api_key ) ) {
        $api_key = wps_generate_api_key( $user_id );
        update_user_meta( $user_id, 'api_key', $api_key );
    }

    // Start output buffering to capture HTML.
    ob_start();
    ?>
    <div class="wps-api-key-container">
        <h3>Your API Key</h3>
        <?php
        // Display a regeneration success message if redirected after regeneration.
        if ( isset($_GET['api_key_regenerated']) && $_GET['api_key_regenerated'] == 'true' ) {
            echo '<div class="notice notice-success is-dismissible"><p>API Key successfully regenerated!</p></div>';
            // Use JavaScript to remove the query arg after display to prevent persistent message on refresh.
            echo '<script>window.history.replaceState(null, null, window.location.pathname);</script>';
        }
        ?>
        <p>
            <label for="wps-api-key-display">API Key:</label><br>
            <input type="text" id="wps-api-key-display" value="<?php echo esc_attr($api_key); ?>" class="regular-text wps-api-key-input" readonly>
            <button type="button" class="button wps-copy-api-key-button" data-copy-target="#wps-api-key-display">
                Copy
            </button>
            <span class="wps-copy-feedback" style="display:none; margin-left: 10px; color: green;">Copied!</span>
        </p>

        <form method="post" style="margin-top: 15px;">
            <?php wp_nonce_field( 'wps_regenerate_api_key_nonce' ); ?>
            <input type="submit" name="wps_regenerate_api_key" value="Regenerate API Key" class="button button-secondary" />
            <p class="description">Clicking "Regenerate" will create a new API key and invalidate the old one. Update any applications using it.</p>
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'api_key', 'wps_api_key_shortcode' );


// --- Dashboard Dependency Checker and Notices ---
/**
 * Checks for the existence of required pages.
 * Displays admin notices if any are not found.
 * This ensures administrators are aware of missing components for the utilities template.
 */
function wps_display_missing_dependency_notices() {
    // Only show notices to administrators
    if (!current_user_can('manage_options')) {
        return;
    }

    $messages = array();
    $pages_admin_url = esc_url(admin_url('post-new.php?post_type=page'));

    // Check for required pages by slug for the custom user flow
    $required_pages = array(
        'start'   => 'Login/Registration Page (for user access control)',
        'welcome' => 'Welcome Page (for post-registration redirect)',
        'portal'  => 'User Portal Page (for logged-in user dashboard)',
        'settings' => 'User Settings Page (for API key management and other user settings)', // 'settings' page added
    );

    foreach ($required_pages as $slug => $description) {
        if (!get_page_by_path($slug)) {
            $messages[] = sprintf(
                '<strong>Utilities Template Alert:</strong> The "<strong>/%1$s/</strong>" page is missing. This page is essential for the %2$s. Please <a href="%3$s">create it</a> with the slug "%1$s".',
                esc_html($slug),
                esc_html($description),
                $pages_admin_url
            );
        }
    }

    // Display all collected messages as admin notices
    if (!empty($messages)) {
        echo '<div class="notice notice-error is-dismissible">';
        echo '<h3>Utilities Template Requirements Missing!</h3>';
        foreach ($messages as $message) {
            echo '<p>' . $message . '</p>';
        }
        echo '<p>Please address these issues to ensure full functionality of the custom user flow and API key management.</p>';
        echo '</div>';
    }
}
add_action('admin_notices', 'wps_display_missing_dependency_notices');
