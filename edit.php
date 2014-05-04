<?php 
$content = file_get_contents($parsed['real_full_file']);
?>
<style>
    body {
        background-color: rgb(41, 41, 41);
    }
    .navbar {
        background-color: rgb(41, 41, 41);
        border-color: rgb(50, 50, 50);
    }
    .navbar-default .navbar-nav>li>a:hover {
        color: rgb(200, 200, 200);
    }
    .navbar .form-control[readonly] {
        background-color: rgb(41, 41, 41);
        color: rgb(100, 100, 100);
        border-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar              {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-button       {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar-track        {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-track-piece  {
        background-color: rgb(41, 41, 41);
    }
    ::-webkit-scrollbar-thumb        {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-scrollbar-corner       {
        background-color: rgb(50, 50, 50);
    }
    ::-webkit-resizer                {
        background-color: rgb(50, 50, 50);
    }
</style>
<textarea id="editor" class="editor"><?php echo $content ?></textarea>
<div class="msg"></div>
<div class="msg2"></div>
<script>
var auto_save_interval, 
    auto_backup_interval, 
    full_file = '<?php echo $parsed['full_file'] ?>',
    current_filemtime = '<?php echo filemtime($parsed['real_full_file']) ?>';

function auto_save(){
    var content = $('#editor').val();
    console.log('current', current_filemtime);
	$.post('save.php', {
        current_filemtime: current_filemtime,
		content: content,
		path: '<?php echo $_GET['path'] ?>'
	}, function(data){
		if(data.code == 'fail'){
			$('.msg').text(data.msg);
        }else if(data.code == 'file changed'){
            $('.msg').text(data.msg);
            console.log('current', data.current_filemtime, 'real', data.real_filemtime);
            // $('body').html('<h1 style="color: white; padding-top: 100px; text-align: center">파일이 밖에서 변경됐습니다.</h1>');
            // location.reload();
		}else{
            current_filemtime = data.real_filemtime;
			$('.msg').text(new Date().toString() + ' - 저장');
		}
	}, 'json');
}

function auto_backup(){
    $.post('backup.php', {
        content: $('#editor').val(),
        path: '<?php echo $_GET['path'] ?>'
    }, function(data){
        if(data.code == 'fail'){
            $('.msg2').text("백업 실패 : " + data.msg);
        }else{
            $('.msg2').text(new Date().toString() + ' - 백업');
        }
    }, 'json');
}

function init_auto_save_backup(){
    auto_save_interval = setInterval(auto_save, 1000);
    auto_backup();
    auto_backup_interval = setInterval(auto_backup, 60*5*1000);
}

function remove_auto_save_backup(){
    clearInterval(auto_save_interval);
    clearInterval(auto_backup_interval);
}

init_auto_save_backup();

// $(window).blur(remove_auto_save_backup);
// $(window).focus(init_auto_save_backup);

$('#file_path').click(function(){
    $(this).select();
});

</script>