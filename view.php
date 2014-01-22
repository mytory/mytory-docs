<nav class="navbar navbar-default" role="navigation">
	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav">
			<li><a href="?path=list:<?php echo $parsed['full_path'] ?>">목록으로 돌아가기</a></li>
			<li><a href="?path=edit:<?php echo $parsed['full_file']?>">수정</a></li>
		</ul>
	</div>
</nav>


<?php echo Markdown(file_get_contents($parsed['real_full_file'])); ?>