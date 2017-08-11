<?php

$pattern_audio = '(<(\w+) href="(.*?)">(.*?)(' . preg_quote($indypress_url) . 'images/audioplayer\.png)(.*?)</\1>)';
$replace_audio = '[audio:${2}]';

$pattern_video = '(<(\w+) href\="(.*?)"([^>]*?)>(.*?)(' . preg_quote($indypress_url) . 'images/videoplayer\.png)(.*?)</\1>)';
$replace_video = '[flowplayer src=${2}]';

$extern_pattern_audio = '([indypressaudio src="(.*?)"])';
$extern_replace_audio = '[audio src="${1}"]';

$extern_pattern_video = '(indypressvideo src="(.*?)")';
$extern_replace_video = '[flowplayer src=${1}]';

$extern_audio_tag = '';
$extern_video_tag = '[flowplayer src=${2}]';

$accepted_file = array(
	'image' => array(
		'jpeg' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
		'tiff' => 'image/tiff',
		),
	'audio' => array(
		'wav' => 'audio/x-wav',
		'mpga mpega mp2 mp3 m4a' => 'audio/mpeg',
		'oga ogg spx' => 'audio/ogg',
		'flac' => 'audio/flac',
		'wma' => 'audio/x-ms-wax',
		'ra rm ram' => 'audio/x-pn-realaudio',
		'ra' => 'audio/x-realaudio'
		),
	'video' => array(
		'ogg' => 'video/ogg ',
		'qt mov' => 'video/quicktime',
		'mpeg mpg mpe' => 'video/mpeg',
		'mpeg' => 'video/mp4',
		'avi' => 'video/x-msvideo',
		'wmv' => 'video/x-ms-wmv ',
		'flv' => 'video/x-flv'
		)
);

?>
