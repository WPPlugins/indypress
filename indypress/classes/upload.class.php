<?php
class indypress_upload {

	function indypress_upload() {
		add_action('wp_ajax_nopriv_indypressupload', array(&$this, 'ajax_upload'));
		add_action('wp_ajax_indypressupload', array(&$this, 'ajax_upload'));
		$this->load_settings();
	}

	function load_settings() {
		$this->author = get_option( 'indypress_author' );
		$this->publication_page = get_option( 'indypress_publication_page' );

		$this->preview = get_option( 'indypress_preview' );
	}

	function ajax_upload() {
		//This will manage the upload using admin-ajax
		function indypress_upload_process($publication) {
			$info = array();
			$info['status'] = 'success';
			// CHECK IF FILETYPE IS UPLOADABLE
			if( $_POST['type']=='audio' || $_POST['type']=='image' || $_POST['type']=='attachment' || $_POST['type']=='video' ) {

				$file = $publication->upload( $_POST['type'], $_FILES['indypress_upload_file'] );
				if( $file==-1 ) {
					$info['status'] = 'error';
					$info['error'] = __('File size ecceded', 'indypress');
					$result = -1;
				} elseif( $file==-2 ) {
					$info['status'] = 'error';
					$info['error'] = __('Error', 'indypress');
				} elseif( $file ) {
					;
				} else {
					$info['status'] = 'error';
					$info['error'] = __('It is not possible to load this file', 'indypress');
				}
				if($info['status'] == 'error')
					return $info;

				//TODO: remove tinymce specific, output only json
				// INSERT FILE INTO TINYMCE AND ADD IT TO LIST FILE
				if(isset($file['converted']))
					$fileinfo = $file['converted'];
				else
					$fileinfo = $file['original'];
				$info['url'] = $fileinfo['url'];
				$info['name'] = $fileinfo['name'];
				$info['type'] = $_POST['type'];

				return $info;
			}

		}
		$info = indypress_upload_process($this);
		if(function_exists('json_encode')) {
			$str = json_encode($info);
			echo $str;
		}
		else {
			print_r($info);
		}
		die();
	}
	function upload( $accepted_type, $file ) {

		global $indypress_path;

		// CHECK UPLOAD ERROR
		if( $file['error']==0 ) {

			// INCLUDING WP API AND MYCONFIG
			require_once ( ABSPATH . 'wp-admin/includes/image.php' );
			require_once ( $indypress_path . 'config.php' );
			global $accepted_file;

			// SANITIZE FILE
			$this->sanitize_file( $file );

			// MOVE UPLOADED TEMP FILE TO THE DIRECTORY
			$filename = $file['name'];
			$target = $this->check_upload_directory( $filename );
			$filetype = $this->check_file_type( $file['tmp_name'] );

			// CHECK FILETYPE
			foreach( $accepted_file[$accepted_type] as $check => $value ) {
				if( $value == $filetype && move_uploaded_file( $file['tmp_name'], $target['path'] ) ) {

					// ATTACH UPLOADED FILE TO POST
					$attach_id = $this->file_into_database( $filetype, $filename, $target );
					if( !$attach_id ) die (__('Upload directory creation error', 'indypress'));

					// SAVE UPLOAD IN SESSION
					$this->file_into_session( $attach_id, $accepted_type, $target['uri'], $filename );
					
					// SET RESULT LIKE $target_uri
					$return = array(
						'url' => $target['uri'],
						'name' => $filename,
						'target_path' => $target['path'],
						'mime_type' => $filetype
					);
					
					$ret['original'] = $return;

					// IF IS A VIDEO CONVERT IT IN A FLV VIDEO					
					if( $accepted_type == 'video' )
						$ret['converted'] = $this->convert_file( 'video', $target['path'], $filename );

					// IF IS A VIDEO CONVERT IT IN A FLV VIDEO					
					if( $accepted_type == 'audio' )
						$ret['converted'] = $this->convert_file( 'audio', $target['path'], $filename );
					
					return $ret;

				}
			}

			// RETURN ERROR
			return 0;

		} elseif( $file['error'] )
			return -1;
		else
			return -2;

	}
	
