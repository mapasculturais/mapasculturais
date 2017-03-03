$(function(){
    var $form = $('#registration-evaluation-form');
    var $list = $('#registrations-list-container');
    var $header = $('#main-header');
    if($form.length){
        $(window).scroll(function(){
            $form.css('margin-top', $header.css('top'));
            $list.css('margin-top', $header.css('top'));
        });
    }
    
    $form.find('.js-evaluation-submit').on('click', function(){
        var $button = $(this);
        var url = MapasCulturais.createUrl('registration', 'saveEvaluation', [MapasCulturais.registration.id]);
        
        $.post(url, $form.find('form').serialize(), function(r){
            if($button.hasClass('js-next')){
                var $current = $("#registrations-list .registration-item.current");
                var $next = $current.nextAll('.visible:first');
                var $link = $next.find('a')
                document.location = $link.attr('href');
            }
        });
    });
});