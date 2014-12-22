<div class="l-view">
<?php 
$content = get_md_content($parsed['real_full_file']);
$html = Markdown($content);
$html = str_replace('<img src="http://', '<imgsrchttp', $html);
$html = str_replace('<img src="https://', '<imgsrchttps', $html);
$html = preg_replace('/<ul>\n<li>([Dd|Aa|Tt][a|u|y][t|t|p][e|h|e][o]{0,1}[r]{0,1} {0,1}:)/', "<ul class='doc-info'>\n<li>$1", $html);

$html_for_image = str_replace('<img src="', '<img src="image.php?path=image:' . $parsed['full_path'] . '/' , $html);
$html_for_image = str_replace('<imgsrchttps', '<img src="https://', $html_for_image);
$html_for_image = str_replace('<imgsrchttp', '<img src="http://', $html_for_image);
echo $html_for_image;
?>
</div>