	function convert_file( $type, $filepath, $filename ) {

		// IF IS A VIDEO CONVERT IT IN A MPG4 VIDEO					
		if( $type == 'video' ) {

			$converted_file = $this->convert_video_to_mp4( $filepath );

			if( $converted_file ) {

				$converted_filename = $filename . '.mp4';

				// ATTACH UPLOADED FILE TO POST
				$target = $this->check_upload_directory( $converted_file , 0);
				$attach_id = $this->file_into_database( 'video/mp4', $converted_filename, $target );

				// MAKE AN ANIMATED GIF FROM THE VIDEO
				$thumbnail = make_animated_gif( $converted_file, $attach_id, 5, $target );

				// SAVE UPLOAD IN SESSION
				$this->file_into_session( $attach_id, $type, $target['uri'], $converted_filename );
							
				// SET RESULT LIKE $target_uri
				$return = array(
					'url' => $target['uri'],
					'name' => $converted_filename,
					'target_path' => $target['path'],
					'mime_type' => 'video/mp4'
				);

				return $return;
			}
		} elseif( $type == 'audio' ) {

			$converted_file = $this->convert_audio( $filepath );

			if( $converted_file ) {

				$converted_filename = $filename . '.mp3';

				// ATTACH UPLOADED FILE TO POST
				$target = $this->check_upload_directory( $converted_file , 0);
				$attach_id = $this->file_into_database( 'audio/mpeg', $converted_filename, $target );

				// SAVE UPLOAD IN SESSION
				$this->file_into_session( $attach_id, $type, $target['uri'], $converted_filename );
							
				// SET RESULT LIKE $target_uri
				$return = array(
					'url' => $target['uri'],
					'name' => $converted_filename,
					'target_path' => $target['path'],
					'mime_type' => 'audio/mpeg'
				);

				return $return;
			}
		}
	}
/*
	function convert_video( $filename ) {
		// Set our source file
		$srcFile = $filename;
		$destFile = $filename . '.flv';
		$ffmpegPath = "/usr/bin/ffmpeg";
		$flvtool2Path = "/usr/bin/flvtool2";
		// Create our FFMPEG-PHP class
		$ffmpegObj = new ffmpeg_movie($srcFile);
		// Save our needed variables
		$srcWidth = $this->makeMultipleTwo( $ffmpegObj->getFrameWidth() );
		$srcHeight = $this->makeMultipleTwo( $ffmpegObj->getFrameHeight() );
		$srcFPS = $ffmpegObj->getFrameRate();
		//$srcAB = intval( $ffmpegObj->getAudioBitRate()/1000 );
		$srcAR = $ffmpegObj->getAudioSampleRate();
		// Call our convert using exec()
		$string = $ffmpegPath . " -i \"" . $srcFile . "\" -ar " . $srcAR . " -ab 32 -f flv -s " . $srcWidth . "x" . $srcHeight . " \"" . $destFile . "\" | " . $flvtool2Path . " -U stdin \"" . $destFile . "\"";
		exec( $string );

		return $destFile;
	}
*/

	function make_animated_gif( $filepath, $filename, $number, $target ) {

		global $indypress_path;
		$temp_dir = $indypress_path . 'tmp/'. $filename;
		mkdir( $temp_dir );
		echo "TEMP $temp_dir<br>";

		$video = new ffmpeg_movie( $filepath );
		$total_frames_number = $video->getFrameCount();
		$base = floor( $total_frames_number / $number );
		echo "FRAMES: $total_fames_number-$base";

		// Make $number frames
		for( $i=1; $i<=$number; $i++ ) {
			$frame = $video->getFrame( $base * $i );
			if( $frame ) {
				$gd_frame = $frame->toGDImage();
				if( $gd_frame ) {
					imagegif( $gd_frame, $temp_dir . '/' . $i . '.gif' );
					imagedestroy( $gd_frame );
				}
			}
		}

		// Merge all frames into an animated GIF
		$animated_path = $temp_dir . '/animated.gif';
		echo "$animated_path <br>";
		exec( '/usr/bin/convert -delay 2 -loop 10 ' . $temp_dir . '/test*.gif ' . $animated_path );
		echo  '/usr/bin/convert -delay 2 -loop 10 ' . $temp_dir . '/test*.gif ' . $animated_path . "<br>";

		// ATTACH UPLOADED FILE TO POST
		//$final_animated_path = $tarteg['dest_path'] . '/' . $attach_id . '-animated.gif';
		$final_animated_path = $filepath . '.gif';
		$animated_name = $filename . '.gif';
		echo "$final_animated_path - $animated_name";
		$target = $this->check_upload_directory( $final_animated_path , 0);
		print_r($target);
		copy( $animated_path, $final_animated_path );
		//$attach_id = $this->file_into_database( 'image/gif', $animated_name, $target );

		// SAVE UPLOAD IN SESSION
		//$this->file_into_session( $attach_id, 'image', $target['uri'], $final_animated_path );

	}

