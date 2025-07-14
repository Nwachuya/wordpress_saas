/**
 * Changes the default WordPress REST API prefix from 'wp-json' to a custom value.
 *
 * @return string The new custom REST API prefix.
 */
function change_rest_api_prefix() {
    return 'api'; // You can change 'api' to whatever you want.
}
add_filter( 'rest_url_prefix', 'change_rest_api_prefix' );
