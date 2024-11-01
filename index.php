<?php
/*
Plugin Name: WP Guest Book
Plugin URI: http://hamidulbdblog.com/wp_guest_book/
Description: A simple plugin, which you can use for Guest Book , Review or Testimonial, you can config every this in this <a href="/options-general.php?page=gb-otpion">Option area</a>, for any Help please contact in support.
Version: 1.6
Author: HamidulBD
Author URI: http://hamidulbdblog.com/
*/



/* Register the plugin script */
function guest_scripts_basic()
{
wp_enqueue_script( 'validatejs', plugins_url( '/js/jquery.validate.js', __FILE__ ) , array('jquery'));
wp_enqueue_script( 'validatejsfunction', plugins_url( '/js/jquery.validation.functions.js', __FILE__ ) , array('jquery'));
wp_enqueue_script( 'validatejsfull', plugins_url( '/js/jquery.validate-full.js', __FILE__ ) , array('jquery'));
wp_enqueue_script( 'masonry');
wp_enqueue_script( 'flexslider', plugins_url( '/js/jquery.flexslider-min.js', __FILE__ ) , array('jquery'));
wp_enqueue_script( 'main', plugins_url( '/js/main.js', __FILE__ ) , array('jquery'));
if ( get_option('use_plugin_css') == 'yes') {
 wp_enqueue_style( 'guest_book_css', plugins_url( '/css/guest-book-style.css', __FILE__ ) );
}
wp_enqueue_style( 'font-awesome.min', plugins_url( '/css/font-awesome.min.css', __FILE__ ) );
wp_enqueue_style( 'cdstyle', plugins_url( '/css/style.css', __FILE__ ) );
wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'guest_scripts_basic' );

/* register custom post type */
function guest_book_type() {        
        register_post_type( 'guest_book',
            array(
                'labels' => array(
                        'name' => __( 'Guest Book' ),
                        'singular_name' => __( 'Guest Book' ),
                        'add_new' => __( 'Add Guest Book' ),
                        'add_new_item' => __( 'Add New Guest Book' ),
                        'edit_item' => __( 'Edit Guest Book' ),
                        'new_item' => __( 'New Guest Book' ),
                        'view_item' => __( 'View Guest Book' ),
                        'not_found' => __( 'Sorry, we couldn\'t find the Name you are looking for.' )
                ),
            'public' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'menu_position' => 14,
//          'show_in_menu' => false,
            'has_archive' => true,
            'hierarchical' => true,
            'capability_type' => 'post',
            'rewrite' => array( 'slug' => 'review' ),
            'supports' => array( 'title', 'thumbnail', 'editor' )
            )
        );
            
    }   
add_action( 'init', 'guest_book_type' );

/* include option tree freamwork */
add_filter( 'ot_meta_boxes', '__return_true' ); 
add_filter('ot_show_pages', '__return_false' );
add_filter('ot_show_new_layout', '__return_false' );
add_filter('ot_theme_mode', '__return_false' );
include_once('option-tree/ot-loader.php' );
include_once('includes/meta-options.php' );


/* register custom taxonomies */
function guest_book_taxonomies() {
  register_taxonomy('guest_book', 'guest_book', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => true,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'Service Type', 'guest_book' ),
      'singular_name' => _x( 'Service Type', 'guest_book' ),
      'search_items' =>  __( 'Search Service Type' ),
      'all_items' => __( 'All Service Type' ),
      'parent_item' => __( 'Parent Service Type' ),
      'parent_item_colon' => __( 'Parent Service Type:' ),
      'edit_item' => __( 'Edit' ),
      'update_item' => __( 'Update' ),
      'add_new_item' => __( 'Add New' ),
      'new_item_name' => __( 'New' ),
      'menu_name' => __( 'Service Type' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'ticket-type', 
      'with_front' => true, 
      'hierarchical' => true 
    ),
  ));
}
add_action( 'init', 'guest_book_taxonomies', 0 );  

