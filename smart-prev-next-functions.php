<?php

/*
 	This function registers the translation text domain.
*/
function SmartPrevNextLanguage() {
	load_plugin_textdomain('smart-prev-next', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	__('Smart Prev Next', 'smart-prev-next');
	__('Adds smart prev/next buttons to the editor.', 'smart-prev-next');
}

/*
 	This function is called for each post/page in the post/page list to add the filter info to the edit links in the quick edit.
 */
function SmartPrevNextBuildParams()
	{
	$options = array();

	// Get the category from the url if it exists and make sure it's an integer
	if( array_key_exists( 'cat', $_REQUEST ) && intval( $_REQUEST['cat'] ) > '0' )
		{
		$options['cat'] = intval( $_REQUEST['cat'] );
		}

	// Get the category name from the url if it exists.
	if( array_key_exists( 'category_name', $_REQUEST ) && $_REQUEST['category_name'] !== '' && $_REQUEST['category_name'] != '' )
		{
		// Make sure its a valid category.
		$categories = get_categories();

		foreach( $cats as $category )
			{
			if( $category->name === $_REQUEST['category_name'] )
				$options['category_name'] = $category->name;

				break;
			}
		}

	// Get the tag name from the url if it exists.
	if( array_key_exists( 'tag', $_REQUEST ) && $_REQUEST['tag'] !== '' && $_REQUEST['tag'] != '' )
		{
		// Make sure its a valid tag.
		$tags = get_tags();

		foreach( $tags as $tag )
			{
			if( $tag->name === $_REQUEST['tag'] )
				$options['tag'] = $tag->name;
			}
		}

	// Get the post type from the url if it exists.
	if( array_key_exists( 'post_type', $_REQUEST ) && $_REQUEST['post_type'] !== '' )
		{
		// Make sure its a valid tag.
		$post_types = get_post_types();

		foreach( $post_types as $post_type )
			{
			if( $post_type === $_REQUEST['post_type'] )
				$options['post_type'] = $post_type;
			}
		}

	// Get the search string from the url if it exists.
	if( array_key_exists( 's' , $_REQUEST ) && $_REQUEST['s'] !== '' )
		{
		// Note: as the search string is any arbitrary text, there is no way to validate beyond that it exists.
		$options['s'] = $_REQUEST['s'];
		}

	// Get the display month from the url if it exists.
	if( array_key_exists( 'm', $_REQUEST ) && $_REQUEST['m'] !== '' )
		{
		// Validate we have a properly formed YYYYMM string.
		if( strlen( $_REQUEST['m'] ) === 6 )
			{
			$year = intval( substr( $_REQUEST['m'], 0, 4 ) );
			$month = intval( substr( $_REQUEST['m'], -2, 2 ) );

			if( $year != 0 && ( $month >0 && $month < 13 ) )
				{
				$options['m'] = sprintf( '%04d%02d', $year, $month );
				}
			}
		}

	// Get the order by from the url if it exists.
	if( array_key_exists( 'orderby', $_REQUEST ) && $_REQUEST['orderby'] !== '' )
		{
		// Make sure we have a valid orderby type.
		$orderbytypes = array( 'none', 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'parent', 'rand', 'comment_count', 'relevance', 'menu_order', 'meta_value', 'meta_value_num', 'post__in', 'post_name__in', 'post_parent__in' );

		foreach( $orderbytypes as $type )
		{
			if( $type === $_REQUEST['orderby'] )
				{
				$options['orderby'] = $type;
				}
			}
		}

	// Get the order from the url if it exists.
	if( array_key_exists( 'order', $_REQUEST ) && $_REQUEST['order'] !== '' )
		{
		// Make sure we have a valid order type.
		$order = strtolower( $_REQUEST['order'] );

		if( $order === 'asc' || $order === 'desc' )
			{
			$options['order'] = $order;
			}
		}

	// Get the author the url if it exists.
	if( array_key_exists( 'author', $_REQUEST ) && intval( $_REQUEST['author'] ) > 0 )
		{
		// Make sure the author is an integer.
		$options['author'] = intval( $_REQUEST['author'] );
		}

	// "all" for post_status is not a valid option in WP_Query, it really means 'default', so strip it out if it's present.
	if( array_key_exists( 	'post_status', $_REQUEST ) &&
							$_REQUEST['post_status'] !== '' &&
							$_REQUEST['post_status'] !== 'all'
							&& in_array( $_REQUEST['post_status'], $avail_post_stati, true )
						)
		{
		$poststatuslist = array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash', 'any' );

		foreach( $poststatuslist as $status )
			{
			if( $status === $_REQUEST['post_status'] )
				{
				$options['post_status'] = $status;
				}
			}
		}

	return $options;
	}

/*
 	This function is called for each post/page in the post/page list to add the filter info to the edit links in the quick edit.
 */
function SmartPrevNextLinkRow( $actions, $post )
	{
	// Get the options from the URL.
	$options = SmartPrevNextBuildParams();

	// Bail if there are no options to add to the URL.
	if( sizeof( $options) === 0 ) { return $actions; }

	// Build the filter parameters in to a string to add to the edit urls.
	$params = '&amp;' . http_build_query( $options, '', '&amp;' );

	// Look for any edit URL in the quick actions and update them with the filter parameters.
	foreach( $actions as $key => $action )
		{
		$actions[$key] = preg_replace( '/post\.php\?post=(\d+)&amp;action=edit"/i', 'post.php?post=$1&amp;action=edit' . $params . '"', $action );
		}

	return $actions;
	}

/*
 	This function is called for each post/page in the post/page list to add the filter info to the edit links in the quick edit.
 */
function SmartPrevNextAdminMenu()
	{
	// Get the options from the URL.
	$options = SmartPrevNextBuildParams();

	// Build the filter parameters in to a string to add to the edit urls.
	$params = '&amp;' . http_build_query( $options, '', '&amp;' );

	if( sizeof( $options ) > 0 )
		{
		// We only want to add the filter to the page or posts menu items.
		if( array_key_exists( 'post_type', $options ) && $options['post_type'] == 'page' )
			{
			add_submenu_page( 'edit.php?post_type=page', __( 'Filtered', 'smart-prev-next' ), __( 'Filtered', 'smart-prev-next' ), 'edit_posts', "edit.php?post_type=page&$params", null, 1 );
			}
		else
			{
			add_submenu_page( 'edit.php', __( 'Filtered', 'smart-prev-next' ), __( 'Filtered', 'smart-prev-next' ), 'edit_posts', "edit.php?$params", null, 1 );
			}
		}
	}


/*
 	This function is called during a new page/post page.
*/
function SmartPrevNextEditor()
	{
	// Get the current screen.
	$current_screen = get_current_screen();

	// Check to see if we're in Gutenberg or not, and call the right code.
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() )
		{
		SmartPrevNextGutenbergEditor();
		}
	else
		{
		SmartPrevNextClassicEditor();
		}
	}

