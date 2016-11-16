$(function(){

    var update_img = function(){
        $.get(
            '/selos/sealModelPreview?p='+$('#seal_model').editable('getValue', true),
            function(r){
                if (r){
                    $('.seal-model-preview').removeClass('hidden');
                    $('.seal-model-preview > img').attr('src', r);
                    console.log('acertou');
                }
                else {
                    $('.seal-model-preview').addClass('hidden');
                    $('.seal-model-preview > img').attr('src', '');
                }
            }
        );
    };
    $('#seal_model').on('hidden', function(e, params){
        update_img();
    });
    update_img();
});