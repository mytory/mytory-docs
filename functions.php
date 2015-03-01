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

    if( ! is_text_file($file)){
        return $file;
    }

	$content = get_md_content($full_path);

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
		echo "잘못된 경로 type1 : " . __FILE__ . " : " . __LINE__ . " : path는 {$_REQUEST['path']}, root는 {$root}";
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

function new_file(){
    global $parsed, $ext_list;

    $pathinfo = pathinfo($_REQUEST['filename']);
    if( ! in_array($pathinfo['extension'], $ext_list)){
        $_REQUEST['filename'] .=  '.' . $ext_list[0];
    }

    $new_file = $parsed['real_full_path'] . DIRECTORY_SEPARATOR . $_REQUEST['filename'];
    if(is_file($new_file)){
        echo "파일명 중복";
        exit;
    }else{
        $handle = fopen($new_file, 'w');
        fwrite($handle, '# ' . $pathinfo['filename']);
        fclose($handle);
        header('location: ' . BASE_URL . '/?path=edit:' . $parsed['full_path'] . '/' . $_REQUEST['filename']);
    }
}

function delete_file(){
    global $parsed;
    if(is_file($parsed['real_full_file'])){
        unlink($parsed['real_full_file']);
    }
    header('location: ' . BASE_URL . '/?path=list:' . $parsed['full_path']);
}

function get_date($full_path){

    if( ! is_text_file($full_path)){
        return date('Y-m-d', filectime($full_path));
    }

    $content = file_get_contents($full_path);

    preg_match('/[D|d]ate {0,1}: {0,1}([0-9]{4}-[0-9]{2}-[0-9]{2})(.*)\n/', $content, $match_date);
    preg_match('/날짜 {0,1}: {0,1}([0-9]{4}-[0-9]{2}-[0-9]{2})(.*)\n/', $content, $match_날짜);
    preg_match('/일시 {0,1}: {0,1}([0-9]{4}-[0-9]{2}-[0-9]{2})(.*)\n/', $content, $match_일시);
    preg_match('/\n([0-9]{4}-[0-9]{2}-[0-9]{2}) */', $content, $match_only_date);

    if(isset($match_date[1]) && trim($match_date[1])){
        return trim($match_date[1]);
    }

    if(isset($match_날짜[1]) && trim($match_날짜[1])){
        return trim($match_날짜[1]);
    }

    if(isset($match_일시[1]) && trim($match_일시[1])){
        return trim($match_일시[1]);
    }

    if(isset($match_only_date[1]) && trim($match_only_date[1])){
        return trim($match_only_date[1]);
    }

    return date('Y-m-d', filectime($full_path));
}

function print_one_dir($name, $href){
    ?>
    <tr class="dir-row">
        <td>
            <span class="glyphicon glyphicon-folder-open"></span>
        </td>
        <td><a class="directory" href="<?php echo $href ?>">
                <?php echo $name ?>
            </a></td>
        <td></td>
        <td></td>
    </tr>
    <?php
}

function print_one_file($info){
    ?>
    <tr class="doc-row">
        <td></td>
        <td>
            <a class="doc-file"
               href="?path=<?php echo $info['path'] ?>">
                <?php echo $info['title'] ?>
            </a>
        </td>
        <td>
            <?php echo $info['date'] ?>
        </td>
        <td>
            <a href="#" class="glyphicon glyphicon-trash js-delete-file" data-toggle="modal" data-target="#delete-file"
               data-title="<?php echo htmlspecialchars($info['title']) ?>"
               data-path="<?php echo str_replace('view:', 'delete-file:', $info['path']) ?>"></a>
        </td>
    </tr>
    <?php
}

function get_md_content($real_full_file){
    $content = file_get_contents($real_full_file);
    $text_encoding = mb_detect_encoding($content);
    if(strtolower($text_encoding) == 'euc-kr'){
        $content = iconv($text_encoding, 'UTF-8' . '//IGNORE', $content);
    }
    return $content;
}

function is_text_file($file){
    return in_array(pathinfo($file, PATHINFO_EXTENSION), array('txt', 'md'));
}