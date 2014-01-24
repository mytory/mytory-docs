<?php
$dir = $parsed['root_path'] . '/' . $parsed['relative_path'];
$dir_list = array();
$file_list = array();

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        ?>
        <ul><?php
        while (($file = readdir($dh)) !== false) {
            if ($file === '.' || $file === '..' || substr($file, 0, 1) === '.') {
                continue;
            }
            $full_path = $dir . '/' . $file;
            if (is_file($full_path) AND is_target_ext($file)) {
                $file_list[] = array(
                    'path' => 'view:' . $parsed['full_path'] . '/' . $file,
                    'title' => get_filename_or_md_headline($dir, $file)
                );
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

            ?>
            <li class="dir-li">
                <a class="directory" href="?path=<?php echo $parent_path ?>">
                    <span class="glyphicon glyphicon-folder-open"></span>
                    &nbsp;
                    상위 폴더
                </a>
            </li>
        <?php
        } else {
            ?>
            <li class="dir-li">
                <a href="<?php echo BASE_URL ?>" class="directory">
                    <span class="glyphicon glyphicon-folder-open"></span>
                    &nbsp;
                    최상위 root 목록
                </a>
            </li>
        <?php
        }

        uasort($dir_list, function ($a, $b) {
            if ($a['title'] == $b['title']) {
                return 0;
            }
            return ($a['title'] < $b['title']) ? -1 : 1;
        });

        uasort($file_list, function ($a, $b) {
            if ($a['title'] == $b['title']) {
                return 0;
            }
            return ($a['title'] < $b['title']) ? -1 : 1;
        });

        foreach ($dir_list as $info) {
            ?>
            <li class="dir-li">
                <a class="directory" href="?path=<?php echo $info['path'] ?>">
                    <span class="glyphicon glyphicon-folder-open"></span>
                    &nbsp;
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
} else {
    echo "$dir is not a real directory : " . __FILE__ . " : " . __LINE__;
    exit;
}
?>