<?php
/**
 * Initialize the custom Meta Boxes. 
 */
add_action( 'admin_init', 'custom_meta_boxes1' );

/**
 * Meta Boxes demo code.
 *
 * You can find all the available option types in demo-theme-options.php.
 *
 * @return    void
 * @since     2.0
 */
function custom_meta_boxes1() {
  
  /**
   * Create a custom meta boxes array that we pass to 
   * the OptionTree Meta Box API Class.
   */
  $my_meta_box = array(
    'id'          => 'demo_meta_box',
    'title'       => __( 'Guest Detail'),
    'pages'       => array( 'guest_book' ),
    'context'     => 'normal',
    'priority'    => 'high',
    'fields'      => array(
      array(
        'label'       => __( 'Guest Name', 'guest_book' ),
        'id'          => 'guest_name',
        'type'        => 'text',
        'desc'        => __( '', 'guest_book' )
      ),
      array(
        'label'       => __( 'Guest Email Address', 'guest_book' ),
        'id'          => 'guest_email',
        'type'        => 'text',
        'desc'        => __( '', 'guest_book' )
      ),
      array(
        'label'       => __( 'Guest Profession', 'guest_book' ),
        'id'          => 'guest_website',
        'type'        => 'text',
        'desc'        => __( '', 'guest_book' )
      ),
      array(
        'label'       => __( 'Guest Facebook Account', 'guest_book' ),
        'id'          => 'guest_fb',
        'type'        => 'text',
        'desc'        => __( '', 'guest_book' )
      ),
      array(
        'label'       => __( 'Guest Twitter Account', 'guest_book' ),
        'id'          => 'guest_tw',
        'type'        => 'text',
        'desc'        => __( '', 'guest_book' )
      )
    )
  );
  
  /**
   * Register our meta boxes using the 
   * ot_register_meta_box() function.
   */
  if ( function_exists( 'ot_register_meta_box' ) )
    ot_register_meta_box( $my_meta_box );

}