/*
	This function gets the query parameters and runs the query.
*/
function SmartPrevNextRunWPQuery()
	{
	// Get the options from the URL, if there are none, set the default.
	$options = SmartPrevNextBuildParams();
	if( sizeof( $options ) === 0 )
		{
		$options = array( 'post_status' => array( 'pending', 'draft', 'future', 'publish', 'private' ) );
		}

	// Create the parameters string for use later, do this before URL decoding the search string.
	$params = '';

	if( sizeof( $options ) > 0 )
		{
		// Convert the options in to a URL parameter list for later.
		$params = '&amp;' . http_build_query( $options, '', '&amp;' );
		}

	// Since the search string is URL encode when we receive it from SmartPrevNextBuildParams(), decode it before passing it to WP_Query().
	if( array_key_exists( 's', $options ) )
		{
		$options['s']  = urldecode( $options['s'] );
		}

	// Run the WP_Query to get the posts list.
	$query = new WP_Query( $options );

	return array( $query, $params );
	}

/*
	This function gets the first/next/prev/last posts from the WP_Query.
*/
function SmartPrevNextQueryPosts( $query )
	{
	// Make sure we got back a WP_Query object before working with it.
	if( ! is_object( $query ) ) { return array( null, null, null, null ); }

	// Find the post count.
	$post_count = sizeof( $query->posts );

	// Record the first/last posts objects from the query.
	$first_post = $query->posts[0];
	$last_post = $query->posts[$post_count-1];

	// Get our current post object.
	$current_post = get_post( null, 'OBJECT' );

	// Set the defaults for prev/next to be first/last.
	$previous_post = $first_post;
	$next_post = $last_post;

	// Brute force find the current post in the query post list.
	foreach( $query->posts as $key => $post )
		{
		// If we've found it, set the prev/next post values.
		if( $post->ID === $current_post->ID )
			{
			if( array_key_exists( $key - 1, $query->posts ) ) { $previous_post = $query->posts[$key - 1]; }
			if( array_key_exists( $key + 1, $query->posts ) ) { $next_post = $query->posts[$key + 1]; }

			break;
			}
		}

	// Check to see if our current post either the first or last post, if so, disable the appropriate buttons.
	if( $current_post->ID === $last_post->ID ) { $next_post = $last_post = null; }
	if( $current_post->ID === $first_post->ID ) { $previous_post = $first_post = null; }

	return array( $first_post, $next_post, $previous_post, $last_post );
	}

