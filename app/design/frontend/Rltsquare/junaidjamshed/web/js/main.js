require([
    'jquery',
    'jquery/ui'
], function ($) {

    /* ========== sticky toolbar on category page at small screen ========== */
    $(document).scroll(function()
    {
        var y = $(this).scrollTop();
        if (y > 65) {
            $('body').addClass('sticky-toolbar-mobile');
        } else {
            $('body').removeClass('sticky-toolbar-mobile');
        }
    });
});