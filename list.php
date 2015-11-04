<?php
$dir = $parsed['root_path'] . '/' . $parsed['relative_path'];
$dir_list = array();
$file_list = array();

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        ?>
        <table id="list-table" class="table table-striped table-hover">
        <colgroup>
            <col width="50"/>
            <col width="50%"/>
            <col width="30%"/>
        </colgroup>
        <thead>
            <tr>
                <th></th>
                <th>Name</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php
        while (($file = readdir($dh)) !== false) {
            if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
                continue;
            }
            $full_path = $dir . '/' . $file;
            $file_info = array();
            if (is_file($full_path)) {
                $file_info = array(
                    'full_path' => $full_path, 
                    'path' => 'view:' . $parsed['full_path'] . '/' . $file,
                    'title' => get_filename_or_md_headline($dir, $file),
                    'date' => get_date($full_path)
                );

                if(is_target_ext($file)){
                    $file_info['is_markdown'] = true;
                }else{
                    $file_info['is_markdown'] = false;
                }
                $file_list[] = $file_info;
            }

            if (is_dir($full_path)) {
                $dir_list[] = array(
                    'path' => 'list:' . $parsed['full_path'] . '/' . $file,
                    'title' => $file
                );
            }
        }
        closedir($dh);

        if ($parsed['relative_path'] !== '') {
            $parent_folder = get_parent_folder($parsed['relative_path']);
            if ($parent_folder) {
                $parent_path = 'list:' . $parsed['root'] . '/' . $parent_folder;
            } else {
                $parent_path = 'list:' . $parsed['root'];
            }

            print_one_dir('Parent Folder', "?path={$parent_path}");
        } else {

            print_one_dir('Root Folder', BASE_URL);
        }

        uasort($dir_list, function ($a, $b) {
            if ($a['title'] == $b['title']) {
                return 0;
            }
            return ($a['title'] < $b['title']) ? -1 : 1;
        });

        uasort($file_list, function ($a, $b) {
            if ($a['date'] == $b['date']) {
                return 0;
            }
            return ($a['date'] < $b['date']) ? 1 : -1;
        });

        foreach ($dir_list as $info) {
            print_one_dir($info['title'], '?path=' . $info['path']);
        }

        foreach ($file_list as $info) {
            print_one_file($info);
        }
        ?></tbody></table><?php
    }
} else {
    echo "$dir is not a real directory : " . __FILE__ . " : " . __LINE__;
    exit;
}
?>