/*
 	This function is called during a new page/post page when using the Gutenberg editor.
*/
function SmartPrevNextGutenbergEditor()
	{
	// Register and enqueue our style sheet.
	wp_register_style( 'smart_prev_next_style', plugins_url( '', __FILE__ ) . '/smart-prev-next.css' );
	wp_enqueue_style( 'smart_prev_next_style' );

	// Check to see which location we're placing the buttons.
	//$location = 'div .interface-pinned-items';
	$location = 'div .edit-post-header__settings';

	// Get the current filtered query.
	list( $query, $params ) = SmartPrevNextRunWPQuery();

	// Figure out the first/next/prev/last posts from the query.
	list( $first_post, $next_post, $previous_post, $last_post) = SmartPrevNextQueryPosts( $query );

	// Finally time to create the HTML for the next/last buttons.
	if( is_object( $next_post ) )
		{
		$next_post_button = '<a href="' . get_edit_post_link( $next_post->ID ) . $params . '" title="' . __( 'Next Post: ', 'smart-prev-next' ) . esc_attr( $next_post->post_title ) . '" class="components-button is-button is-secondary is-large spn-next-post"><span class="dashicons dashicons-controls-forward"></span></a>';
		$last_post_button = '<a href="' . get_edit_post_link( $last_post->ID ) . $params . '" title="' . __( 'Last Post: ', 'smart-prev-next' ) . esc_attr( $last_post->post_title ) . '" class="components-button is-button is-secondary is-large spn-next-post"><span class="dashicons dashicons-controls-skipforward"></span></a>';
		}
	else
		{
		$next_post_button = '<a aria-disabled="true" class="components-button is-button is-secondary is-large spn-next-post"><span class="dashicons dashicons-controls-forward"></span></a>';
		$last_post_button = '<a aria-disabled="true" class="components-button is-button is-secondary is-large spn-next-post"><span class="dashicons dashicons-controls-skipforward"></span></a>';
		}

	// Finally time to create the HTML for the prev/first buttons.
	if( is_object( $previous_post ) )
		{
		$previous_post_button = '<a href="' . get_edit_post_link( $previous_post->ID ) . $params . '" title="' . __( 'Previous Post: ', 'smart-prev-next' ) . esc_attr( $previous_post->post_title ) . '" class="components-button is-button is-secondary is-large spn-prev-post"><span class="dashicons dashicons-controls-back"></span></a>';
		$first_post_button = '<a href="' . get_edit_post_link( $first_post->ID ) . $params . '" title="' . __( 'First Post: ', 'smart-prev-next' ) . esc_attr( $first_post->post_title ) . '" class="components-button is-button is-secondary is-large spn-prev-post"><span class="dashicons dashicons-controls-skipback"></span></a>';
		}
	else
		{
		$previous_post_button = '<a aria-disabled="true" class="components-button is-button is-secondary is-large spn-prev-post"><span class="dashicons dashicons-controls-back"></span></a>';
		$first_post_button = '<a aria-disabled="true" class="components-button is-button is-secondary is-large spn-prev-post"><span class="dashicons dashicons-controls-skipback"></span></a>';
		}

?>
    <script>
    	function SmartPrevNextPrevNext() {
    		var pinned_items = jQuery( '<?php echo $location; ?>' );

    		if( typeof pinned_items === 'object' ) {
				pinned_items.before( '<?php echo $first_post_button . $previous_post_button . $next_post_button . $last_post_button; ?>' );
			} else {
				setTimeout( 'SmartPrevNextAddPrevNext()', 250 );
			}
    	}

		jQuery( document ).ready( function( $ ) {
			setTimeout( 'SmartPrevNextPrevNext()', 100 );
		} );
    </script>
<?php
	}

