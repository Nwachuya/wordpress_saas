<?php
/**
 * Color Combination Picker Shortcode
 * 
 * Usage: [color_picker] or [color_picker default="blue"]
 * 
 * Fetches color data from https://color.serialif.com/ API
 * and displays all color combinations with visual swatches
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register the shortcode
add_shortcode('color_picker', 'ccp_color_picker_shortcode');

// Register AJAX handlers for both logged-in and non-logged-in users
add_action('wp_ajax_ccp_fetch_color', 'ccp_fetch_color_ajax');
add_action('wp_ajax_nopriv_ccp_fetch_color', 'ccp_fetch_color_ajax');

/**
 * AJAX handler to fetch color data from API (server-side proxy to avoid CORS)
 */
function ccp_fetch_color_ajax() {
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ccp_color_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
        wp_die();
    }
    
    // Get and sanitize the color value
    $color = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : '';
    
    if (empty($color)) {
        wp_send_json_error(array('message' => 'No color provided.'));
        wp_die();
    }
    
    // Clean the color value - remove # if present
    $color = ltrim($color, '#');
    
    // Build API URL
    $api_url = 'https://color.serialif.com/' . urlencode($color);
    
    // Make the request from server side
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to connect to color API: ' . $response->get_error_message()));
        wp_die();
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($response_code !== 200) {
        wp_send_json_error(array('message' => 'Color not found. Please try a valid color name or hex value.'));
        wp_die();
    }
    
    // Decode and validate JSON
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['status'])) {
        wp_send_json_error(array('message' => 'Invalid response from color API.'));
        wp_die();
    }
    
    if ($data['status'] !== 'success') {
        $error_msg = isset($data['message']) ? $data['message'] : 'Unknown error from color API.';
        wp_send_json_error(array('message' => $error_msg));
        wp_die();
    }
    
    // Success - return the color data
    wp_send_json_success($data);
    wp_die();
}

/**
 * Main shortcode function
 */
