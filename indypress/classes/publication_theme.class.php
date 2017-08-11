<?php

class indypress_publication_theme {

	function indypress_publication_theme() {
		add_filter( 'the_content', array( $this, 'replace_multimedia_tags' ), 0 );
		add_filter( 'the_author', array( $this, 'author' ) );
		add_filter( 'author_link', array( $this, 'author_site' ) );
		if( get_option( 'indypress_thumb_attachment' ) )
			add_filter( 'the_excerpt', array( $this, 'first_attachment' ) );
		if( get_option( 'indypressevent_info_top' ) )
			add_filter( 'the_content', array( $this, 'events_fields' ) );
		
		add_filter( 'the_content', array( $this, 'load_attachments' ) );
		add_filter( 'the_excerpt', array( $this, 'events_fields' ) );

		// Add "media_embed" class to appropriate posts
		add_filter( 'post_class', array( $this, 'embed_class' ) );
	}

	function embed_class( $classes ) {
		global $post;
		if ( get_post_meta( $post->ID, 'indypress_embed', TRUE ) === '1' )
			$classes[] = 'media_embed';
		return $classes;
	}

	function author( $author ) {
		global $post;

		$custom_author = get_post_meta( $post->ID, 'post_author_name', TRUE );
		if( $custom_author )
			return $custom_author;
		elseif( $author==NULL )
			return get_bloginfo( 'name' );
		else
			return $author;
	}

	function author_site( $author_site ) {
		global $post;

		$custom_author = get_post_meta( $post->ID, 'post_author_name', TRUE );
		if( $custom_author ) {
			$custom_author_site = get_post_meta( $post->ID, 'post_author_link', TRUE );
			if( $custom_author_site )
				return $custom_author_site;

		} else
			return $author_site;
	}

	// SHOW FIRST ATTACHMENT IN THE EXCERPT

	function first_attachment( $excerpt ) {
		global $post;
		global $audio_player_image_url;

		//Get images attached to the post
		$args = array(
		'post_type' => 'attachment',
			'numberposts' => -1,
			'order' => 'ASC',
			'post_status' => null,
			'post_parent' => $post->ID
		);
		$attachments = get_posts( $args );
		$return = "";
		if ( $attachments ) {
			foreach ( $attachments as $attachment ) {
				$url = wp_get_attachment_thumb_url( $attachment->ID );
				if( !$url ) $url = $attachment->guid;
				$mime_type = $attachment->post_mime_type;
				break;
			}
			switch( $mime_type ) {
				case "image/png":
					$return = '<img src="' . $url . '" align="left" style="margin:5px;">';
					break;
				case "image/jpeg":
					$return = '<img src="' . $url . '" align="left" style="margin:5px;">';
					break;
				case "image/jpg":
					$return = '<img src="' . $url . '" align="left" style="margin:5px;">';
					break;
				case "image/gif":
					$return = '<img src="' . $url . '" align="left" style="margin:5px;">';
					break;
				case "audio/mpeg":
					$return = '<img src="' . $audio_player_image_url . '" align="left" style="margin:5px;">';
					break;
				case "audio/x-realaudio":
					$return = '<img src="' . $audio_player_image_url . '" align="left" style="margin:5px;">';
					break;
				case "audio/wav":
					$return = '<img src="' . $audio_player_image_url . '" align="left" style="margin:5px;">';
					break;
				case "audio/ogg":
					$return = '<img src="' . $audio_player_image_url . '" align="left" style="margin:5px;">';
					break;
			}
		}

		return $return . $excerpt;
	}

	function events_fields( $content ) {
		global $post;

		$event_start = get_post_meta( $post->ID, 'event_start', TRUE );
		$event_end = get_post_meta( $post->ID, 'event_end', TRUE );

		if( $event_start && $event_end ) {
			return the_event_information() . $content;
		}
		return $content;

	}

	// SHOW ATTACHMENT LIST OF SINGLE POST
	function load_attachments( $content ) {
		global $post, $indypress_url;

		if( is_single() ) {
			//Get images attached to the post
			$args = array(
				'post_type' => 'attachment',
				'numberposts' => -1,
				'order' => 'ASC',
				'post_status' => null,
				'post_parent' => $post->ID
			);
			$attachments = get_posts( $args );

			$return = "";
			if ( $attachments ) {
				$return = '<br /><h3>' . __('Attached files', 'indypress') . '</h3><div class="indypress_attachment_list">';
				foreach ( $attachments as $attachment ) {
					$return .= '<div>';
					switch( $attachment->post_mime_type ) {
						case "image/png":
							$return .= '<img src="' . $indypress_url . '/images/image_icon.gif" />';
							break;
						case "image/jpeg":
							$return .= '<img src="' . $indypress_url . '/images/image_icon.gif" />';
							break;
						case "image/jpg":
							$return .= '<img src="' . $indypress_url . '/images/image_icon.gif" />';
							break;
						case "image/gif":
							$return .= '<img src="' . $indypress_url . '/images/image_icon.gif" />';
							break;
						case "audio/mpeg":
							$return .= '<img src="' . $indypress_url . '/images/audio_icon.gif" />';
							break;
						case "audio/x-realaudio":
							$return .= '<img src="' . $indypress_url . '/images/audio_icon.gif" />';
							break;
						case "audio/wav":
							$return .= '<img src="' . $indypress_url . '/images/audio_icon.gif" />';
							break;
						case "audio/ogg":
							$return .= '<img src="' . $indypress_url . '/images/audio_icon.gif" />';
							break;
					}
					$return .= '<a href="' . $attachment->guid . '">' . $attachment->post_title . '</a></div>';
				}
				$return .= '</div>';
			}

			return $content . "<p>" . $return . "</p>";
		} else
			return $content;
	}

	function replace_multimedia_tags( $content ) {
		global $pattern_audio, $replace_audio, $pattern_video, $replace_video;
		global $extern_pattern_audio, $extern_replace_audio, $extern_pattern_video, $extern_replace_video;
		//echo $extern_pattern_video . "saffasfsa ". $extern_replace_video;
		return preg_replace( $extern_pattern_video, $extern_replace_video, $content );
	}
}

?>
