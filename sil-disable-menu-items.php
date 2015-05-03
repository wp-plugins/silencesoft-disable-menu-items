<?php
/*
 * Plugin Name: Silencesoft Disable Menu Items 
 * Description: Allow to enable/disable menu items
 * Version: 1.1
 * Plugin URI: http://silencesoft.co
 * Author: Byron Herrera
 * Author URI: http://byronh.axul.net
 * License: WTFPL
 * 
 * DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
 * Version 2, December 2004 
 * 
 * Copyright (C) 2004 Sam Hocevar <sam@hocevar.net> 
 *
 * Everyone is permitted to copy and distribute verbatim or modified 
 * copies of this license document, and changing it is allowed as long
 * as the name is changed. 
 * 
 * DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE 
 * TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION 
 *
 * 0. You just DO WHAT THE FUCK YOU WANT TO.

 * Based on code proposed by 
 * WordPress Menu Item Meta Fields
 * http://changeset.hr/blog/code/wordpress-menu-item-meta-fields
 * 
 **/

// don't load directly
if ( !function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( !class_exists( "Sil_Disable_Items_Plugin" ) ):

class Sil_Disable_Items_Plugin
{
	
	/**
	 * Hook da stuff!
	 */
	function __construct() {
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'edit_nav_menu_walker' ),10, 1 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'sil_disable_update_nav_menu_item' ), 10, 3 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'sil_nav_menu_items' ) );
		add_action('plugins_loaded', array( $this, 'sil_plugin_init') );
		// fix for Walker 
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'sil_custom_fields' ), 10, 4 );
	}

	
	/**
	 * Change the admin menu walker class name.
	 * @param string $walker
	 * @return string
	 */
	function edit_nav_menu_walker( $walker ) {
		//@TODO this should be loaded somewhere sooner... 
		// require_once WP_PLUGIN_DIR . '/silencesoft-disable-menu-items/tocka-nav-menu-walker.php';
		require_once 'tocka-nav-menu-walker.php';
		
		// swap the menu walker class only if it's the default wp class (just in case)
		if ( $walker === 'Walker_Nav_Menu_Edit' ) {
			$walker = 'Sil_Disable_Walker_Nav_Menu_Edit';
		}
		return $walker;
	}

	function sil_custom_fields( $item_id, $item, $depth, $args ) {
		$test_val = esc_attr( get_post_meta( $item_id, 'sil_disable_menu_item_test_val', TRUE ) );
		$test_val = ($test_val == "1")?" checked='checked'":'';
		print "<div class='field-disable description-wide'><input type='checkbox' ".$test_val." value='1' name='sil_disable_menu_item_test_val_".$item_id."' /><span class='description'>".__('Disable menu item', 'sil_disable')."</span></div>";
	}

	/**
	 * Save post meta. Menu items are just posts of type "menu_item".
	 * 
	 * 
	 * @param type $menu_id
	 * @param type $menu_item_id
	 * @param type $args
	 */
	function sil_disable_update_nav_menu_item($menu_id, $menu_item_id, $args) {
		
		if ( isset( $_POST[ "sil_disable_menu_item_test_val_$menu_item_id" ] ) ) {
			update_post_meta( $menu_item_id, 'sil_disable_menu_item_test_val', $_POST[ "sil_disable_menu_item_test_val_$menu_item_id" ] );
		} else {
			delete_post_meta( $menu_item_id, 'sil_disable_menu_item_test_val' );
		}
	}
	
	function sil_nav_menu_items ( $items )
	{
		$new_items = Array();
		foreach ( $items as $item )
		{
			$dis = get_post_meta( $item->ID, 'sil_disable_menu_item_test_val' , true);
			if ($dis != "1")
				$new_items[] = $item;
			
		}
		return $new_items;
	}

	function sil_plugin_init() {
		load_plugin_textdomain( 'sil_disable', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
}

// ignition!
new Sil_Disable_Items_Plugin();

endif;
