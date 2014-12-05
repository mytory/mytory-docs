$(document).ready(function(){
    
    if($('#list-table').length > 0){
        
        $('#list-table').on('click', '.js-delete-file', function(e){
            e.preventDefault();
            var path = $(this).data('path');
            $('#delete-file').find('[name="path"]').val(path);
            $('#delete-file').find('.modal-body p').text("Delete target : " + $(this).data('title'));
        });

        var Dynatable = $('#list-table').dynatable({
            features: {
                paginate: false
            }
        }).data('dynatable');
        Dynatable.sorts.add('Date',-1);
        Dynatable.process();
    }

    if($('.l-view').length > 0){
        $('title').text($('h1').first().text() + ' : Mytory Docs');
        $('table').addClass('table');
    }
});