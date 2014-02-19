$(function(){
    function esquemao_include(){
        $('inc').each(function(){
            var $this = $(this);
            var file = $(this).attr('file');
            $.get('incs/' + file, function(content){
                $this.replaceWith(content);
                esquemao_include();
            });
        });
    }

    esquemao_include();
});