/*config submit ajax form */
add_action('wp_ajax_front_end_form_process', 'front_end_form_process');
add_action('wp_ajax_nopriv_front_end_form_process', 'front_end_form_process');
function front_end_form_process(){
  if (wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {
    $post_information = array(
        'post_title' => esc_attr(strip_tags($_POST['guest_title'])),
        'post_content' => esc_attr(strip_tags($_POST['detail_review'])),
        'post_status' => get_option('review_stats'),
        'post_type' => 'guest_book'
        
    );
        
    $post_id = wp_insert_post($post_information);
    if($post_id)
    {
        // Update Custom Meta 
    update_post_meta($post_id, 'guest_name', esc_attr(strip_tags($_POST['guest_name'])));
    update_post_meta($post_id, 'guest_email', esc_attr(strip_tags($_POST['guest_email'])));
    update_post_meta($post_id, 'guest_website', esc_attr(strip_tags($_POST['guest_website'])));
    update_post_meta($post_id, 'guest_fb', esc_attr(strip_tags($_POST['guest_fb']))); 
    update_post_meta($post_id, 'guest_tw', esc_attr(strip_tags($_POST['guest_tw'])));  
    }
    // Update Custom Tax
    wp_set_object_terms($post_id, $_POST['guest_book_type'], 'guest_book', true);
    // Send Thank you Message after submission
    if ( get_option('thank_you_email') == 'yes' ){
    $to = $_POST['guest_email'];
    $subject = 'Thank you for review';
    $message = get_option('email_content');
    //$headersss = 'your email';
    wp_mail( $to, $subject, $message, $header );
    }
}else{
  //if not verify you can add script
}
}


/* shortcode for form */
add_action('wp_footer', 'adding_ajax_footer');
function adding_ajax_footer(){
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function($) {
                $("#guest_name").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "Your Name is Required, Please enter your name"
                });
                $("#guest_title").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "Review Title is Required"
                });
                $("#detail_review").validate({
                    expression: "if (VAL) return true; else return false;",
                    message: "Please Write Your Review"
                });
                $("#guest_email").validate({
                    expression: "if (VAL.match(/^[^\\W][a-zA-Z0-9\\_\\-\\.]+([a-zA-Z0-9\\_\\-\\.]+)*\\@[a-zA-Z0-9_]+(\\.[a-zA-Z0-9_]+)*\\.[a-zA-Z]{2,4}$/)) return true; else return false;",
                    message: "Please enter a valid Email ID"
                });
                $("#guest_book_type").validate({
                    expression: "if (VAL != '0') return true; else return false;",
                    message: "Please Select your service"
                });
                
    $('#successmsg').hide();
    $("#guest_book_button").click( function() {
      $('#guest_book_form').hide();
      $('#loading').html('<img src="<?php echo plugins_url( "/ajax-loader.gif", __FILE__ ); ?>" alt="">');
      var message = $('#detail_review').val();
      var guest_title = $('#guest_title').val();
      var guest_name = $('#guest_name').val();
      var guest_email = $('#guest_email').val();
      var guest_website = $('#guest_website').val();
      var guest_fb = $('#guest_fb').val();
      var guest_tw = $('#guest_tw').val();
      var message = $('#detail_review').val();
      var post_nonce_field = $('#post_nonce_field').val();
      var guest_book_type = $('#guest_book_type').val();
$.ajax({
  type: "POST",
  url: "<?php echo admin_url( 'admin-ajax.php'); ?>",
  data: {
                action: 'front_end_form_process',
                guest_title: guest_title,
                guest_name: guest_name,
                guest_email: guest_email,
                guest_website: guest_website,
                guest_fb: guest_fb,
                guest_tw: guest_tw,
                guest_book_type: guest_book_type,
                detail_review: message,
                post_nonce_field: post_nonce_field,
            },
  dataType: 'json',
  beforeSend: function() {
      var message = $('#detail_review').val();
      var guest_title = $('#guest_title').val();
      var guest_name = $('#guest_name').val();
      var guest_email = $('#guest_email').val();
       if (!message || !guest_title || !guest_name || !guest_email) {
          $('#loading').html('<h1>Please Write Your Name , Email , Review Title and Review</h1>');
          $('#guest_book_form').show();
          return false;
        }
      },
  success: function(response) {
    $('#successmsg').show();
    $('#loading').hide();
         }  
});
     return false;    

   });
      });
    </script>
  <?php
}