	function convert_video_to_mp4( $filename ) {
		// Set our source file
		$srcFile = $filename;
		$destFile = $filename . '.mp4';
		$ffmpegPath = "/usr/bin/ffmpeg";
		// Create our FFMPEG-PHP class
		$ffmpegObj = new ffmpeg_movie($srcFile);
		// Save our needed variables
		$srcWidth = $this->makeMultipleTwo( $ffmpegObj->getFrameWidth() );
		$srcHeight = $this->makeMultipleTwo( $ffmpegObj->getFrameHeight() );
		$srcFPS = $ffmpegObj->getFrameRate();
		$srcAB = intval( $ffmpegObj->getAudioBitRate()/1000 );
		$srcAR = $ffmpegObj->getAudioSampleRate();
		// Call our convert using exec()
		$string = $ffmpegPath . " -i \"" . $srcFile . "\" -ar " . $srcAR . " -ab 32 -f flv -vcodec libx264 -s " . $srcWidth . "x" . $srcHeight . " \"" . $destFile . "\"";
		exec( $string ); echo $string;

		return $destFile;
	}

	function convert_audio( $filename ) {
		// Set our source file
		$srcFile = $filename;
		$destFile = $filename . '.mp3';
		$ffmpegPath = "/usr/bin/ffmpeg";
		// Call our convert using exec()
		$string = $ffmpegPath . " -i \"" . $srcFile . "\" \"" . $destFile . "\"";
		exec( $string );

		return $destFile;
	}

	function check_file_type( $file ) {

		if( function_exists( 'finfo_open' ) && false ) {
			$finfo = finfo_open( FILEINFO_MIME_TYPE ); // return mime type ala mimetype extension
			foreach ( glob( $file ) as $filename ) {
				return finfo_file( $finfo, $filename );
			}
			finfo_close( $finfo );
		} else {
			return mime_content_type( $file );
		}
	}

	function check_upload_directory( $filename, $add_time = 1 ) {
		$destination_path = wp_upload_dir();

		if( $add_time ) {
			$target_path = $destination_path['path'] . '/' . time() . '-' . basename( $filename );
			$target_uri = $destination_path['url'] . '/' . time() . '-' . basename( $filename );
		} else {
			$target_path = $destination_path['path'] . '/' . basename( $filename );
			$target_uri = $destination_path['url'] . '/' . basename( $filename );
		}

		if( !is_dir( $destination_path['path'] ) ) mkdir( $destination_path["path"], 0777, true );

		return array( 'path' => $target_path, 'uri' => $target_uri, 'dest_path' => $destination['path'] );
	}

	function file_into_database( $filetype, $filename, $target ) {
		if(isset($_POST['alt']) && $_POST['alt'])
			$title = $_POST['alt'] . '-';
		else
			$title = '';
		$title .= preg_replace( '/\.[^.]+$/', '', basename( $filename ) );
		$attachment = array(
			'post_mime_type' => $filetype,
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $target['uri']
		);

		$attach_id = wp_insert_attachment( $attachment, $target['path'] );
		if( $accepted_type == 'image' )
			wp_create_thumbnail( $attach_id, 90 );
		$attach_data = wp_generate_attachment_metadata( $attach_id, $target['path'] );
		wp_update_attachment_metadata( $attach_id,  $attach_data );

		return $attach_id;
	}

	function file_into_session( $attach_id, $accepted_type, $url, $name ) {

		if(!session_id())
			session_start();
		$sess_attach = array(
			'ID' => $attach_id,
			'type' => $accepted_type,
			'url' => $url,
			'name' => htmlspecialchars( $name )
		);
		$_SESSION['indypress_attachment'][] = $sess_attach;
	}
	
	// Make multiples function
	function makeMultipleTwo ($value) {
		$sType = gettype($value/2);
		if($sType == "integer") {
			return $value;
		} else {
			return ($value-1);
		}
	}

	function sanitize_file( &$file ) {
		// http://es.php.net/manual/en/filter.filters.validate.php
		//$file['name'] = filter_var( $file['name'], FILTER_SANITIZE_STRING );
		$file['name'] = preg_replace( "([^a-zA-Z0-9\.\ ])", "", $file['name']);
		$file['name'] = preg_replace( "(\ )", "_", $file['name']);
		return $file['name'];
	}

	function fix_names( $name ) {
		//NOTE: for some reason, when this get called post is already present in db
		global $wpdb;
		$querystr = "
			SELECT post_name FROM $wpdb->posts
			WHERE post_name LIKE '$name%'";
		$used_names = $wpdb->get_col( $querystr );
		if( count( $used_names ) <= 1)
			return $name;
		$i = 1;
		$newname = $name;
		while( in_array( $newname, $used_names ) ) {
			$i = $i + 1;
			$newname = $name . '-' . $i;
		}
		return $newname;
	}


}

?>
