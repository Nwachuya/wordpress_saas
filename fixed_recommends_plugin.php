<?php
// =========================================================================
//  Inject REVISED CSS Styles into the Page Header
// =========================================================================
add_action( 'wp_head', 'my_recommends_inline_css_func_v2' );
function my_recommends_inline_css_func_v2() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'recommends_grid' ) ) {
        return;
    }
    ?>
    <style>
        /* GRID LAYOUT: 3 columns on desktop, responsive on smaller screens */
        .recommends-grid-list{list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(3,1fr);gap:2.5rem 2rem}
        @media(max-width:992px){.recommends-grid-list{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:600px){.recommends-grid-list{grid-template-columns:1fr}}

        /* CARD CONTAINER: White with shadow and hover effect */
        .recommends-card-item{background:#fff;border-radius:4px;box-shadow:0 5px 15px rgba(0,0,0,.07);transition:transform .2s ease-in-out,box-shadow .2s ease-in-out;display:flex;flex-direction:column}
        .recommends-card-item:hover{transform:translateY(-5px);box-shadow:0 8px 25px rgba(0,0,0,.1)}
        .recommends-card-link{text-decoration:none;color:#000;display:flex;flex-direction:column;flex-grow:1}

        /* CARD IMAGE: Fixed 250px height */
        .card-image-wrapper{height:250px;overflow:hidden;border-radius:4px 4px 0 0;background-color:#f5f5f5}
        .card-image{width:100%;height:100%;object-fit:cover;transition:transform .3s ease}
        .recommends-card-item:hover .card-image{transform:scale(1.05)}
        .card-image-placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#b0b0b0}
        
        /* CARD CONTENT: Clean typography */
        .recommends-card{display:flex;flex-direction:column;height:100%}
        .card-content{padding:1rem;flex-grow:1;display:flex;flex-direction:column}
        .card-title{font-size:1.2rem;font-weight:600;margin:0 0 .25rem;color:#000}
        .card-interests{font-size:.85rem;color:#777;margin-bottom:.75rem}
        .card-meta{margin-top:auto;font-size:.9rem;color:#333;display:flex;flex-direction:column;gap:.25rem}
        .card-meta span{display:flex;align-items:center;gap:.4rem}
        .card-rating{font-weight:500}
        .card-rating .star-icon{color:#ffc107}

        /* SIDEBAR FILTERS: Minimalist link style */
        .recommends-filter-sidebar h3{font-size:1.1rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin:0 0 1rem}
        .recommends-filter-sidebar ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.75rem}
        .recommends-filter-sidebar a{text-decoration:none;color:#000;transition:color .2s}
        .recommends-filter-sidebar a:hover{color:#555;text-decoration:underline}
        .recommends-filter-sidebar a.active{font-weight:bold;text-decoration:underline}

        /* AJAX LOADER */
        .recommends-grid-container{position:relative}
        .grid-loader-overlay{position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,.8);z-index:10;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity .2s,visibility .2s}
        .grid-loader-overlay.is-loading{opacity:1;visibility:visible}
        .grid-loader{border:4px solid #f3f3f3;border-top:4px solid #333;border-radius:50%;width:40px;height:40px;animation:spin .8s linear infinite}
        @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        .no-results{text-align:center;grid-column:1/-1;padding:3rem 1rem;color:#777}
    </style>
    <?php
}

// =========================================================================
//  Inject JavaScript into the Page Footer
// =========================================================================
add_action( 'wp_footer', 'my_recommends_inline_js_func_v2' );
function my_recommends_inline_js_func_v2() {
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) || ! has_shortcode( $post->post_content, 'recommends_grid' ) ) {
        return;
    }
    ?>
    <script id="recommends-filter-script">
    (function($) {
        // Wait for DOM to be ready
        $(document).ready(function() {
            // Check if jQuery is available
            if (typeof $ === 'undefined') {
                console.error('jQuery is not loaded');
                return;
            }

            // Use event delegation to handle dynamically added elements
            $(document).on('click', '.interest-filter-link', function(e) {
                e.preventDefault();
                
                const interestId = $(this).data('interest-id');
                const gridContainer = $('#recommends-grid');
                const loader = $('.grid-loader-overlay');
                
                // Validate elements exist
                if (!gridContainer.length || !loader.length) {
                    console.error('Required elements not found');
                    return;
                }
                
                // Update active state
                $('.interest-filter-link').removeClass('active');
                $(this).addClass('active');
                
                // Show loader
                loader.addClass('is-loading');

                // Perform AJAX request
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: { 
                        action: 'filter_recommends', 
                        interest: interestId,
                        nonce: '<?php echo wp_create_nonce('filter_recommends_nonce'); ?>'
                    },
                    timeout: 10000, // 10 second timeout
                    success: function(response) {
                        if (response.success && response.data && response.data.html) {
                            gridContainer.fadeOut(150, function() {
                                $(this).html(response.data.html).fadeIn(150);
                            });
                        } else {
                            console.error('Invalid response format:', response);
                            gridContainer.html('<li class="no-results">Error loading content. Please try again.</li>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        gridContainer.html('<li class="no-results">Error loading content. Please try again.</li>');
                    },
                    complete: function() {
                        setTimeout(function() { 
                            loader.removeClass('is-loading'); 
                        }, 150);
                    }
                });
            });
        });
    })(jQuery);
    </script>
    <?php
}

// =========================================================================
//  HELPER FUNCTION: Renders a single card's HTML
// =========================================================================
function my_render_recommend_card( $post_id = null ) {
    if ( ! $post_id ) $post_id = get_the_ID();
    
    // Validate post ID
    if ( ! $post_id || get_post_status( $post_id ) !== 'publish' ) {
        return '';
    }

    $cover_url    = get_field( 'cover', $post_id );
    $rating       = get_field( 'averagerating', $post_id );
    $review_count = get_field( 'reviewscount', $post_id );
    $location     = get_field( 'location', $post_id );
    $cost         = get_field( 'averagecost', $post_id );
    $post_link    = get_permalink( $post_id );
    $title        = get_the_title( $post_id );
    $interests    = get_the_terms( $post_id, 'interest' );

    // Fallback to featured image if 'cover' URL field is empty
    if ( ! $cover_url && has_post_thumbnail( $post_id ) ) {
        $cover_url = get_the_post_thumbnail_url( $post_id, 'large' );
    }

    ob_start();
    ?>
    <li class="recommends-card-item">
        <a href="<?php echo esc_url( $post_link ); ?>" class="recommends-card-link">
            <div class="recommends-card">
                <div class="card-image-wrapper">
                    <?php if ( $cover_url ) : ?>
                        <img src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="card-image" loading="lazy">
                    <?php else: ?>
                        <div class="card-image-placeholder"><span>No Image</span></div>
                    <?php endif; ?>
                </div>
                <div class="card-content">
                    <h3 class="card-title"><?php echo esc_html( $title ); ?></h3>
                     <?php if ( ! empty( $interests ) && ! is_wp_error( $interests ) ) : ?>
                        <div class="card-interests">
                            <?php 
                            // Displaying interests as plain text
                            $interest_names = array();
                            foreach ( $interests as $interest ) {
                                $interest_names[] = esc_html( $interest->name );
                            }
                            echo implode( ' · ', $interest_names );
                            ?>
                        </div>
                    <?php endif; ?>
                    <div class="card-meta">
                        <?php if ( $rating ) : ?>
                            <span class="card-rating">★ <?php echo esc_html( number_format( (float) $rating, 1 ) ); ?> (<?php echo esc_html( $review_count ? $review_count : '0' ); ?>)</span>
                        <?php endif; ?>
                        <?php if ( $location ) : ?>
                            <span class="card-location">📍 <?php echo esc_html( $location ); ?></span>
                        <?php endif; ?>
                        <?php if ( $cost ) : ?>
                            <span class="card-cost">💰 <?php echo esc_html( $cost ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </a>
    </li>
    <?php
    return ob_get_clean();
}

// =========================================================================
//  SHORTCODE 1: [recommends_grid] - Displays the main content grid
// =========================================================================
add_shortcode( 'recommends_grid', 'my_recommends_grid_shortcode_func' );
function my_recommends_grid_shortcode_func() {
    $args = array(
        'post_type' => 'recommend',
        'posts_per_page' => 9,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    $query = new WP_Query($args);
    
    ob_start();
    ?>
    <div class="recommends-grid-container">
        <ul id="recommends-grid" class="recommends-grid-list">
            <?php
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    echo my_render_recommend_card();
                }
            } else {
                echo '<li class="no-results">No places have been added yet.</li>';
            }
            wp_reset_postdata();
            ?>
        </ul>
        <div class="grid-loader-overlay">
            <div class="grid-loader"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// =========================================================================
//  SHORTCODE 2: [recommends_filters] - Displays the sidebar filters
// =========================================================================
add_shortcode( 'recommends_filters', 'my_recommends_filters_shortcode_func' );
function my_recommends_filters_shortcode_func() {
    $terms = get_terms( array(
        'taxonomy' => 'interest',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC'
    ) );
    
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '<div class="recommends-filter-sidebar"><p>No filters available.</p></div>';
    }
    
    ob_start();
    ?>
    <div class="recommends-filter-sidebar">
        <h3>Filter by Interest</h3>
        <ul>
            <li><a href="#" class="interest-filter-link active" data-interest-id="0">All</a></li>
            <?php foreach ( $terms as $term ) : ?>
                <li>
                    <a href="#" class="interest-filter-link" data-interest-id="<?php echo esc_attr( $term->term_id ); ?>">
                        <?php echo esc_html( $term->name ); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

// =========================================================================
//  AJAX HANDLER: Powers the filtering action
// =========================================================================
add_action( 'wp_ajax_filter_recommends', 'my_ajax_filter_handler_func' );
add_action( 'wp_ajax_nopriv_filter_recommends', 'my_ajax_filter_handler_func' );
function my_ajax_filter_handler_func() {
    // Verify nonce for security
    if ( ! wp_verify_nonce( $_POST['nonce'], 'filter_recommends_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Security check failed' ) );
        return;
    }
    
    $interest_id = isset( $_POST['interest'] ) ? intval( $_POST['interest'] ) : 0;
    
    $args = array(
        'post_type' => 'recommend',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    // Add taxonomy filter if specific interest is selected
    if ( $interest_id > 0 ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'interest',
                'field'    => 'term_id',
                'terms'    => $interest_id,
            ),
        );
    }
    
    $query = new WP_Query( $args );
    
    ob_start();
    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            echo my_render_recommend_card();
        }
    } else {
        echo '<li class="no-results">No places found matching your selection.</li>';
    }
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success( array( 'html' => $html ) );
}

// =========================================================================
//  ADMIN NOTICE: Check for required plugins/fields
// =========================================================================
add_action( 'admin_notices', 'my_recommends_admin_notice' );
function my_recommends_admin_notice() {
    if ( ! function_exists( 'get_field' ) ) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Recommends Grid Plugin:</strong> Advanced Custom Fields plugin is required for this plugin to work properly.</p>';
        echo '</div>';
    }
    
    if ( ! post_type_exists( 'recommend' ) ) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Recommends Grid Plugin:</strong> The "recommend" post type does not exist. Please create it or check your post type registration.</p>';
        echo '</div>';
    }
    
    if ( ! taxonomy_exists( 'interest' ) ) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Recommends Grid Plugin:</strong> The "interest" taxonomy does not exist. Please create it or check your taxonomy registration.</p>';
        echo '</div>';
    }
}