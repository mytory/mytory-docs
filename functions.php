<?php

function check_config_error(){
	global $doc_roots;
	foreach($doc_roots as $root){
		if( ! $result = is_dir($root)){
            echo "$root is not a real directory or server has not permission: " . __FILE__ . " : " . __LINE__;
			exit;
		}
	}
}

function get_filename_or_md_headline($dir, $file){
	$full_path = realpath($dir . '/' . $file);

	$content = file_get_contents($full_path);

	preg_match('/^#(.*)#*\n|(.*)\n={3,}/', $content, $match);

	if(empty($match)){
		return $file;
	}else{
		return trim($match[1] ? $match[1] : $match[2]);
	}
}

function get_parent_folder($relative_path){
	$temp = explode('/', $relative_path);
	if(count($temp) === 1){
		return '';
	}else{
		array_pop($temp);
		return implode('/', $temp);
	}
}

function is_target_ext($full_path){
	global $ext_list;
	return in_array(pathinfo($full_path, PATHINFO_EXTENSION), $ext_list);
}

function get_cmd_type(){
	if( ! isset($_REQUEST['path'])){
		return null;
	}

	$temp = explode(':', $_REQUEST['path']);
	$type = $temp[0];
	return $type;
}

function parse_path(){
	if( ! isset($_REQUEST['path'])){
		return null;
	}

	global $doc_roots;

	$temp = explode(':', $_REQUEST['path']);
	array_shift($temp);
	$full_path = implode('/', $temp);
	$temp2 = explode('/', $full_path);

	$root = array_shift($temp2);
	if( ! isset($doc_roots[$root])){
		echo "잘못된 경로 type1 : " . __FILE__ . " : " . __LINE__;
		exit;
	}

	$root_path = realpath($doc_roots[$root]);
	$real_full_path = $root_path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $temp2);

	if( ! is_file($real_full_path) AND ! is_dir($real_full_path)){
		echo '잘못된 경로 type2. ' . $real_full_path . " : " . __FILE__ . " : " . __LINE__;;
		exit;
	}

	$file = '';
	$full_file = '';
	$real_full_file = '';
	if(is_file($real_full_path)){
		$file = array_pop($temp2);
		$full_file = $full_path;
		$real_full_file = $real_full_path;
		$full_path = str_replace('/' . $file, '', $full_path);
		$real_full_path = str_replace(DIRECTORY_SEPARATOR . $file, '', $real_full_path);
	}

	$relative_path = implode('/', $temp2);

	return array(
		'root' => $root,
		'root_path' => $root_path,
		'relative_path' => $relative_path,
		'file' => $file,
		'full_path' => $full_path,
		'real_full_path' => $real_full_path,
		'full_file' => $full_file,
		'real_full_file' => $real_full_file,
	);

}