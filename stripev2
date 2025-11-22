// Register the payment post type
function register_payment_post_type() {
    $args = array(
        'label' => 'Payments',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
        'rewrite' => array('slug' => 'payments'),
        'supports' => array('title', 'editor', 'custom-fields'),
        'menu_icon' => 'dashicons-money-alt',
        'show_in_rest' => true,
    );
    register_post_type('payment', $args);
}
add_action('init', 'register_payment_post_type');

// Add admin menu for Stripe settings
add_action('admin_menu', 'add_stripe_settings_page');

function add_stripe_settings_page() {
    add_submenu_page(
        'options-general.php',
        'Stripe Settings',
        'Stripe Settings',
        'manage_options',
        'stripe-settings',
        'stripe_settings_page'
    );
}

// Register settings
add_action('admin_init', 'register_stripe_settings');

function register_stripe_settings() {
    // Register setting groups
    register_setting('stripe_settings_group', 'stripe_test_mode');
    register_setting('stripe_settings_group', 'stripe_test_publishable_key');
    register_setting('stripe_settings_group', 'stripe_test_secret_key');
    register_setting('stripe_settings_group', 'stripe_live_publishable_key');
    register_setting('stripe_settings_group', 'stripe_live_secret_key');
    
    // Add settings sections
    add_settings_section(
        'stripe_api_section',
        'Stripe API Configuration',
        'stripe_api_section_callback',
        'stripe-settings'
    );
    
    // Add settings fields
    add_settings_field(
        'stripe_test_mode',
        'Test Mode',
        'stripe_test_mode_callback',
        'stripe-settings',
        'stripe_api_section'
    );
    
    add_settings_field(
        'stripe_test_keys',
        'Test API Keys',
        'stripe_test_keys_callback',
        'stripe-settings',
        'stripe_api_section'
    );
    
    add_settings_field(
        'stripe_live_keys',
        'Live API Keys',
        'stripe_live_keys_callback',
        'stripe-settings',
        'stripe_api_section'
    );
    
    add_settings_field(
        'stripe_webhook_url',
        'Webhook URL',
        'stripe_webhook_url_callback',
        'stripe-settings',
        'stripe_api_section'
    );
}

function stripe_api_section_callback() {
    echo '<p>Configure your Stripe API keys and webhook settings.</p>';
}

function stripe_test_mode_callback() {
    $test_mode = get_option('stripe_test_mode', 1);
    echo '<input type="checkbox" name="stripe_test_mode" value="1" ' . checked(1, $test_mode, false) . ' />';
    echo '<label for="stripe_test_mode">Enable Test Mode</label>';
}

function stripe_test_keys_callback() {
    $test_publishable = get_option('stripe_test_publishable_key', '');
    $test_secret = get_option('stripe_test_secret_key', '');
    
    // Mask the keys for display
    $masked_publishable = !empty($test_publishable) ? substr($test_publishable, 0, 8) . '...' . substr($test_publishable, -4) : '';
    $masked_secret = !empty($test_secret) ? substr($test_secret, 0, 8) . '...' . substr($test_secret, -4) : '';
    
    echo '<p><strong>Test Publishable Key:</strong></p>';
    echo '<input type="text" name="stripe_test_publishable_key" value="' . esc_attr($test_publishable) . '" style="width: 400px;" placeholder="pk_test_..." />';
    if (!empty($masked_publishable)) {
        echo '<br><small>Current: ' . $masked_publishable . '</small>';
    }
    
    echo '<p><strong>Test Secret Key:</strong></p>';
    echo '<input type="password" name="stripe_test_secret_key" value="' . esc_attr($test_secret) . '" style="width: 400px;" placeholder="sk_test_..." />';
    if (!empty($masked_secret)) {
        echo '<br><small>Current: ' . $masked_secret . '</small>';
    }
}

