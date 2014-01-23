<?php

function check_config_error(){
	global $doc_roots;
	foreach($doc_roots as $root){
		if( ! is_dir($root)){
			echo "$root is not a real directory.";
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

function print_docs_list($parsed){
	$dir = $parsed['root_path'] . '/' . $parsed['relative_path'];
	$dir_list = array();
	$file_list = array();

	if (is_dir($dir)){
		if ($dh = opendir($dir)){
			?><ul><?php
			while (($file = readdir($dh)) !== false){
				if($file === '.' || $file === '..' || substr($file, 0, 1) === '.'){
					continue;
				}
				$full_path = $dir . '/' . $file;
				if(is_file($full_path) AND is_target_ext($file)){
					$file_list[] = array(
						'path' => 'view:' . $parsed['full_path'] . '/' . $file,
						'title' => get_filename_or_md_headline($dir, $file)
					);
				}
				if(is_dir($full_path)){
					$dir_list[] = array(
						'path' => 'list:' . $parsed['full_path'] . '/' . $file,
						'title' => $file
					);
				}
			}
			closedir($dh);

			if($parsed['relative_path'] !== ''){
				$parent_folder = get_parent_folder($parsed['relative_path']);
				if($parent_folder){
					$parent_path = 'list:' . $parsed['root'] . '/' . $parent_folder;
				}else{
					$parent_path = 'list:' . $parsed['root'];
				}
				
				?>
				<li>
					<a class="directory" 
							href="?path=<?php echo $parent_path ?>">
							상위 폴더
					</a>
				</li>
				<?php
			}else{
				?>
				<li>
					<a href="/" class="directory">
						최상위 root 목록 
					</a>
				</li>
				<?php
			}

			uasort($dir_list, function($a, $b){
				if ($a['title'] == $b['title']) {
			        return 0;
			    }
			    return ($a['title'] < $b['title']) ? -1 : 1;
			});

			uasort($file_list, function($a, $b){
				if ($a['title'] == $b['title']) {
			        return 0;
			    }
			    return ($a['title'] < $b['title']) ? -1 : 1;
			});

			foreach ($dir_list as $info) {
				?>
				<li>
					<a class="directory" 
							href="?path=<?php echo $info['path'] ?>">
						<?php echo $info['title'] ?>
					</a>	
				</li>
				<?php
			}

			foreach ($file_list as $info) {
				?>
				<li>
					<a class="doc-file" 
							href="?path=<?php echo $info['path'] ?>">
						<?php echo $info['title'] ?>
					</a>	
				</li>
				<?php
			}		
			?></ul><?php
		}
	}else{
		echo "$dir is not a real directory.";
		exit;
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
		echo '잘못된 경로 type1.';
		exit;
	}

	$root_path = realpath($doc_roots[$root]);
	$real_full_path = $root_path . DIRECTORY_SEPARATOR . implode(PATH_SEPARATOR, $temp2);

	if( ! is_file($real_full_path) AND ! is_dir($real_full_path)){
		echo '잘못된 경로 type2. ' . $real_full_path;
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