function ccp_color_picker_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'default' => 'orange',
        ),
        $atts,
        'color_picker'
    );
    
    $default_color = sanitize_text_field($atts['default']);
    
    // Generate unique ID for multiple instances on same page
    $instance_id = 'ccp-' . uniqid();
    
    // Generate nonce for security
    $nonce = wp_create_nonce('ccp_color_nonce');
    
    ob_start();
    ?>
    
    <div class="ccp-app" 
         id="<?php echo esc_attr($instance_id); ?>" 
         data-default="<?php echo esc_attr($default_color); ?>"
         data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
         data-nonce="<?php echo esc_attr($nonce); ?>">
        
        <!-- Header Section -->
        <div class="ccp-header">
            <h2 class="ccp-title">Color Palette Generator</h2>
            <p class="ccp-subtitle">Pick a color or enter a name to generate complementary palettes</p>
        </div>
        
        <!-- Color Input Section -->
        <div class="ccp-input-card">
            <div class="ccp-input-row">
                <div class="ccp-picker-wrapper">
                    <input 
                        type="color" 
                        id="<?php echo esc_attr($instance_id); ?>-picker" 
                        class="ccp-color-picker" 
                        value="#ffa500"
                    >
                    <div class="ccp-picker-ring"></div>
                </div>
                <div class="ccp-text-wrapper">
                    <input 
                        type="text" 
                        id="<?php echo esc_attr($instance_id); ?>-text" 
                        class="ccp-color-text" 
                        placeholder="Enter color name or hex..."
                        value="<?php echo esc_attr($default_color); ?>"
                        spellcheck="false"
                        autocomplete="off"
                    >
                    <div class="ccp-input-hint">Try: coral, steelblue, #ff6b6b</div>
                </div>
            </div>
            
            <!-- Status indicator -->
            <div class="ccp-status">
                <div class="ccp-status-dot"></div>
                <span class="ccp-status-text">Ready</span>
            </div>
        </div>
        
        <!-- Error Message -->
        <div class="ccp-error" style="display: none;">
            <svg class="ccp-error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span class="ccp-error-text"></span>
        </div>
        
        <!-- Results Section -->
        <div class="ccp-results">
            
            <!-- Base Color -->
            <div class="ccp-color-group" data-group="base">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Base Color</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
            <!-- Complementary Color -->
            <div class="ccp-color-group" data-group="complementary">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Complementary</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
            <!-- Grayscale -->
            <div class="ccp-color-group" data-group="grayscale">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Grayscale</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
            <!-- Base Contrasted Text -->
            <div class="ccp-color-group" data-group="base_without_alpha_contrasted_text">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Base Text</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
            <!-- Complementary Contrasted Text -->
            <div class="ccp-color-group" data-group="complementary_without_alpha_contrasted_text">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Complementary Text</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
            <!-- Grayscale Contrasted Text -->
            <div class="ccp-color-group" data-group="grayscale_without_alpha_contrasted_text">
                <div class="ccp-color-card">
                    <div class="ccp-swatch">
                        <div class="ccp-swatch-inner"></div>
                    </div>
                    <div class="ccp-card-content">
                        <div class="ccp-card-header">
                            <span class="ccp-card-label">Grayscale Text</span>
                            <span class="ccp-card-name"></span>
                        </div>
                        <div class="ccp-values"></div>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Global tooltip container (appended to body via JS) -->
        
    </div>
    
    <?php
    
    // Only add styles and scripts once per page
    static $assets_loaded = false;
    if (!$assets_loaded) {
        $assets_loaded = true;
        ?>
        
        <!-- Google Fonts - Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <style>
            .ccp-app {
                --ccp-primary: #6366f1;
                --ccp-primary-light: #818cf8;
                --ccp-bg: #ffffff;
                --ccp-card-bg: #ffffff;
                --ccp-input-bg: #f8fafc;
                --ccp-text: #1e293b;
                --ccp-text-muted: #64748b;
                --ccp-border: #e2e8f0;
                --ccp-success: #10b981;
                --ccp-error: #ef4444;
                --ccp-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                --ccp-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
                --ccp-radius: 16px;
                --ccp-radius-sm: 10px;
                
                font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
                max-width: 900px;
                margin: 0 auto;
                padding: 30px 20px;
                background: var(--ccp-bg);
                border-radius: var(--ccp-radius);
                position: relative;
            }
            
            .ccp-app * {
                box-sizing: border-box;
            }
            
            /* Header */
            .ccp-header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .ccp-title {
                font-size: 28px;
                font-weight: 700;
                color: var(--ccp-text);
                margin: 0 0 8px 0;
                letter-spacing: -0.5px;
            }
            
            .ccp-subtitle {
                font-size: 15px;
                font-weight: 400;
                color: var(--ccp-text-muted);
                margin: 0;
            }
            
            /* Input Card */
            .ccp-input-card {
                background: var(--ccp-card-bg);
                border-radius: var(--ccp-radius);
                padding: 24px;
                box-shadow: var(--ccp-shadow);
                margin-bottom: 24px;
                border: 1px solid var(--ccp-border);
            }
            
            .ccp-input-row {
                display: flex;
                gap: 20px;
                align-items: center;
            }
            
            .ccp-picker-wrapper {
                position: relative;
                flex-shrink: 0;
            }
            
            .ccp-color-picker {
                width: 64px;
                height: 64px;
                padding: 0;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                background: none;
                position: relative;
                z-index: 2;
            }
            
            .ccp-color-picker::-webkit-color-swatch-wrapper {
                padding: 4px;
            }
            
            .ccp-color-picker::-webkit-color-swatch {
                border: none;
                border-radius: 50%;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .ccp-color-picker::-moz-color-swatch {
                border: none;
                border-radius: 50%;
            }
            
            .ccp-picker-ring {
                position: absolute;
                top: -4px;
                left: -4px;
                right: -4px;
                bottom: -4px;
                border: 3px solid var(--ccp-border);
                border-radius: 50%;
                pointer-events: none;
                transition: border-color 0.3s ease, transform 0.3s ease;
            }
            
            .ccp-picker-wrapper:hover .ccp-picker-ring {
                border-color: var(--ccp-primary-light);
                transform: scale(1.05);
            }
            
            .ccp-text-wrapper {
                flex: 1;
            }
            
            .ccp-color-text {
                width: 100%;
                padding: 16px 20px;
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-weight: 500;
                color: var(--ccp-text);
                background: var(--ccp-input-bg);
                border: 2px solid transparent;
                border-radius: var(--ccp-radius-sm);
                outline: none;
                transition: all 0.3s ease;
            }
            
            .ccp-color-text:focus {
                border-color: var(--ccp-primary);
                background: var(--ccp-card-bg);
                box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            }
            
            .ccp-color-text::placeholder {
                color: var(--ccp-text-muted);
                font-weight: 400;
            }
            
            .ccp-input-hint {
                margin-top: 8px;
                font-size: 12px;
                color: var(--ccp-text-muted);
                padding-left: 4px;
            }
            
            /* Status */
            .ccp-status {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 16px;
                padding-top: 16px;
                border-top: 1px solid var(--ccp-border);
            }
            
            .ccp-status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--ccp-success);
                transition: background 0.3s ease;
            }
            
            .ccp-app.is-loading .ccp-status-dot {
                background: var(--ccp-primary);
                animation: ccp-pulse 1s ease-in-out infinite;
            }
            
            .ccp-app.has-error .ccp-status-dot {
                background: var(--ccp-error);
            }
            
            @keyframes ccp-pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.2); }
            }
            
            .ccp-status-text {
                font-size: 13px;
                font-weight: 500;
                color: var(--ccp-text-muted);
            }
            
            /* Error */
            .ccp-error {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 16px 20px;
                background: #fef2f2;
                border: 1px solid #fecaca;
                border-radius: var(--ccp-radius-sm);
                margin-bottom: 24px;
            }
            
            .ccp-error-icon {
                width: 20px;
                height: 20px;
                color: var(--ccp-error);
                flex-shrink: 0;
            }
            
            .ccp-error-text {
                font-size: 14px;
                font-weight: 500;
                color: #b91c1c;
            }
            
            /* Results Grid */
            .ccp-results {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                opacity: 0;
                transform: translateY(10px);
                transition: opacity 0.4s ease, transform 0.4s ease;
            }
            
            .ccp-app.has-results .ccp-results {
                opacity: 1;
                transform: translateY(0);
            }
            
            /* Color Card */
            .ccp-color-card {
                background: var(--ccp-card-bg);
                border-radius: var(--ccp-radius);
                overflow: hidden;
                box-shadow: var(--ccp-shadow);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid var(--ccp-border);
            }
            
            .ccp-color-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--ccp-shadow-lg);
            }
            
            .ccp-swatch {
                height: 100px;
                position: relative;
                overflow: hidden;
            }
            
            .ccp-swatch-inner {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                transition: background-color 0.4s ease;
            }
            
            .ccp-swatch::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 40px;
                background: linear-gradient(to top, rgba(0,0,0,0.05), transparent);
                pointer-events: none;
            }
            
            .ccp-card-content {
                padding: 16px;
            }
            
            .ccp-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
            }
            
            .ccp-card-label {
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: var(--ccp-text-muted);
            }
            
            .ccp-card-name {
                font-size: 12px;
                font-weight: 500;
                color: var(--ccp-primary);
                text-transform: capitalize;
            }
            
            /* Values */
            .ccp-values {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            
            .ccp-value-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 12px;
            }
            
            .ccp-value-label {
                font-weight: 500;
                color: var(--ccp-text-muted);
                text-transform: uppercase;
                font-size: 10px;
                letter-spacing: 0.3px;
            }
            
            .ccp-value-data {
                font-family: 'SF Mono', Monaco, 'Courier New', monospace;
                font-size: 12px;
                font-weight: 500;
                color: var(--ccp-text);
                padding: 4px 10px;
                background: var(--ccp-input-bg);
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid transparent;
            }
            
            .ccp-value-data:hover {
                background: var(--ccp-primary);
                color: white;
                transform: scale(1.02);
            }
            
            .ccp-value-data.copied {
                background: var(--ccp-success);
                color: white;
                border-color: var(--ccp-success);
            }
            
            /* Global floating tooltip - appended to body */
            .ccp-floating-tooltip {
                position: fixed;
                background: #1e293b;
                color: white;
                font-family: 'Poppins', sans-serif;
                font-size: 12px;
                font-weight: 500;
                padding: 8px 14px;
                border-radius: 8px;
                white-space: nowrap;
                z-index: 999999;
                pointer-events: none;
                opacity: 0;
                transform: translateY(4px);
                transition: opacity 0.2s ease, transform 0.2s ease;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            }
            
            .ccp-floating-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 6px solid transparent;
                border-top-color: #1e293b;
            }
            
            .ccp-floating-tooltip.show {
                opacity: 1;
                transform: translateY(0);
            }
            
            .ccp-floating-tooltip .ccp-tooltip-icon {
                display: inline-block;
                width: 14px;
                height: 14px;
                margin-right: 6px;
                vertical-align: -2px;
            }
            
            /* Loading skeleton */
            .ccp-app.is-loading .ccp-swatch-inner {
                background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
                background-size: 200% 100%;
                animation: ccp-shimmer 1.5s infinite;
            }
            
            @keyframes ccp-shimmer {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .ccp-results {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .ccp-title {
                    font-size: 24px;
                }
            }
            
            @media (max-width: 500px) {
                .ccp-app {
                    padding: 20px 16px;
                }
                
                .ccp-input-row {
                    flex-direction: column;
                    gap: 16px;
                }
                
                .ccp-picker-wrapper {
                    align-self: center;
                }
                
                .ccp-results {
                    grid-template-columns: 1fr;
                }
                
                .ccp-title {
                    font-size: 22px;
                }
                
                .ccp-subtitle {
                    font-size: 14px;
                }
            }
        </style>
        
        <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                
                // Create global tooltip element once
                var globalTooltip = document.createElement('div');
                globalTooltip.className = 'ccp-floating-tooltip';
                globalTooltip.innerHTML = '<svg class="ccp-tooltip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>Copied to clipboard!';
                document.body.appendChild(globalTooltip);
                
                var tooltipTimeout = null;
                
                // Initialize all color picker instances
                var containers = document.querySelectorAll('.ccp-app');
                
                containers.forEach(function(container) {
                    initColorPicker(container);
                });
                
                function showTooltip(element) {
                    clearTimeout(tooltipTimeout);
                    
                    var rect = element.getBoundingClientRect();
                    var tooltipRect = globalTooltip.getBoundingClientRect();
                    
                    // Position above the element, centered
                    var left = rect.left + (rect.width / 2);
                    var top = rect.top - 10;
                    
                    globalTooltip.style.left = left + 'px';
                    globalTooltip.style.top = top + 'px';
                    globalTooltip.style.transform = 'translate(-50%, -100%)';
                    
                    // Show tooltip
                    globalTooltip.classList.add('show');
                    
                    // Hide after delay
                    tooltipTimeout = setTimeout(function() {
                        globalTooltip.classList.remove('show');
                    }, 1500);
                }
                
                function initColorPicker(container) {
                    var picker = container.querySelector('.ccp-color-picker');
                    var textInput = container.querySelector('.ccp-color-text');
                    var statusText = container.querySelector('.ccp-status-text');
                    var errorDiv = container.querySelector('.ccp-error');
                    var errorText = container.querySelector('.ccp-error-text');
                    var results = container.querySelector('.ccp-results');
                    var defaultColor = container.getAttribute('data-default');
                    var ajaxUrl = container.getAttribute('data-ajax-url');
                    var nonce = container.getAttribute('data-nonce');
                    
                    var debounceTimer = null;
                    var currentRequest = null;
                    var lastFetchedColor = null;
                    
                    // Debounced fetch function
                    function debouncedFetch(delay) {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function() {
                            var colorValue = textInput.value.trim();
                            if (colorValue && colorValue !== lastFetchedColor) {
                                fetchColorData();
                            }
                        }, delay);
                    }
                    
                    // Color picker change - immediate fetch
                    picker.addEventListener('input', function() {
                        textInput.value = this.value;
                        debouncedFetch(300);
                    });
                    
                    // Text input change - debounced fetch
                    textInput.addEventListener('input', function() {
                        var val = this.value.trim();
                        
                        // Update picker if valid hex
                        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                            picker.value = val;
                        } else if (/^[0-9A-Fa-f]{6}$/.test(val)) {
                            picker.value = '#' + val;
                        }
                        
                        debouncedFetch(500);
                    });
                    
                    // Enter key - immediate fetch
                    textInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            clearTimeout(debounceTimer);
                            fetchColorData();
                        }
                    });
                    
                    // Copy to clipboard functionality
                    results.addEventListener('click', function(e) {
                        if (e.target.classList.contains('ccp-value-data')) {
                            var text = e.target.textContent;
                            copyToClipboard(text);
                            
                            // Visual feedback on the element
                            e.target.classList.add('copied');
                            setTimeout(function() {
                                e.target.classList.remove('copied');
                            }, 600);
                            
                            // Show floating tooltip above the element
                            showTooltip(e.target);
                        }
                    });
                    
                    function setStatus(status, message) {
                        container.classList.remove('is-loading', 'has-error', 'has-results');
                        
                        if (status === 'loading') {
                            container.classList.add('is-loading');
                            statusText.textContent = message || 'Fetching colors...';
                        } else if (status === 'error') {
                            container.classList.add('has-error');
                            statusText.textContent = message || 'Error occurred';
                        } else if (status === 'success') {
                            container.classList.add('has-results');
                            statusText.textContent = message || 'Ready';
                        } else {
                            statusText.textContent = message || 'Ready';
                        }
                    }
                    
                    function fetchColorData() {
                        var colorValue = textInput.value.trim();
                        
                        if (!colorValue) {
                            return;
                        }
                        
                        // Cancel previous request if pending
                        if (currentRequest) {
                            currentRequest.abort();
                        }
                        
                        // Show loading state
                        setStatus('loading', 'Fetching colors...');
                        errorDiv.style.display = 'none';
                        
                        // Create AbortController for this request
                        var controller = new AbortController();
                        currentRequest = controller;
                        
                        // Create form data for WordPress AJAX
                        var formData = new FormData();
                        formData.append('action', 'ccp_fetch_color');
                        formData.append('nonce', nonce);
                        formData.append('color', colorValue);
                        
                        // Fetch via WordPress AJAX (server-side proxy)
                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            signal: controller.signal
                        })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(response) {
                            if (!response.success) {
                                throw new Error(response.data.message || 'Unknown error');
                            }
                            lastFetchedColor = colorValue;
                            displayResults(response.data);
                            setStatus('success', 'Palette generated');
                        })
                        .catch(function(error) {
                            if (error.name === 'AbortError') {
                                return; // Ignore aborted requests
                            }
                            showError(error.message);
                            setStatus('error', 'Failed to fetch');
                        })
                        .finally(function() {
                            currentRequest = null;
                        });
                    }
                    
                    function displayResults(data) {
                        var groups = {
                            'base': data.base,
                            'base_without_alpha_contrasted_text': data.base_without_alpha_contrasted_text,
                            'complementary': data.complementary,
                            'complementary_without_alpha_contrasted_text': data.complementary_without_alpha_contrasted_text,
                            'grayscale': data.grayscale,
                            'grayscale_without_alpha_contrasted_text': data.grayscale_without_alpha_contrasted_text
                        };
                        
                        for (var groupKey in groups) {
                            var groupData = groups[groupKey];
                            var groupEl = container.querySelector('[data-group="' + groupKey + '"]');
                            
                            if (groupEl && groupData) {
                                var swatchInner = groupEl.querySelector('.ccp-swatch-inner');
                                var cardName = groupEl.querySelector('.ccp-card-name');
                                var values = groupEl.querySelector('.ccp-values');
                                
                                // Set swatch background with transition
                                swatchInner.style.backgroundColor = groupData.hex.value;
                                
                                // Set color name
                                cardName.textContent = groupData.keyword || '';
                                
                                // Build values HTML
                                var valuesHtml = '';
                                valuesHtml += createValueRow('HEX', groupData.hex.value);
                                valuesHtml += createValueRow('RGB', groupData.rgb.value);
                                valuesHtml += createValueRow('HSL', groupData.hsl.value);
                                
                                values.innerHTML = valuesHtml;
                            }
                        }
                        
                        // Update the color picker to match the base color
                        if (data.base && data.base.hex) {
                            picker.value = data.base.hex.value;
                        }
                    }
                    
                    function createValueRow(label, value) {
                        return '<div class="ccp-value-row">' +
                            '<span class="ccp-value-label">' + label + '</span>' +
                            '<span class="ccp-value-data">' + escapeHtml(value) + '</span>' +
                            '</div>';
                    }
                    
                    function escapeHtml(text) {
                        var div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    }
                    
                    function showError(message) {
                        errorText.textContent = message;
                        errorDiv.style.display = 'flex';
                    }
                    
                    function copyToClipboard(text) {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text);
                        } else {
                            // Fallback for older browsers
                            var textarea = document.createElement('textarea');
                            textarea.value = text;
                            textarea.style.position = 'fixed';
                            textarea.style.opacity = '0';
                            document.body.appendChild(textarea);
                            textarea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textarea);
                        }
                    }
                    
                    // Auto-fetch default color on load
                    if (defaultColor) {
                        fetchColorData();
                    }
                }
                
            });
        })();
        </script>
        
        <?php
    }
    
    return ob_get_clean();
}
