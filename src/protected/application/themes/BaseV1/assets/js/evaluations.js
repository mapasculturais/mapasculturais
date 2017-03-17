$(function(){
    var labels = MapasCulturais.gettext.evaluations;

    var $form = $('#registration-evaluation-form');
    var $list = $('#registrations-list-container');
    var $header = $('#main-header');
    $(window).scroll(function(){
        $form.css('margin-top', $header.css('top'));
        $list.css('margin-top', $header.css('top'));
    });

    $form.find('.js-evaluation-submit').on('click', function(){
        var $button = $(this);
        var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {'0': MapasCulturais.registration.id, 'status': 'evaluated'});
        var data = $form.find('form').serialize();

        if(!data){
            MapasCulturais.Messages.success(labels.emptyForm);
        }
        $.post(url, data, function(r){
            MapasCulturais.Messages.success(labels.saveMessage);
            if($button.hasClass('js-next')){
                var $current = $("#registrations-list .registration-item.current");
                var $next = $current.nextAll('.visible:first');
                var $link = $next.find('a');
                document.location = $link.attr('href');
            }
        });
    });
});
