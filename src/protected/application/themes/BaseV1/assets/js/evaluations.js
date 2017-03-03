$(function(){
    var $form = $('#registration-evaluation-form');
    var $header = $('#main-header');
    if($form.length){
        $(window).scroll(function(){
            $form.css('margin-top', $header.css('top'));
        });
    }
});