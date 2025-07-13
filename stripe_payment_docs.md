# WordPress Stripe Payment Integration Documentation

## Overview

This WordPress plugin provides a complete integration with Stripe for processing payments and managing payment records. It creates a custom post type for payments, provides an admin interface for Stripe configuration, handles webhook processing, and automatically creates payment records from Stripe events.

## Features

- **Custom Payment Post Type**: Dedicated post type for managing payment records
- **Stripe API Configuration**: Admin interface for managing test/live API keys
- **Webhook Processing**: Automated payment record creation from Stripe webhooks
- **ACF Integration**: Custom fields for storing payment data
- **Connection Testing**: Built-in API connection testing
- **Duplicate Prevention**: Prevents duplicate payment records
- **Customer Data Fetching**: Automatically retrieves customer information from Stripe

## Installation & Setup

### Prerequisites

- WordPress 5.0 or higher
- Advanced Custom Fields (ACF) plugin
- Active Stripe account
- PHP 7.4 or higher

### Installation Steps

1. **Install the Plugin**
   ```php
   // Add this code to your theme's functions.php or create a custom plugin
   // Save the provided code as a .php file in your plugins directory
   ```

2. **Activate ACF Plugin**
   - Ensure Advanced Custom Fields is installed and activated
   - The plugin will automatically create the necessary custom fields

3. **Configure Stripe Settings**
   - Navigate to **Settings > Stripe Settings** in your WordPress admin
   - Configure your API keys and webhook settings

## Configuration

### Stripe API Configuration

#### Test Mode Setup
1. Go to **Settings > Stripe Settings**
2. Check "Enable Test Mode"
3. Enter your test API keys:
   - **Test Publishable Key**: `pk_test_...`
   - **Test Secret Key**: `sk_test_...`

#### Live Mode Setup
1. Uncheck "Enable Test Mode"
2. Enter your live API keys:
   - **Live Publishable Key**: `pk_live_...`
   - **Live Secret Key**: `sk_live_...`

#### Webhook Configuration
1. Copy the webhook URL from the settings page: `https://yoursite.com/wp-json/webhook/v1/payment`
2. In your Stripe Dashboard:
   - Go to **Developers > Webhooks**
   - Click "Add endpoint"
   - Paste the webhook URL
   - Select the following events:
     - `charge.succeeded`
     - `invoice.payment_succeeded`
     - `payment_intent.succeeded`

### Testing Connection

Use the "Test Connection" button in the Stripe Settings to verify your API configuration:

```javascript
// The test function sends a request to Stripe's customers endpoint
// Success response indicates valid API keys and proper connection
```

## Usage

### Custom Post Type: Payments

The plugin creates a custom post type called "Payments" with the following features:

- **Public**: Accessible via frontend
- **Admin UI**: Full admin interface
- **Custom Fields**: Integrated ACF fields
- **REST API**: Enabled for API access
- **Menu Icon**: Money icon in admin menu
- **URL Slug**: `/payments/`

### Payment Fields

Each payment record includes the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `payment_id` | Text | Unique Stripe payment identifier |
| `email` | Email | Customer email address |
| `amount` | Number | Payment amount (in dollars) |
| `payment_date` | Date | Date of payment (d/m/Y format) |
| `product` | Text | Product or service name |
| `stripe_event_id` | Text | Stripe webhook event ID |
| `customer_id` | Text | Stripe customer ID |
| `payment_status` | Select | Payment status (paid, pending, failed, etc.) |
| `invoice_pdf` | URL | Link to invoice PDF |

### Webhook Processing

The plugin automatically processes three types of Stripe webhook events:

#### 1. Charge Succeeded (`charge.succeeded`)
```json
{
  "id": "evt_1234567890",
  "object": "event",
  "type": "charge.succeeded",
  "data": {
    "object": {
      "id": "ch_1234567890",
      "amount": 2000,
      "billing_details": {
        "email": "customer@example.com"
      },
      "created": 1640995200,
      "description": "Premium Plan",
      "receipt_url": "https://pay.stripe.com/receipts/...",
      "customer": "cus_1234567890"
    }
  }
}
```

