$(function(){
    var labels = MapasCulturais.gettext.evaluations;

    var $formContainer = $('#registration-evaluation-form');
    var $form = $formContainer.find('form');
    var $list = $('#registrations-list-container');
    var $header = $('#main-header');
    $(window).scroll(function(){
        var top = parseInt($header.css('top'));
        $formContainer.css('margin-top', top);
        $list.css('margin-top', top);
    });

    $formContainer.find('.js-evaluation-submit').on('click', function(){
        var $button = $(this);
        var url = MapasCulturais.createUrl('registration', 'saveEvaluation', {'0': MapasCulturais.registration.id, 'status': 'evaluated'});
        var data = $form.serialize();

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
        }).fail(function(rs) {
            if(rs.responseJSON && rs.responseJSON.error){
                if(rs.responseJSON.data instanceof Array){
                    rs.responseJSON.data.forEach(function(msg){
                        console.log(msg);
                        MapasCulturais.Messages.error(msg);
                    });
                } else {
                    MapasCulturais.Messages.error(rs.responseJSON.data);
                }
            }
        });
    });
    var __onChangeTimeout;
    $form.on('change', function() {
        clearTimeout(__onChangeTimeout);
        __onChangeTimeout = setTimeout(function(){
            var data = $form.serialize();
            var url = MapasCulturais.createUrl('registration', 'saveEvaluation', [MapasCulturais.registration.id]);
            $.post(url, data, function(r){
                MapasCulturais.Messages.success(labels.saveMessage);
            });
        },1000);

    });
});