function stripe_live_keys_callback() {
    $live_publishable = get_option('stripe_live_publishable_key', '');
    $live_secret = get_option('stripe_live_secret_key', '');
    
    // Mask the keys for display
    $masked_publishable = !empty($live_publishable) ? substr($live_publishable, 0, 8) . '...' . substr($live_publishable, -4) : '';
    $masked_secret = !empty($live_secret) ? substr($live_secret, 0, 8) . '...' . substr($live_secret, -4) : '';
    
    echo '<p><strong>Live Publishable Key:</strong></p>';
    echo '<input type="text" name="stripe_live_publishable_key" value="' . esc_attr($live_publishable) . '" style="width: 400px;" placeholder="pk_live_..." />';
    if (!empty($masked_publishable)) {
        echo '<br><small>Current: ' . $masked_publishable . '</small>';
    }
    
    echo '<p><strong>Live Secret Key:</strong></p>';
    echo '<input type="password" name="stripe_live_secret_key" value="' . esc_attr($live_secret) . '" style="width: 400px;" placeholder="sk_live_..." />';
    if (!empty($masked_secret)) {
        echo '<br><small>Current: ' . $masked_secret . '</small>';
    }
}

function stripe_webhook_url_callback() {
    $webhook_url = home_url('/wp-json/webhook/v1/payment');
    echo '<input type="text" value="' . esc_attr($webhook_url) . '" readonly style="width: 400px;" />';
    echo '<p><small>Use this URL in your Stripe webhook configuration.</small></p>';
}

// Settings page HTML
function stripe_settings_page() {
    ?>
    <div class="wrap">
        <h1>Stripe Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('stripe_settings_group');
            do_settings_sections('stripe-settings');
            ?>
            <p class="submit">
                <input type="submit" class="button-primary" value="Save Settings" />
                <button type="button" class="button" onclick="testStripeConnection()">Test Connection</button>
            </p>
        </form>
        
        <div id="stripe-test-result" style="margin-top: 20px;"></div>
    </div>
    
    <script>
    function testStripeConnection() {
        var resultDiv = document.getElementById('stripe-test-result');
        resultDiv.innerHTML = '<p>Testing connection...</p>';
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=test_stripe_connection&nonce=<?php echo wp_create_nonce('test_stripe_connection'); ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="notice notice-success"><p>' + data.data.message + '</p></div>';
            } else {
                resultDiv.innerHTML = '<div class="notice notice-error"><p>' + data.data.message + '</p></div>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<div class="notice notice-error"><p>Error testing connection: ' + error.message + '</p></div>';
        });
    }
    </script>
    <?php
}

// AJAX handler for testing Stripe connection
add_action('wp_ajax_test_stripe_connection', 'test_stripe_connection_handler');

function test_stripe_connection_handler() {
    if (!wp_verify_nonce($_POST['nonce'], 'test_stripe_connection')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied');
    }
    
    $secret_key = get_stripe_secret_key();
    
    if (empty($secret_key)) {
        wp_send_json_error(array('message' => 'No API key configured'));
        return;
    }
    
    $test_result = test_stripe_api_connection($secret_key);
    
    if ($test_result['success']) {
        wp_send_json_success(array('message' => 'Connection successful! ' . $test_result['message']));
    } else {
        wp_send_json_error(array('message' => 'Connection failed: ' . $test_result['message']));
    }
}

// Helper function to get the current secret key based on mode
function get_stripe_secret_key() {
    $test_mode = get_option('stripe_test_mode', 1);
    
    if ($test_mode) {
        return get_option('stripe_test_secret_key', '');
    } else {
        return get_option('stripe_live_secret_key', '');
    }
}

// Helper function to get the current publishable key based on mode
function get_stripe_publishable_key() {
    $test_mode = get_option('stripe_test_mode', 1);
    
    if ($test_mode) {
        return get_option('stripe_test_publishable_key', '');
    } else {
        return get_option('stripe_live_publishable_key', '');
    }
}

