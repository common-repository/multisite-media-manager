<?php

/**
 * The plugin information file
 *
 * @link              http://pradhanp.com.np
 * @since             1.0.0
 * @package           Multisite_Media_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Multisite Media Manager
 * Plugin URI:        #
 * Description:       Multisite Media Manager is a Wordpress plugin to manage all the uploaded media to one central folder making it accessible across all the Multisite Network.
 * Version:           1.0.0
 * Author:            Pashupati Pradhan
 * Author URI:        http://pradhanp.com.np
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       multisite-media-manager
 * Domain Path:       /languages
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Id of the parent site where all the media will be stored.
 * Usually the parent site ID is used.
 *
 * @var    integer
 */
const MUMM_PARENT_ID = 1;

/**
 * List all the uploaded media from the parent folders
 */
function mumm_ajax_query_attachments() {

	switch_to_blog( MUMM_PARENT_ID );

	wp_ajax_query_attachments();

	restore_current_blog();

	exit;
}

add_action( 'wp_ajax_query-attachments', 'mumm_ajax_query_attachments', 0 );


/**
 * Send media via AJAX call to editor
 *
 * @since   2015-01-26
 * @return  void
 */
function mumm_ajax_send_attachment_to_editor() {

	$attachment = wp_unslash( $_POST['attachment'] );

	$_POST['attachment'] = wp_slash( $attachment );

	switch_to_blog( MUMM_PARENT_ID );
	wp_ajax_send_attachment_to_editor();

	restore_current_blog();

	exit();
}

add_action( 'wp_ajax_send-attachment-to-editor', 'mumm_ajax_send_attachment_to_editor', 0 );

/**
 * Override Featured Image HTML meta box to support multisite media manager
 *
 * @param $content
 * @param $post
 * @param $thumbnail_id
 *
 * @return string
 */
function mumm_featured_image_html( $content, $post, $thumbnail_id ) {

	switch_to_blog( MUMM_PARENT_ID );

	$_wp_additional_image_sizes = wp_get_additional_image_sizes();

	$post = get_post( $post );

	$set_thumbnail_link = '<p class="hide-if-no-js"><a href="%s" id="set-post-thumbnail"%s class="thickbox">%s</a></p>';
	$upload_iframe_src  = get_upload_iframe_src( 'image', $post->ID );

	$content = sprintf( $set_thumbnail_link,
		esc_url( $upload_iframe_src ),
		'', // Empty when there's no featured image set, `aria-describedby` attribute otherwise.
		esc_html( __( 'Set featured Image' ) )
	);

	if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
		$size = isset( $_wp_additional_image_sizes['post-thumbnail'] ) ? 'post-thumbnail' : [ 266, 266 ];

		$size = apply_filters( 'admin_post_thumbnail_size', $size, $thumbnail_id, $post );

		$thumbnail_html = wp_get_attachment_image( $thumbnail_id, $size );

		if ( ! empty( $thumbnail_html ) ) {
			$content = sprintf( $set_thumbnail_link,
				esc_url( $upload_iframe_src ),
				' aria-describedby="set-post-thumbnail-desc"',
				$thumbnail_html
			);
			$content .= '<p class="hide-if-no-js howto" id="set-post-thumbnail-desc">' . __( 'Click the image to edit or update' ) . '</p>';
			$content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail">' . __( 'Remove featured image' ) . '</a></p>';
		}
	}

	$content .= '<input type="hidden" id="_thumbnail_id" name="_thumbnail_id" value="' . esc_attr( $thumbnail_id ? $thumbnail_id : '-1' ) . '" />';

	restore_current_blog();

	return $content;

}

add_filter( 'admin_post_thumbnail_html', 'mumm_featured_image_html', 1, 3 );


/**
 * Save the selected featured image for the child blog
 *
 * @param $post_id
 *
 * @return bool|int
 */
function mumm_save_featured_image( $post_id ) {
	if ( isset( $_POST["_thumbnail_id"] ) && intval( $_POST["_thumbnail_id"] ) ) {
		switch_to_blog( MUMM_PARENT_ID );

		$thumbnail_id = intval( $_POST["_thumbnail_id"] );

		if ( wp_get_attachment_image( $thumbnail_id, 'thumbnail' ) ) {
			restore_current_blog();

			return update_post_meta( $post_id, '_thumbnail_id', $thumbnail_id );
		} else {
			restore_current_blog();

			return delete_post_meta( $post_id, '_thumbnail_id' );
		}
	}

	return false;
}

add_action( 'save_post', 'mumm_save_featured_image', 0, 1 );


/**
 * Delete the feature image attachment when the post is deleted
 */
function mumm_delete_featured_image() {
	if ( ! empty( $_POST ) && $_POST['action'] == 'delete-post' ) {
		switch_to_blog( MUMM_PARENT_ID );
	}
}

add_action( 'wp_ajax_delete_post', 'mumm_delete_featured_image' );

/**
 * Pull the featured image path to be displayed on the template
 * Calling this function will return the image path
 *
 * @param string $size
 * @param int $post_id
 *
 * @return string
 */
function mumm_get_featured_image( $size = 'full', $post_id = 0 ) {
	global $post;

	if ( $post_id < 1 || ! $post_id ) {
		$post_id = $post->ID;
	}

	$thumbnail = '';

	if ( has_post_thumbnail( $post_id ) ) {
		$thumbId = get_post_thumbnail_id( $post_id );

		switch_to_blog( MUMM_PARENT_ID );

		$thumbnail = wp_get_attachment_image_src( $thumbId, $size )[0];

		restore_current_blog();
	}

	return $thumbnail;
}
