jQuery(function($){
    // redirect when user selects a category from dropdown
    $(document).on('change', '.tr-category-select', function(){
        var url = $(this).val();
        if ( url ) {
            window.location.href = url;
        }
    });
});
