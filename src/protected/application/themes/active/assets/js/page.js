$(function(){
    function setActiveMenu(){
        var pageUrl = document.location.origin + document.location.pathname,
            pageUrlWithHash = pageUrl + document.location.hash,
            $el = null;

        $('#nav-da-pagina a').each(function(){
            var href = $(this).attr('href');
            if(pageUrl.substr(-href.length) === href || pageUrlWithHash.substr(-href.length) === href){
                $el = $(this);
            }
        });
        
        if($el){
            $('#nav-da-pagina a').removeClass('active');
            $el.addClass('active');
        }
    }
    
    setActiveMenu();
    
    $(window).on('hashchange', setActiveMenu);
    
    document.title = $('.main-content h1:first').text();
});