#### 2. Invoice Payment Succeeded (`invoice.payment_succeeded`)
```json
{
  "id": "evt_1234567890",
  "object": "event",
  "type": "invoice.payment_succeeded",
  "data": {
    "object": {
      "id": "in_1234567890",
      "amount_paid": 2000,
      "customer_email": "customer@example.com",
      "invoice_pdf": "https://pay.stripe.com/invoice/...",
      "lines": {
        "data": [{
          "price": {
            "nickname": "Monthly Subscription"
          }
        }]
      }
    }
  }
}
```

#### 3. Payment Intent Succeeded (`payment_intent.succeeded`)
```json
{
  "id": "evt_1234567890",
  "object": "event",
  "type": "payment_intent.succeeded",
  "data": {
    "object": {
      "id": "pi_1234567890",
      "amount": 2000,
      "charges": {
        "data": [{
          "billing_details": {
            "email": "customer@example.com"
          }
        }]
      },
      "customer": "cus_1234567890"
    }
  }
}
```

### Manual Testing

For testing purposes, you can send direct JSON to the webhook endpoint:

```bash
curl -X POST https://yoursite.com/wp-json/webhook/v1/payment \
  -H "Content-Type: application/json" \
  -d '{
    "payment_id": "test_payment_123",
    "email": "test@example.com",
    "amount": 29.99,
    "payment_date": "15/01/2025",
    "product": "Test Product",
    "payment_status": "paid"
  }'
```

## API Reference

### Helper Functions

#### `get_stripe_secret_key()`
```php
/**
 * Returns the appropriate secret key based on test/live mode
 * @return string Secret key
 */
$secret_key = get_stripe_secret_key();
```

#### `get_stripe_publishable_key()`
```php
/**
 * Returns the appropriate publishable key based on test/live mode
 * @return string Publishable key
 */
$publishable_key = get_stripe_publishable_key();
```

#### `fetch_stripe_customer($customer_id)`
```php
/**
 * Fetches customer data from Stripe API
 * @param string $customer_id Stripe customer ID
 * @return array|false Customer data or false on error
 */
$customer = fetch_stripe_customer('cus_1234567890');
if ($customer) {
    $email = $customer['email'];
    $name = $customer['name'];
}
```

#### `test_stripe_api_connection($secret_key)`
```php
/**
 * Tests Stripe API connection
 * @param string $secret_key Stripe secret key
 * @return array Result with success status and message
 */
$result = test_stripe_api_connection($secret_key);
if ($result['success']) {
    echo "Connection successful: " . $result['message'];
}
```

### REST API Endpoints

#### Payment Webhook
- **Endpoint**: `POST /wp-json/webhook/v1/payment`
- **Purpose**: Receive Stripe webhook events
- **Authentication**: None (public endpoint)
- **Response**: JSON with creation status

### AJAX Endpoints

#### Test Stripe Connection
- **Action**: `test_stripe_connection`
- **Method**: POST
- **Nonce**: `test_stripe_connection`
- **Permission**: `manage_options`

## Code Examples

### Creating a Payment Record Programmatically

```php
// Create a new payment record
$payment_data = array(
    'payment_id' => 'ch_1234567890',
    'email' => 'customer@example.com',
    'amount' => 29.99,
    'payment_date' => date('d/m/Y'),
    'product' => 'Premium Plan',
    'payment_status' => 'paid'
);

$post_id = wp_insert_post(array(
    'post_title' => $payment_data['product'] . ' - ' . $payment_data['payment_id'],
    'post_type' => 'payment',
    'post_status' => 'publish'
));

if (!is_wp_error($post_id)) {
    foreach ($payment_data as $field => $value) {
        update_field($field, $value, $post_id);
    }
}
```

### Querying Payment Records

```php
// Get all payments for a specific customer
$payments = get_posts(array(
    'post_type' => 'payment',
    'meta_query' => array(
        array(
            'key' => 'email',
            'value' => 'customer@example.com',
            'compare' => '='
        )
    ),
    'posts_per_page' => -1
));

foreach ($payments as $payment) {
    $amount = get_field('amount', $payment->ID);
    $date = get_field('payment_date', $payment->ID);
    $product = get_field('product', $payment->ID);
    
    echo "Payment: {$product} - \${$amount} on {$date}\n";
}
```

