$(document).ready(function() {
    $(".edit-entity").click(function(){
        let url = MapasCulturais.createUrl('eventos', 'edita', [MapasCulturais.eventId]);
        document.location = url;
    });

    $(".view-entity").click(function(){
        let url = MapasCulturais.createUrl('evento', [MapasCulturais.eventId]);
        document.location = url;
    });
    selectRadio('js-ownerProject')
   
});


function selectRadio(owener){
      $('.owner-select').find('.active').removeClass('active')
      $('.'+owener).addClass('active');
}

function saveEvent(formId, complete = false){
    let url = MapasCulturais.createUrl('eventos', '');
    var dataForm = $('#'+formId).serializeArray();
    MapasCulturais.frequencies = {once: "uma vez", daily: "todos os dias", weekly: "semanal", monthly: "mensal"}
    $.ajax({
        url: url,
        type: 'post',
        data: dataForm,
        beforeSend: function() {
            if(!complete){
                $(".spinner").show();
            }
        }
    })
    .done(function(data) {
        $(".spinner").hide();
        $(".message").html("");
        if(!(data.hasOwnProperty('error'))){
            $(".create-event").toggle('hidden');
            $(".modal-feedback-event").toggle('hidden');
            $(".cancel-action").toggle('hidden');
            $(".btn-event").toggle('hidden');
            $("#IdEvent").val(data.id);
            MapasCulturais.eventId = data.id;
            $(document).trigger('createEvent', data);
        }else{
            $(".message").html("<p class='alert danger'>Não foi possível inserir o evento.</p>");
        }
    })
    .fail(function(jqXHR, textStatus, msg) {
        alert('Erro inesperado, fale com administrador.');
    });
}