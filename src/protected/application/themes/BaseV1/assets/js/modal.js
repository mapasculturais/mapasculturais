$(document).ready(function() {
    $(".edit-entity").click(function(){
        let url = MapasCulturais.createUrl('eventos', 'edita', [MapasCulturais.eventId]);
        document.location = url;        
    });
    
    $(".view-entity").click(function(){
        let url = MapasCulturais.createUrl('evento', [MapasCulturais.eventId]);
        document.location = url;        
    });

    let element = document.getElementById("dialog-event-occurrence");
    element.children[0].addEventListener("click", function(event) {
        $("#dialog-event-occurrence").removeClass('occurrence-open');
    })
});


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
        }else{
            $(".message").html("<b>Não foi possível inserir o evento.</b>");
        }
    })
    .fail(function(jqXHR, textStatus, msg) {
        alert('Erro inesperado, fale com administrador.');
    });
}