// Test Stripe API connection
function test_stripe_api_connection($secret_key) {
    $url = 'https://api.stripe.com/v1/customers?limit=1';
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ),
        'timeout' => 30
    );
    
    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code === 200) {
        $test_mode = get_option('stripe_test_mode', 1);
        $mode_text = $test_mode ? 'Test Mode' : 'Live Mode';
        return array(
            'success' => true,
            'message' => $mode_text . ' - API key is valid'
        );
    } else {
        $error_data = json_decode($response_body, true);
        $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown error';
        return array(
            'success' => false,
            'message' => $error_message
        );
    }
}

// Function to fetch customer data from Stripe
function fetch_stripe_customer($customer_id) {
    $secret_key = get_stripe_secret_key();
    
    if (empty($secret_key)) {
        error_log('Stripe secret key not configured');
        return false;
    }
    
    $url = 'https://api.stripe.com/v1/customers/' . $customer_id;
    
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $secret_key,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ),
        'timeout' => 30
    );
    
    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        error_log('Stripe API error: ' . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    if ($response_code === 200) {
        $customer_data = json_decode($response_body, true);
        return $customer_data;
    } else {
        error_log('Stripe API error: HTTP ' . $response_code . ' - ' . $response_body);
        return false;
    }
}

// Add ACF fields
add_action('acf/include_fields', function() {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }
    
    acf_add_local_field_group(array(
        'key' => 'group_payment_fields_' . wp_generate_uuid4(),
        'title' => 'Payment fields',
        'fields' => array(
            array(
                'key' => 'field_payment_id_' . wp_generate_uuid4(),
                'label' => 'Payment ID',
                'name' => 'payment_id',
                'type' => 'text',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_email_' . wp_generate_uuid4(),
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_amount_' . wp_generate_uuid4(),
                'label' => 'Amount',
                'name' => 'amount',
                'type' => 'number',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '',
                'min' => '',
                'max' => '',
                'step' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_payment_date_' . wp_generate_uuid4(),
                'label' => 'Payment Date',
                'name' => 'payment_date',
                'type' => 'date_picker',
                'required' => 0,
                'display_format' => 'd/m/Y',
                'return_format' => 'd/m/Y',
                'first_day' => 1,
            ),
            array(
                'key' => 'field_product_' . wp_generate_uuid4(),
                'label' => 'Product',
                'name' => 'product',
                'type' => 'text',
                'required' => 0,
                'default_value' => '',
                'maxlength' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_stripe_event_id_' . wp_generate_uuid4(),
                'label' => 'Stripe Event ID',
                'name' => 'stripe_event_id',
                'type' => 'text',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_customer_id_' . wp_generate_uuid4(),
                'label' => 'Customer ID',
                'name' => 'customer_id',
                'type' => 'text',
                'required' => 0,
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_payment_status_' . wp_generate_uuid4(),
                'label' => 'Payment Status',
                'name' => 'payment_status',
                'type' => 'select',
                'required' => 0,
                'choices' => array(
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                    'failed' => 'Failed',
                    'refunded' => 'Refunded',
                    'canceled' => 'Canceled'
                ),
                'default_value' => 'paid',
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_invoice_pdf_' . wp_generate_uuid4(),
                'label' => 'Invoice PDF',
                'name' => 'invoice_pdf',
                'type' => 'url',
                'required' => 0,
                'default_value' => '',
                'placeholder' => 'https://example.com/invoice.pdf',
                'prepend' => '',
                'append' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'payment',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ));
});

// Display payment details above content for payment post type
function display_payment_details($content) {
    if (is_singular('payment') && in_the_loop() && is_main_query()) {
        global $post;
        
        // Get payment data
        $payment_id = get_field('payment_id', $post->ID);
        $email = get_field('email', $post->ID);
        $amount = get_field('amount', $post->ID);
        $payment_date = get_field('payment_date', $post->ID);
        $product = get_field('product', $post->ID);
        $stripe_event_id = get_field('stripe_event_id', $post->ID);
        $customer_id = get_field('customer_id', $post->ID);
        $payment_status = get_field('payment_status', $post->ID);
        $invoice_pdf = get_field('invoice_pdf', $post->ID);
        
        // Build the payment details display
        $payment_details = '<div class="payment-details" style="background: #FFFFFF00; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin-bottom: 20px; font-family: Arial, sans-serif;">';
        $payment_details .= '<h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px;">Payment Details</h3>';
        
        $payment_details .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">';
        
        // Left column
        $payment_details .= '<div>';
        if (!empty($payment_id)) {
            $payment_details .= '<p><strong>Payment ID:</strong> <span style="font-family: monospace; background: #fff; padding: 2px 6px; border-radius: 3px;">' . esc_html($payment_id) . '</span></p>';
        }
        if (!empty($email)) {
            $payment_details .= '<p><strong>Customer Email:</strong> ' . esc_html($email) . '</p>';
        }
        if (!empty($amount)) {
            $payment_details .= '<p><strong>Amount:</strong> <span style="color: #28a745; font-weight: bold;">$' . number_format($amount, 2) . '</span></p>';
        }
        if (!empty($payment_date)) {
            $payment_details .= '<p><strong>Payment Date:</strong> ' . esc_html($payment_date) . '</p>';
        }
        if (!empty($product)) {
            $payment_details .= '<p><strong>Product:</strong> ' . esc_html($product) . '</p>';
        }
        $payment_details .= '</div>';
        
        // Right column
        $payment_details .= '<div>';
        if (!empty($payment_status)) {
            $status_color = '';
            switch (strtolower($payment_status)) {
                case 'paid':
                    $status_color = '#28a745';
                    break;
                case 'pending':
                    $status_color = '#ffc107';
                    break;
                case 'failed':
                    $status_color = '#dc3545';
                    break;
                case 'refunded':
                    $status_color = '#6c757d';
                    break;
                case 'canceled':
                    $status_color = '#6c757d';
                    break;
                default:
                    $status_color = '#007cba';
            }
            $payment_details .= '<p><strong>Status:</strong> <span style="color: ' . $status_color . '; font-weight: bold; text-transform: capitalize;">' . esc_html($payment_status) . '</span></p>';
        }
        if (!empty($customer_id)) {
            $payment_details .= '<p><strong>Customer ID:</strong> <span style="font-family: monospace; font-size: 0.9em;">' . esc_html($customer_id) . '</span></p>';
        }
        if (!empty($stripe_event_id)) {
            $payment_details .= '<p><strong>Stripe Event ID:</strong> <span style="font-family: monospace; font-size: 0.9em;">' . esc_html($stripe_event_id) . '</span></p>';
        }
        if (!empty($invoice_pdf)) {
            $payment_details .= '<p><strong>Invoice:</strong> <a href="' . esc_url($invoice_pdf) . '" target="_blank" style="color: #007cba; text-decoration: none;">View PDF</a></p>';
        }
        $payment_details .= '</div>';
        
        $payment_details .= '</div>'; // Close grid
        $payment_details .= '</div>'; // Close payment-details div
        
        // Prepend payment details to content
        $content = $payment_details . $content;
    }
    
    return $content;
}
add_filter('the_content', 'display_payment_details');

// Create the webhook endpoint
add_action('rest_api_init', function () {
    register_rest_route('webhook/v1', '/payment', array(
        'methods' => 'POST',
        'callback' => 'handle_payment_webhook',
        'permission_callback' => '__return_true',
    ));
});

// Handle the webhook data
function handle_payment_webhook($request) {
    $body = $request->get_body();
    $data = json_decode($body, true);
    
    // Log the webhook data for debugging
    error_log('Webhook received: ' . $body);
    
    // Check if this is a Stripe webhook
    $is_stripe_webhook = isset($data['object']) && $data['object'] === 'event';
    
    if ($is_stripe_webhook) {
        // Handle Stripe webhook format
        $event_type = isset($data['type']) ? $data['type'] : '';
        
        // Only process specific payment success events
        $allowed_events = array(
            'invoice.payment_succeeded',    // Subscription payments
            'charge.succeeded',             // All successful charges (has email)
            'payment_intent.succeeded'      // One-time payments
        );
        
        if (!in_array($event_type, $allowed_events)) {
            return new WP_REST_Response(array(
                'message' => 'Event type not processed: ' . $event_type,
                'processed' => false
            ), 200);
        }
        
        $event_data = $data['data']['object'];
        
        // Initialize variables
        $payment_id = '';
        $email = '';
        $amount = 0;
        $payment_date = '';
        $payment_status = 'paid';
        $invoice_pdf = '';
        $product = '';
        $customer_id = '';
        $stripe_event_id = isset($data['id']) ? sanitize_text_field($data['id']) : '';
        
        // Extract data based on event type
        if ($event_type === 'charge.succeeded') {
            $payment_id = isset($event_data['id']) ? sanitize_text_field($event_data['id']) : '';
            $email = isset($event_data['billing_details']['email']) ? sanitize_email($event_data['billing_details']['email']) : '';
            $amount = isset($event_data['amount']) ? floatval($event_data['amount']) / 100 : 0;
            $payment_date = isset($event_data['created']) ? date('d/m/Y', $event_data['created']) : date('d/m/Y');
            $payment_status = isset($event_data['status']) ? sanitize_text_field($event_data['status']) : 'paid';
            $invoice_pdf = isset($event_data['receipt_url']) ? esc_url($event_data['receipt_url']) : '';
            $customer_id = isset($event_data['customer']) ? sanitize_text_field($event_data['customer']) : '';
            
            // Get product from description or metadata
            $product = 'Payment';
            if (isset($event_data['description'])) {
                $product = sanitize_text_field($event_data['description']);
            } elseif (isset($event_data['metadata']['product_name'])) {
                $product = sanitize_text_field($event_data['metadata']['product_name']);
            }
            
        } elseif ($event_type === 'invoice.payment_succeeded') {
            $payment_id = isset($event_data['id']) ? sanitize_text_field($event_data['id']) : '';
            $amount = isset($event_data['amount_paid']) ? floatval($event_data['amount_paid']) / 100 : 0;
            $payment_date = isset($event_data['created']) ? date('d/m/Y', $event_data['created']) : date('d/m/Y');
            $payment_status = isset($event_data['status']) ? sanitize_text_field($event_data['status']) : 'paid';
            $invoice_pdf = isset($event_data['invoice_pdf']) ? esc_url($event_data['invoice_pdf']) : '';
            $customer_id = isset($event_data['customer']) ? sanitize_text_field($event_data['customer']) : '';
            
            // Get email from customer_email field if available
            if (isset($event_data['customer_email'])) {
                $email = sanitize_email($event_data['customer_email']);
            }
            
            // Get product name from line items
            $product = 'Subscription';
            if (isset($event_data['lines']['data'][0]['price']['nickname'])) {
                $product = sanitize_text_field($event_data['lines']['data'][0]['price']['nickname']);
            } elseif (isset($event_data['lines']['data'][0]['description'])) {
                $product = sanitize_text_field($event_data['lines']['data'][0]['description']);
            }
            
        } elseif ($event_type === 'payment_intent.succeeded') {
            $payment_id = isset($event_data['id']) ? sanitize_text_field($event_data['id']) : '';
            $amount = isset($event_data['amount']) ? floatval($event_data['amount']) / 100 : 0;
            $payment_date = isset($event_data['created']) ? date('d/m/Y', $event_data['created']) : date('d/m/Y');
            $payment_status = isset($event_data['status']) ? sanitize_text_field($event_data['status']) : 'paid';
            $customer_id = isset($event_data['customer']) ? sanitize_text_field($event_data['customer']) : '';
            
            // Get email from billing details if available
            if (isset($event_data['charges']['data'][0]['billing_details']['email'])) {
                $email = sanitize_email($event_data['charges']['data'][0]['billing_details']['email']);
            }
            
            // Get product from description or metadata
            $product = 'Payment';
            if (isset($event_data['description'])) {
                $product = sanitize_text_field($event_data['description']);
            } elseif (isset($event_data['metadata']['product_name'])) {
                $product = sanitize_text_field($event_data['metadata']['product_name']);
            }
        }
        
        // If we don't have email but have customer_id, fetch customer data from Stripe
        if (empty($email) && !empty($customer_id)) {
            error_log('No email found in webhook, attempting to fetch customer data for: ' . $customer_id);
            
            $customer_data = fetch_stripe_customer($customer_id);
            if ($customer_data && isset($customer_data['email'])) {
                $email = sanitize_email($customer_data['email']);
                error_log('Successfully fetched customer email: ' . $email);
            } else {
                error_log('Failed to fetch customer data for customer_id: ' . $customer_id);
            }
        }
        
    } else {
        // Handle direct JSON format (for testing)
        $payment_id = isset($data['payment_id']) ? sanitize_text_field($data['payment_id']) : '';
        $email = isset($data['email']) ? sanitize_email($data['email']) : '';
        $amount = isset($data['amount']) ? floatval($data['amount']) : 0;
        $payment_date = isset($data['payment_date']) ? sanitize_text_field($data['payment_date']) : date('d/m/Y');
        $product = isset($data['product']) ? sanitize_text_field($data['product']) : '';
        $stripe_event_id = '';
        $customer_id = isset($data['customer_id']) ? sanitize_text_field($data['customer_id']) : '';
        $payment_status = isset($data['payment_status']) ? sanitize_text_field($data['payment_status']) : 'paid';
        $invoice_pdf = isset($data['invoice_pdf']) ? esc_url($data['invoice_pdf']) : '';
    }
    
    if (empty($payment_id)) {
        return new WP_Error('missing_payment_id', 'Payment ID is required', array('status' => 400));
    }
    
    // Check if payment already exists to prevent duplicates
    $existing_posts = get_posts(array(
        'post_type' => 'payment',
        'meta_query' => array(
            array(
                'key' => 'payment_id',
                'value' => $payment_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    if (!empty($existing_posts)) {
        return new WP_REST_Response(array(
            'message' => 'Payment already exists',
            'post_id' => $existing_posts[0]->ID
        ), 200);
    }
    
    // Create post title: Product - Payment ID
    $post_title = $product . ' - ' . $payment_id;
    
    // Create the payment post
    $post_id = wp_insert_post(array(
        'post_title' => $post_title,
        'post_type' => 'payment',
        'post_status' => 'publish',
        'post_content' => 'Payment processed via webhook'
    ));
    
    if (is_wp_error($post_id)) {
        return new WP_Error('post_creation_failed', 'Failed to create payment post', array('status' => 500));
    }
    
    // Update ACF fields
    update_field('payment_id', $payment_id, $post_id);
    update_field('email', $email, $post_id);
    update_field('amount', $amount, $post_id);
    update_field('payment_date', $payment_date, $post_id);
    update_field('product', $product, $post_id);
    update_field('payment_status', $payment_status, $post_id);
    
    if (!empty($stripe_event_id)) {
        update_field('stripe_event_id', $stripe_event_id, $post_id);
    }
    
    if (!empty($customer_id)) {
        update_field('customer_id', $customer_id, $post_id);
    }
    
    if (!empty($invoice_pdf)) {
        update_field('invoice_pdf', $invoice_pdf, $post_id);
    }
    
    return new WP_REST_Response(array(
        'message' => 'Payment created successfully',
        'post_id' => $post_id,
        'email_fetched' => !empty($email) ? 'yes' : 'no'
    ), 201);
}