/*
 	This function is called during a new page/post page when using the classic editor.
*/
function SmartPrevNextClassicEditor()
	{
	// Register and enqueue our style sheet.
	wp_register_style( 'smart_prev_next_style', plugins_url( '', __FILE__ ) . '/smart-prev-next.css' );
	wp_enqueue_style( 'smart_prev_next_style' );

	// Check to see which location we're placing the buttons.
	//$location = 'div .interface-pinned-items';
	$location = '.page-title-action';

	// Get the current filtered query.
	list( $query, $params ) = SmartPrevNextRunWPQuery();

	// Figure out the first/next/prev/last posts from the query.
	list( $first_post, $next_post, $previous_post, $last_post) = SmartPrevNextQueryPosts( $query );

	// Finally time to create the HTML for the next/last buttons.
	if( is_object( $next_post ) )
		{
		$next_post_button = '<a href="' . get_edit_post_link( $next_post->ID ) . $params . '" title="' . __( 'Next Post: ', 'smart-prev-next' ) . esc_attr( $next_post->post_title ) . '" class="page-title-action spn-next-post">' . __( 'Next', 'smart-prev-next' ) . '</a>';
		$last_post_button = '<a href="' . get_edit_post_link( $last_post->ID ) . $params . '" title="' . __( 'Last Post: ', 'smart-prev-next' ) . esc_attr( $last_post->post_title ) . '" class="page-title-action spn-next-post">' . __( 'Last', 'smart-prev-next' ) . '</a>';
		}
	else
		{
		$next_post_button = '<a aria-disabled="true" class="page-title-action button-primary-disabled spn-next-post">' . __( 'Next', 'smart-prev-next' ) . '</a>';
		$last_post_button = '<a aria-disabled="true" class="page-title-action button-primary-disabled spn-next-post">' . __( 'Last', 'smart-prev-next' ) . '</a>';
		}

	// Finally time to create the HTML for the prev/first buttons.
	if( is_object( $previous_post ) )
		{
		$previous_post_button = '<a href="' . get_edit_post_link( $previous_post->ID ) . $params . '" title="' . __( 'Previous Post: ', 'smart-prev-next' ) . esc_attr( $previous_post->post_title ) . '" class="page-title-action disabled spn-prev-post">' . __( 'Prev', 'smart-prev-next' ) . '</a>';
		$first_post_button = '<a href="' . get_edit_post_link( $first_post->ID ) . $params . '" title="' . __( 'First Post: ', 'smart-prev-next' ) . esc_attr( $first_post->post_title ) . '" class="page-title-action spn-prev-post">First</span></a>';
		}
	else
		{
		$previous_post_button = '<a aria-disabled="true" class="page-title-action button-primary-disabled spn-prev-post">Prev</a>';
		$first_post_button = '<a aria-disabled="true" class="page-title-action button-primary-disabled spn-prev-post">First</a>';
		}

?>
    <script>
		jQuery( document ).ready( function( $ ) {
    		var pinned_items = jQuery( '<?php echo $location; ?>' );

			pinned_items.before( '<?php echo $first_post_button . $previous_post_button . $next_post_button . $last_post_button; ?>' );
		} );
    </script>
<?php
	}

/*
	This function alters the post link values in the main post/pages list if there options set.
*/
function SmartPrevNextEditPostLink( $link, $ID, $context )
	{
	// Get the options from the URL.
	$options = SmartPrevNextBuildParams();

	// Bail if there are no options to add to the URL.
	if( sizeof( $options) === 0 ) { return $link; }

	// Build the filter parameters in to a string to add to the edit urls.
	$params = '&amp;' . http_build_query( $options, '', '&amp;' );

	// Replace the edit link if required.
	$link = preg_replace( '/post\.php\?post=(\d+)&amp;action=edit/i', 'post.php?post=$1&amp;action=edit' . $params, $link );

	return $link;
	}
