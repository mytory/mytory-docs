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
                <th>이름</th>
                <th>날짜</th>
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
            if (is_file($full_path) AND is_target_ext($file)) {
                $file_list[] = array(
                    'path' => 'view:' . $parsed['full_path'] . '/' . $file,
                    'title' => get_filename_or_md_headline($dir, $file),
                    'date' => get_date($full_path)
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

            print_one_dir('상위폴더', "?path={$parent_path}");
        } else {

            print_one_dir('최상위', BASE_URL);
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
<script>
    $('.js-delete-file').click(function(){
        $('#delete-file').find('[name="path"]').val($(this).data('path'));
        $('#delete-file').find('.modal-body p').text("다음 파일을 삭제합니다 : " + $(this).data('title'));
    });
    $('#list-table').dynatable({
        features: {
            paginate: false
        }
    });
</script>