<?php


class DrupalApi {
	function __construct() {
	}

	function jsonResonse($data){
		return json_encode(array(
			'retrievedAt' => date('c'),
			'data' => $data
		));
	}

	function fileUrl($uri, $style= '') {
		if(empty($uri))
			return $uri;

		if (empty($style)) {
			return BASE_URL.'/'.variable_get('file_public_path','').'/'.file_uri_target($uri);
		}
		else {
			return BASE_URL.'/'.variable_get('file_public_path','').'/styles/news_thumbnail/public/'.file_uri_target($uri);
		}


	}

	function videoUrl($uri) {
		if(empty($uri))
			return $uri;

		$scheme = file_uri_scheme($uri);
		$filename = file_uri_target(trim($uri, '/'));
		if($scheme == 'youtube') {
			return PROTOCOL.'youtube.com/'.$filename;
		}
		if($scheme == 'yandex') {
			return PROTOCOL.'static.video.yandex.net/lite/'.$filename;
		}
		if($scheme == 'vimeo') {
			return PROTOCOL.'player.vimeo.com/video/'.substr($filename,strrpos($filename, '/') + 1);
		}
		if($scheme == 'rutube') {
			return PROTOCOL.'rutube.ru/play/embed/'.substr($filename,strrpos($filename, '/') + 1);
		}
		if($scheme == 'public' && strpos($filename, 'video_ext') === 0) {
			if(strpos($filename, 'video_ext.php?') !== 0) {
				$filename = preg_replace("/(.*?)\.php/",'video_ext.php', $filename);
			}
			return PROTOCOL.'vk.com/'.$filename;
		}
	}

	protected function getUser($uid, $uuid) {
		if(empty($uid) || empty($uuid)) {
			return null;
		}

		$name = db_select('users', 'u')
			->fields('u', array('name'))
			->condition('uid', $uid)
			->condition('uuid', $uuid)
			->range(0,1)
			->execute()
			->fetchField();

		if(empty($name)) {
			return false;
		} else {
			return $name;
		}

	}

} 