### Custom Payment Status Update

```php
// Update payment status
function update_payment_status($payment_id, $new_status) {
    $posts = get_posts(array(
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
    
    if (!empty($posts)) {
        $post_id = $posts[0]->ID;
        update_field('payment_status', $new_status, $post_id);
        return true;
    }
    
    return false;
}

// Usage
update_payment_status('ch_1234567890', 'refunded');
```

## Error Handling

The plugin includes comprehensive error handling:

### Webhook Errors
- **Missing Payment ID**: Returns 400 error
- **Duplicate Payment**: Returns 200 with existing post ID
- **Post Creation Failed**: Returns 500 error

### API Connection Errors
- **Invalid API Key**: Returns error message from Stripe
- **Network Errors**: Returns WordPress error message
- **Timeout**: 30-second timeout for API calls

### Security Features
- **Nonce Verification**: All AJAX requests include nonce verification
- **Capability Checks**: Admin functions require `manage_options` capability
- **Data Sanitization**: All input data is sanitized using WordPress functions
- **SQL Injection Prevention**: Uses WordPress meta queries

## Troubleshooting

### Common Issues

1. **Webhook Not Receiving Data**
   - Check webhook URL in Stripe dashboard
   - Verify SSL certificate on your site
   - Check server logs for errors

2. **API Connection Fails**
   - Verify API keys are correct
   - Check test/live mode setting
   - Ensure server can make outbound HTTPS requests

3. **Duplicate Payment Records**
   - The plugin prevents duplicates automatically
   - Check for multiple webhook endpoints in Stripe

4. **Missing Customer Email**
   - Plugin automatically fetches customer data if email is missing
   - Check Stripe customer records for email addresses

### Debug Mode

Enable WordPress debug mode to see detailed error logs:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `/wp-content/debug.log` for webhook and API errors.

## Customization

### Custom Field Groups

To add additional fields to the payment post type:

```php
// Add custom field to existing field group
add_action('acf/include_fields', function() {
    if (function_exists('acf_add_local_field')) {
        acf_add_local_field(array(
            'key' => 'field_custom_note',
            'label' => 'Custom Note',
            'name' => 'custom_note',
            'type' => 'textarea',
            'parent' => 'group_payment_fields_' . wp_generate_uuid4(),
        ));
    }
});
```

### Custom Webhook Events

To handle additional Stripe events:

```php
// Modify the allowed_events array in handle_payment_webhook function
$allowed_events = array(
    'invoice.payment_succeeded',
    'charge.succeeded',
    'payment_intent.succeeded',
    'subscription.created',  // Add new event
    'customer.created'       // Add new event
);
```

### Custom Post Type Modifications

```php
// Modify the post type registration
add_filter('register_payment_post_type_args', function($args) {
    $args['public'] = false;  // Make private
    $args['show_in_rest'] = false;  // Disable REST API
    return $args;
});
```

## Security Considerations

1. **API Key Protection**: Keys are stored in WordPress options and masked in the admin interface
2. **Webhook Verification**: Consider implementing Stripe webhook signature verification
3. **Rate Limiting**: Implement rate limiting for webhook endpoints
4. **Data Validation**: All webhook data is sanitized before storage
5. **Access Control**: Admin functions require proper capabilities

## Performance Optimization

1. **Caching**: Consider caching Stripe customer data
2. **Database Indexes**: Add indexes for frequently queried meta fields
3. **Batch Processing**: For high-volume sites, consider batch processing webhooks
4. **Logging**: Implement structured logging for better debugging

## License & Support

This plugin is provided as-is for educational and development purposes. For production use, consider:

- Adding comprehensive error handling
- Implementing webhook signature verification
- Adding automated testing
- Following WordPress coding standards
- Adding proper documentation headers

Remember to test thoroughly in a staging environment before deploying to production.