add_shortcode( 'add_guest_book_form', 'front_end_form' );

function front_end_form( $atts ) {
    ob_start();?>
<?php
if(get_option('login_require') == 'yes'){

if ( is_user_logged_in() ) { ?>
<form action="" id="guest_book_form" method="POST">
            <fieldset>
                <label for="guest_name"><?php _e('Name:', 'guest_book') ?></label>
                <input type="text" name="guest_name" id="guest_name" value="<?php if(isset($_POST['guest_name'])) echo $_POST['guest_name'];?>" class="guest_input" />
                <div id="name-error"></div>
            </fieldset>
                      <fieldset>
                <label for="guest_email"><?php _e('Email:', 'guest_book') ?></label>
                <input type="text" name="guest_email" id="guest_email" value="<?php if(isset($_POST['guest_email'])) echo $_POST['guest_email'];?>" />
                <div id="email-error"></div>
            </fieldset>
            <fieldset>
                <label for="guest_website"><?php _e('Profession (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_website" id="guest_website" value="<?php if(isset($_POST['guest_website'])) echo $_POST['guest_website'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_fb"><?php _e('Facebook (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_fb" id="guest_fb" value="<?php if(isset($_POST['guest_fb'])) echo $_POST['guest_fb'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_tw"><?php _e('Twitter (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_tw" id="guest_tw" value="<?php if(isset($_POST['guest_tw'])) echo $_POST['guest_tw'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_title"><?php _e('Review Headline:', 'guest_book') ?></label>
                <input type="text" name="guest_title" id="guest_title" value="<?php if(isset($_POST['guest_title'])) echo $_POST['guest_title'];?>" class="required" />
                <div id="title-error"></div>
            </fieldset>
            <fieldset>
            <label for="guest_book_type"><?php _e('Which Service did You took?:', 'guest_book') ?></label>
                <select name="guest_book_type" id="guest_book_type">
                <option value="0" >Please Select One</option>
                <?php 

$argss = array(
  'type'                     => 'name',
  'child_of'                 => 0,
  'parent'                   => '',
  'orderby'                  => 'name',
  'order'                    => 'ASC',
  'hide_empty'               => 0,
  'hierarchical'             => 1,
  'exclude'                  => '',
  'include'                  => '',
  'number'                   => '',
  'taxonomy'                 => 'guest_book',
  'pad_counts'               => false 

); 

$categoriess = get_categories($argss);
  foreach($categoriess as $categorys) { ?>
 <option value="<?php echo $categorys->name; ?>" ><?php echo $categorys->name; ?></option>
<?php } ?>
                   
                </select>
            </fieldset>
            <fieldset>  
                <label for="detail_review"><?php _e('Write Detail Review:', 'framework') ?></label>
                <textarea name="detail_review" id="detail_review" rows="10" cols="100"><?php if(isset($_POST['detail_review'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['detail_review']); } else { echo $_POST['detail_review']; } } ?></textarea>
            </fieldset>
            <fieldset>
                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                <input type="hidden" name="submitted" id="submitted" value="true" />
                <input id="guest_book_button" type="submit" value="<?php _e('Submit', 'framework') ?>">
            </fieldset>
        </form>
        <div id="loading"></div>
        <div id="successmsg">
        <?php $aftersubmission = get_option('after_submit_review');
        if (!empty($aftersubmission)) {
          echo $aftersubmission ;
        }else{ ?>
          <h1>Thank you for giving Review</h1>
        <?php } ?>
        </div>
    <?php } else {  $loggedinmassage = get_option('non_loggedin_user');
        if (!empty($loggedinmassage)) {
          echo $loggedinmassage;
        }else{ ?>
    <h2>Please <a href="/wp-login.php">Sign In</a> or <a href="/wp-login.php?action=register">Sign Up </a> First For Write Review </h2>
<?php }} }else{ ?> <form action="" id="guest_book_form" method="POST">
            <fieldset>
                <label for="guest_name"><?php _e('Name:', 'guest_book') ?></label>
                <input type="text" name="guest_name" id="guest_name" value="<?php if(isset($_POST['guest_name'])) echo $_POST['guest_name'];?>" class="guest_input" />
                <div id="name-error"></div>
            </fieldset>
                      <fieldset>
                <label for="guest_email"><?php _e('Email:', 'guest_book') ?></label>
                <input type="text" name="guest_email" id="guest_email" value="<?php if(isset($_POST['guest_email'])) echo $_POST['guest_email'];?>" />
                <div id="email-error"></div>
            </fieldset>
            <fieldset>
                <label for="guest_website"><?php _e('Website (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_website" id="guest_website" value="<?php if(isset($_POST['guest_website'])) echo $_POST['guest_website'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_fb"><?php _e('Facebook (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_fb" id="guest_fb" value="<?php if(isset($_POST['guest_fb'])) echo $_POST['guest_fb'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_tw"><?php _e('Twitter (Optional):', 'guest_book') ?></label>
                <input type="text" name="guest_tw" id="guest_tw" value="<?php if(isset($_POST['guest_tw'])) echo $_POST['guest_tw'];?>" />
            </fieldset>
            <fieldset>
                <label for="guest_title"><?php _e('Review Headline:', 'guest_book') ?></label>
                <input type="text" name="guest_title" id="guest_title" value="<?php if(isset($_POST['guest_title'])) echo $_POST['guest_title'];?>" class="required" />
                <div id="title-error"></div>
            </fieldset>
            <fieldset>
            <label for="guest_book_type"><?php _e('Which Service did You took?:', 'guest_book') ?></label>
                <select name="guest_book_type" id="guest_book_type">
                <?php 

$argss = array(
  'type'                     => 'name',
  'child_of'                 => 0,
  'parent'                   => '',
  'orderby'                  => 'name',
  'order'                    => 'ASC',
  'hide_empty'               => 0,
  'hierarchical'             => 1,
  'exclude'                  => '',
  'include'                  => '',
  'number'                   => '',
  'taxonomy'                 => 'guest_book',
  'pad_counts'               => false 

); 

$categoriess = get_categories($argss);
  foreach($categoriess as $categorys) { ?>
 <option value="<?php echo $categorys->name; ?>" ><?php echo $categorys->name; ?></option>
<?php } ?>
                   
                </select>
            </fieldset>
            <fieldset>  
                <label for="detail_review"><?php _e('Write Detail Review:', 'framework') ?></label>
                <textarea name="detail_review" id="detail_review" rows="10" cols="100"><?php if(isset($_POST['detail_review'])) { if(function_exists('stripslashes')) { echo stripslashes($_POST['detail_review']); } else { echo $_POST['detail_review']; } } ?></textarea>
            </fieldset>
            <fieldset>
                <?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
                <input type="hidden" name="submitted" id="submitted" value="true" />
                <input id="guest_book_button" type="submit" value="<?php _e('Submit', 'framework') ?>">
            </fieldset>
        </form>
        <div id="loading"></div>
        <div id="successmsg">
         <?php $aftersubmission = get_option('after_submit_review');
        if (!empty($aftersubmission)) {
          echo $aftersubmission ;
        }else{ ?>
          <h1>Thank you for giving Review</h1>
        <?php } ?>
        </div><?php } $myvariable = ob_get_clean();
    return $myvariable;
    } 

/* Guest Book Plugin Options */

function guest_book_option(){
  register_setting('guest_book_system', 'login_require');
  register_setting('guest_book_system', 'review_stats');
  register_setting('guest_book_system', 'thank_you_email');
  register_setting('guest_book_system', 'use_plugin_css');
  register_setting('guest_book_system', 'email_content');
  register_setting('guest_book_system', 'non_loggedin_user');
  register_setting('guest_book_system', 'after_submit_review');
  register_setting('guest_book_system', 'custom_css');
}
add_action('admin_init','guest_book_option');

function guest_book_system(){
?>
<div class="warp">
<h2>Setting</h2>
<form action="options.php" method="post" id="guest_book_option_form">
<?php settings_fields('guest_book_system'); ?>

<table class="form-table">
        <tr valign="top">
        <th scope="row">Login Require For Write Review ? </th>
        <td> <select name="login_require">
          <option value="yes" <?php if ( get_option('login_require') == 'yes' ) echo 'selected="selected"'; ?>>Yes</option>
          <option value="no" <?php if ( get_option('login_require') == 'no' ) echo 'selected="selected"'; ?>>No</option>
        </select>
        <p id="login_require" class="description">Do you want let user write a review without login ?</p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Write Massage for unregister or non logged in user  </th>
        <td> <textarea name="non_loggedin_user" id="non_loggedin_user" rows="10" cols="100"><?php echo get_option('non_loggedin_user'); ?>
        </textarea>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Review Stats</th>
        <td> <select name="review_stats">
          <option value="publish" <?php if ( get_option('review_stats') == 'publish' ) echo 'selected="selected"'; ?>>Publish</option>
          <option value="pending" <?php if ( get_option('review_stats') == 'pending' ) echo 'selected="selected"'; ?>>Pending</option>
        </select>
        <p id="review_stats" class="description">Do you want publish review immediately or keep pending for review ?</p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Would You like to Send Thank You Email ?</th>
        <td> <select name="thank_you_email">
          <option value="yes" <?php if ( get_option('thank_you_email') == 'yes' ) echo 'selected="selected"'; ?>>Yes</option>
          <option value="no" <?php if ( get_option('thank_you_email') == 'no' ) echo 'selected="selected"'; ?>>no</option>
        </select>
        <p id="thank_you_email" class="description">Do you want send thank you email after review submission ?</p></td>
        </tr>
        <tr valign="top">
        <th scope="row">Write Thank You Email(You can use HTML)</th>
        <td> 
          <textarea name="email_content" id="email_content" rows="10" cols="100"><?php echo get_option('email_content'); ?></textarea>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Do You Want To Use Plugin CSS?</th>
        <td> 
        <select name="use_plugin_css">
          <option value="yes" <?php if ( get_option('use_plugin_css') == 'yes' ) echo 'selected="selected"'; ?>>Yes</option>
          <option value="no"  <?php if ( get_option('use_plugin_css') == 'no' ) echo 'selected="selected"'; ?>>no</option>
        </select></td>
        </tr>
        <tr valign="top">
        <th scope="row">Show Msagess after submission</th>
        <td> 
        <textarea name="after_submit_review" id="after_submit_review" rows="10" cols="100"><?php echo get_option('after_submit_review') ?></textarea></td>
        </tr>
        <tr valign="top">
        <th scope="row">Custom CSS</th>
        <td> 
        <textarea name="custom_css" id="custom_css" rows="10" cols="100"><?php echo get_option('custom_css'); ?></textarea></td>
        </tr>
    </table>
  <?php submit_button(); ?>
</form>
</div>
<?php
}

function guest_book_option_page(){
  add_options_page('Guest Book Setting', 'Guest Book Setting', 'manage_options', 'gb-otpion', 'guest_book_system');
}

add_action('admin_menu', 'guest_book_option_page');

/* guest book post query with shortcode*/
add_shortcode( 'guest_book_slider', 'guest_book_slider_function' );

function guest_book_slider_function( $atts ) {
  $atts = shortcode_atts( array(
    'show_review' => 10,
    'title' => 'Our Guest Book Review'
  ), $atts, 'guest_book_slider' );
    ob_start();?>
    <div id="testimonials">
        <h2>Client Testimonials</h2>
<div class="carousel-nav clearfix">
  <!-- arrows http://findicons.com/icon/235460/forward?id=388672 -->
  <img src="<?php echo plugins_url( '/img/prev.png', __FILE__ ); ?>" id="prv-testimonial" class="prevbtn">
  <img src="<?php echo plugins_url( '/img/next.png', __FILE__ ); ?>" id="nxt-testimonial" class="nextbtn">
</div>
<div class="carousel-wrap">
  <ul id="testimonial-list" class="clearfix">
<?php
$args = array(
    'post_type' => 'guest_book',
    'posts_per_page' => $atts['show_review'],
    'post_status' => 'publish'
);
$guest_query = new WP_Query( $args );
if ( $guest_query->have_posts() ) :
while ( $guest_query->have_posts() ) : $guest_query->the_post(); 
?>
    <li>
      <div class="context">"<?php the_content(); ?>"</div>
      <p class="credits"><?php echo get_post_meta( get_the_ID(), 'guest_name', true );  ?>, <a href=""><?php echo get_post_meta( get_the_ID(), 'guest_website', true );  ?></a></p>
    </li>
<?php endwhile; ?>
<?php else: ?>
<h1>You have No Ticket</h1>
<?php endif; wp_reset_postdata(); ?>
  </ul><!-- @end #testimonial-list -->
</div><!-- @end .carousel-wrap -->
</div>
<?php $myvariable = ob_get_clean();
    return $myvariable;
    } 


add_shortcode( 'guest_book_grid', 'guest_book_grid_function' );

function guest_book_grid_function( $atts ) {
  $atts = shortcode_atts( array(
    'show_review' => 1,
    'title' => 'Our Guest Book Review'
  ), $atts, 'guest_book_slider' );
    ob_start();?>
  <div id="grid_guest">
  <h1 class="guest_title"><?php echo $atts['title']; ?></h1>
<?php
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type' => 'guest_book',
    'posts_per_page' => $atts['show_review'],
    'post_status' => 'publish',
    'paged' => $paged
);
$guest_query = new WP_Query( $args );
if ( $guest_query->have_posts() ) :
while ( $guest_query->have_posts() ) : $guest_query->the_post(); 
?>
     <div class="grid_guest_review">
     <h2><?php the_title(); ?></h2>
     <div class="guest_detail_review"><?php the_content(); ?></div>
        <div class="authorContainer">
          <p class="quote-author" style="float: right;margin-bottom:0px;"><?php echo get_post_meta( get_the_ID(), 'guest_name', true ); ?></br><?php echo get_post_meta( get_the_ID(), 'guest_website', true ); ?>
          <a href="<?php echo get_post_meta( get_the_ID(), 'guest_fb', true ); ?>"><i class="fa fa-facebook-official"></i></a> <a href="<?php echo get_post_meta( get_the_ID(), 'guest_tw', true ); ?>"><i class="fa fa-twitter"></i></a>  </p>
        </div>
      </div>
<?php endwhile; ?>
<div class="pagition"><?php next_posts_link( 'Older Review', $guest_query->max_num_pages );
previous_posts_link( 'Newer Review' ); ?></div>


<?php else: ?>
<h1>You have No Review</h1>
<?php endif;

 wp_reset_postdata(); ?>
</div>
<?php $myvariable = ob_get_clean();
    return $myvariable;
    } 

add_shortcode( 'guest_book', 'guest_book_cursol_function' );
function guest_book_cursol_function( $atts ) {
  $atts = shortcode_atts( array(
    'show_review' => 5,
    'title' => 'Our Guest Book Review'
  ), $atts, 'guest_book_slider' );
    ob_start();?>
  <h1 class="guest_title"><?php echo $atts['title']; ?></h1>
  <div class="cd-testimonials-wrapper cd-container">
  <ul class="cd-testimonials">
<?php
$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$args = array(
    'post_type' => 'guest_book',
    'posts_per_page' => $atts['show_review'],
    'post_status' => 'publish',
    'paged' => $paged
);
$guest_query = new WP_Query( $args );
if ( $guest_query->have_posts() ) :
while ( $guest_query->have_posts() ) : $guest_query->the_post(); 
?>
    
    <li>
      <p><?php the_content(); ?></p>
      <div class="cd-author">
       <?php if ( has_post_thumbnail() ) { ?>
  <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>" alt="Author image">
<?php }else{ ?>
<img src="<?php echo plugins_url( '/img/profile-img.png', __FILE__ ); ?>" alt="<?php echo get_post_meta( get_the_ID(), 'guest_name', true ); ?>">          <?php } ?>
        <ul class="cd-author-info">
          <li><?php echo get_post_meta( get_the_ID(), 'guest_name', true ); ?></li>
          <li><?php echo get_post_meta( get_the_ID(), 'guest_website', true ); ?></li>
        </ul>
      </div>
    </li>    
<?php endwhile; ?>
<?php else: ?>
<h1>You have No Review</h1>
<?php endif; wp_reset_postdata(); ?>
  </ul> <!-- cd-testimonials -->
  <a href="#0" class="cd-see-all">See all</a>
</div> <!-- cd-testimonials-wrapper -->
<div class="cd-testimonials-all">
  <div class="cd-testimonials-all-wrapper">
    <ul>
<?php
$args = array(
    'post_type' => 'guest_book',
    'posts_per_page' => -1,
    'post_status' => 'publish'
);
$guestall_query = new WP_Query( $args );
if ( $guestall_query->have_posts() ) :
while ( $guestall_query->have_posts() ) : $guestall_query->the_post();  ?>
      <li class="cd-testimonials-item">
        <?php the_content(); ?>
        <div class="cd-author">
        <?php if ( has_post_thumbnail() ) { ?>
  <img src="<?php echo wp_get_attachment_url( get_post_thumbnail_id() ); ?>" alt="Author image">
<?php }else{ ?>
<img src="<?php echo plugins_url( '/img/profile-img.png', __FILE__ ); ?>" alt="<?php echo get_post_meta( get_the_ID(), 'guest_name', true ); ?>">          <?php } ?>
          <ul class="cd-author-info">
            <li><?php echo get_post_meta( get_the_ID(), 'guest_name', true ); ?></li>
          <li><?php echo get_post_meta( get_the_ID(), 'guest_website', true ); ?></li>
          <li><a href="<?php echo get_post_meta( get_the_ID(), 'guest_fb', true ); ?>"><i class="fa fa-facebook-official"></i></a> <a href="<?php echo get_post_meta( get_the_ID(), 'guest_tw', true ); ?>"><i class="fa fa-twitter"></i></a></li>
          </ul>
        </div> <!-- cd-author -->
      </li>
<?php  endwhile; endif; wp_reset_postdata(); ?> 
    </ul>
  </div> <?php $myvariable = ob_get_clean();
    return $myvariable;
    }

add_filter('next_posts_link_attributes', 'guest_book_posts_link_attributes_1');
add_filter('previous_posts_link_attributes', 'guest_book_posts_link_attributes_2');

function guest_book_posts_link_attributes_1() {
    return 'class="prev-post"';
}
function guest_book_posts_link_attributes_2() {
    return 'class="next-post"';
}

/*adding custom css */
add_action('wp_head', 'custom_css_add');
function custom_css_add(){
  ?>
<style type="text/css">
  <?php echo get_option('custom_css'); ?>
</style>
  <?php
}
?>

