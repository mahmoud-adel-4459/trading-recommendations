jQuery(document).ready(function($){
    var modal = $('#tr-close-modal');
    $('#tr-mark-closed').on('click', function(e){
        e.preventDefault();
        modal.show();
    });
    $('#tr_close_cancel').on('click', function(){
        modal.hide();
    });

    $('#tr_save_close').on('click', function(){
        var post_id = $('#tr-mark-closed').data('postid');
        var close_time = $('#tr_close_time').val();
        var result = $('#tr_result').val();
        var close_price = $('#tr_close_price').val();
        $.post(tr_recommend.ajax_url, {
            action: 'tr_mark_closed',
            nonce: tr_recommend.nonce,
            post_id: post_id,
            close_time: close_time,
            result: result,
            close_price: close_price
        }, function(res){
            if(res.success){
                // update UI without reload: change status select and hide modal
                $('select[name="tr_status"]').val('Closed');
                modal.hide();
                // show admin notice
                if($('#message.updated').length===0){
                    $('<div id="message" class="updated"><p>Trade marked as Closed.</p></div>').insertBefore('#post');
                }
            } else {
                alert('Error: ' + (res.data||'Unknown'));
            }
        });
    });
    // sample data import
    $('#tr-import-sample').on('click', function(e){
        e.preventDefault();
        var btn = $(this);
        var status = $('#tr-import-status');
        btn.prop('disabled', true);
        status.text('Importing...');
        $.post(tr_recommend.ajax_url, {
            action: 'tr_import_sample',
            nonce: btn.data('nonce')
        }, function(res){
            if ( res && res.success ) {
                status.text('Done. Inserted ' + (res.data.count || 0) + ' items.');
            } else {
                status.text('Error: ' + (res.data || 'unknown'));
            }
            btn.prop('disabled', false);
        }).fail(function(){
            status.text('Request failed');
            btn.prop('disabled', false);
        });
    });
    // list table sample import button
    $(document).on('click', '#tr-list-import-sample', function(e){
        e.preventDefault();
        var btn = $(this);
        var status = $('#tr-list-import-status');
        btn.prop('disabled', true);
        status.text('Importing...');
        $.post(tr_recommend.ajax_url, {
            action: 'tr_import_sample',
            nonce: tr_recommend.nonce
        }, function(res){
            if ( res && res.success ) {
                status.text('Done. Inserted ' + (res.data.count || 0) + ' items.');
                // optional: refresh the page to show new posts
                setTimeout(function(){ location.reload(); }, 900);
            } else {
                status.text('Error: ' + (res.data || 'unknown'));
            }
            btn.prop('disabled', false);
        }).fail(function(){
            status.text('Request failed');
            btn.prop('disabled', false);
        });
    });
    // list table delete samples button
    $(document).on('click', '#tr-list-delete-samples', function(e){
        e.preventDefault();
        if ( ! confirm('Are you sure you want to delete sample data? / هل أنت متأكد من حذف البيانات التجريبية؟') ) return;
        var btn = $(this);
        var status = $('#tr-list-import-status');
        btn.prop('disabled', true);
        status.text('Deleting...');
        $.post(tr_recommend.ajax_url, {
            action: 'tr_delete_samples',
            nonce: tr_recommend.nonce
        }, function(res){
            if ( res && res.success ) {
                status.text('Deleted ' + (res.data.deleted || 0) + ' items.');
                setTimeout(function(){ location.reload(); }, 900);
            } else {
                status.text('Error: ' + (res.data || 'unknown'));
            }
            btn.prop('disabled', false);
        }).fail(function(){
            status.text('Request failed');
            btn.prop('disabled', false);
        });
    });
});
