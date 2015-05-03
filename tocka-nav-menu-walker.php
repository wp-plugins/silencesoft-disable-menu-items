<?php


// don't load directly
if ( !function_exists( 'is_admin' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}



if ( !class_exists( "Sil_Disable_Walker_Nav_Menu_Edit" ) && class_exists( 'Walker_Nav_Menu_Edit' ) ):

class Sil_Disable_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	
	
	function start_el(&$output, $item, $depth, $args) {
		
		// append next menu element to $output
		parent::start_el($output, $item, $depth, $args);
		
		
		// now let's add a custom form field
		
		if ( ! class_exists( 'phpQuery') ) {
			// load phpQuery at the last moment, to minimise chance of conflicts (ok, it's probably a bit too defensive)
			require_once 'phpQuery-onefile.php';
		}
		
		$_doc = phpQuery::newDocumentHTML( $output );
		$_li = phpQuery::pq( 'li.menu-item:last' ); // ":last" is important, because $output will contain all the menu elements before current element
		
		// if the last <li>'s id attribute doesn't match $item->ID something is very wrong, don't do anything
		// just a safety, should never happen...
		$menu_item_id = str_replace( 'menu-item-', '', $_li->attr( 'id' ) );
		if( $menu_item_id != $item->ID ) {
			return;
		}
		
		// fetch previously saved meta for the post (menu_item is just a post type)
		$test_val = esc_attr( get_post_meta( $menu_item_id, 'sil_disable_menu_item_test_val', TRUE ) );
		$test_val = ($test_val == "1")?" checked='checked'":'';

		// fix for walker conflict
		ob_start();
		do_action( 'wp_nav_menu_item_custom_fields', $id, $item, $depth, $args );
		$fields = ob_get_clean();
		
		// by means of phpQuery magic, inject a new input field
		$_li->find( '.field-link-target' )
			->before($fields);

		// ->before(  "<div class='field-disable description-wide'><input type='checkbox' ".$test_val." value='1' name='sil_disable_menu_item_test_val_$menu_item_id' /><span class='description'>".__('Disable menu item', 'sil_disable')."</span></div>" )
		
		// swap the $output
		$output = $_doc->html();
		
	}

}

endif;
