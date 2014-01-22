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

function print_docs_list($root, $relative_path){
	global $doc_roots;

	$dir = $doc_roots[$root] . '/' . $relative_path;
	$dir_list = array();
	$file_list = array();

	if (is_dir($dir)){
		if ($dh = opendir($dir)){
			?><ul><?php
			while (($file = readdir($dh)) !== false){
				if($file === '.' || $file === '..' || substr($file, 0, 1) === '.'){
					continue;
				}
				$fullpath = $dir . '/' . $file;
				if(is_file($fullpath) AND is_target_ext($file)){
					$file_list[] = array(
						'r' => $root,
						'p' => $relative_path,
						'f' => $file,
						'title' => get_filename_or_md_headline($dir, $file)
					);
				}
				if(is_dir($fullpath)){
					$dir_list[] = array(
						'r' => $root,
						'p' => $relative_path . '/' . $file,
						'title' => $file
					);
				}
			}
			closedir($dh);

			if($relative_path !== '.'){
				$parent_folder = get_parent_folder($relative_path);
				?>
				<li>
					<a class="directory" 
							href="?r=<?php echo $root ?>&amp;p=<?php echo $parent_folder?>">
							상위 폴더
					</a>
				</li>
				<?php
			}else{
				?>
				<li>
					<a href="/" class="directory">
						docs 목록 
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
							href="?r=<?php echo $info['r'] ?>&amp;p=<?php echo $info['p'] ?>">
						<?php echo $info['title'] ?>
					</a>	
				</li>
				<?php
			}

			foreach ($file_list as $info) {
				?>
				<li>
					<a class="doc-file" 
							href="?r=<?php echo $info['r'] ?>&amp;p=<?php echo $info['p'] ?>&amp;f=<?php echo $info['f'] ?>">
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
		return '.';
	}else{
		array_pop($temp);
		return implode('/', $temp);
	}
}

function is_target_ext($full_path){
	global $ext_list;
	return in_array(pathinfo($full_path, PATHINFO_EXTENSION), $ext_list);
}