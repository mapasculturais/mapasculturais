$(function() {
    // avança para o próximo campo com a tecla Enter ou com a setinha do teclado do celular
    $('li.registration-edit-mode input').on('keypress', function(e){
        if (e.charCode == 13) {
            var $inputs = $('li.registration-edit-mode input');
            var $nextInput = $inputs.get($inputs.index(this) + 1);
            if ($nextInput) {
                $nextInput.focus();
             }
        }
    });
});