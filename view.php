<nav class="navbar navbar-default" role="navigation">
	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav">
			<li><a href="?path=list:<?php echo $parsed['full_path'] ?>">목록으로 돌아가기</a></li>
			<li><a href="?path=edit:<?php echo $parsed['full_file']?>">수정</a></li>
		</ul>
	</div>
</nav>
<p>파일경로 : <input type="text" value="<?php echo $parsed['real_full_file'] ?>"></p>
<?php 
$html = Markdown(file_get_contents($parsed['real_full_file']));
$html = str_replace('<img src="http://', '<imgsrchttp', $html);
$html = str_replace('<img src="https://', '<imgsrchttps', $html);
$html_for_image = str_replace('<img src="', '<img src="image.php?path=image:' . $parsed['full_path'] . '/' , $html);
$html_for_image = str_replace('<imgsrchttps', '<img src="https://', $html_for_image);
$html_for_image = str_replace('<imgsrchttp', '<img src="http://', $html_for_image);
echo $html_for_image;
?>