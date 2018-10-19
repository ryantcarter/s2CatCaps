<?php

/*
 Plugin Name: s2Member - Auto Cat Caps
 Description: Based on the categories selected, automatically assigns custom capabilities to the post
 Version: 0.1
 Author: Ryan Carter
 Author URI: CRTR.co.uk
 */
class C_s2member_Auto_Custom_Capabilities
{
    /**
     * We store an instance of the class as a class variable. This way, the plugin can be extended by other plugins.
     * Third-parties would just sub-class C_s2member_Auto_Custom_Capabilities and then override the $_instance variable
     * @var null
     */
    static $_instance = NULL;
    static function get_instance()
    {
        if ( ! isset( self::$_instance ) ) {
            $klass = get_class();
            self::$_instance = new $klass;
        }
        return self::$_instance;
    }

    /**
     * Setup the plugin
     */
    function __construct()
    {
        add_action( 'save_post', array(&$this, 'associate_custom_capabilities'), PHP_INT_MAX );
    }

    /**
     * After a post has been saved, automatically associate the custom capabilities for the post, as indicated
     * by the categories assigned
     */
    function associate_custom_capabilities( $post_id )
    {
        $ccap_field = 's2member_ccaps_req';
        $categories = wp_get_post_categories( $post_id );
        $ccaps = get_post_meta( $post_id, $ccap_field, TRUE );

        // Iterate through each category_id, get the slug of the category,
        // and then add that slug as a custom capability for the post
        foreach ( $categories as $category_id ) {
            $category = get_category( $category_id );
            if ( ! in_array( $category->slug, $ccaps ) ) {
                $ccaps[] = $category->slug;
            }
        }

        // Update the the custom capabilities field for the post
        global $wpdb;
        $wpdb->delete( $wpdb->postmeta, array(
            'post_id'   =>  $post_id,
            'meta_key'  =>  $ccap_field
        ));
        $wpdb->insert( $wpdb->postmeta, array(
           'post_id'    =>  $post_id,
           'meta_key'   =>  $ccap_field,
           'meta_value' =>  serialize( $ccaps )
        ));
    }
}

C_s2member_Auto_Custom_Capabilities::get_instance();


