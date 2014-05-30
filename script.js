$(document).ready(function(){
    
    if($('#list-table').length > 0){
        
        $('#list-table').on('click', '.js-delete-file', function(e){
            e.preventDefault();
            var path = $(this).data('path');
            $('#delete-file').find('[name="path"]').val(path);
            $('#delete-file').find('.modal-body p').text("다음 파일을 삭제합니다 : " + $(this).data('title'));
        });

        var Dynatable = $('#list-table').dynatable({
            features: {
                paginate: false
            }
        }).data('dynatable');
        Dynatable.sorts.add('날짜',-1);
        Dynatable.process();
    }

    if($('.content').length > 0){
        $('title').text($('h1').first().text() + ' : Mytory Docs');
    }
});