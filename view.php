<?php 
$content = get_md_content($parsed['real_full_file']);
$html = Markdown($content);
$html = str_replace('<img src="http://', '<imgsrchttp', $html);
$html = str_replace('<img src="https://', '<imgsrchttps', $html);
$html_for_image = str_replace('<img src="', '<img src="image.php?path=image:' . $parsed['full_path'] . '/' , $html);
$html_for_image = str_replace('<imgsrchttps', '<img src="https://', $html_for_image);
$html_for_image = str_replace('<imgsrchttp', '<img src="http://', $html_for_image);
echo $html_for_image;
?>