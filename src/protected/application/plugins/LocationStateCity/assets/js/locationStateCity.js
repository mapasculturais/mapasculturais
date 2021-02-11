$(document).ready(function () {
    // PARA PREENCHIMENTO DO MUNICIPIO QUANDO A PÁGINA É CARREGADA
    function getCity() {
        var dataLocation = {
            'key' : 'En_Municipio',
            'idAgente'  : MapasCulturais.entity.id
        };
        if(MapasCulturais.request.controller == 'agent') {
            dataLocation.params = 'agent';
        }else if(MapasCulturais.request.controller == 'space'){
            //dataLocation.push('params' , 'agent');
            dataLocation.params = 'space';
        }
       
        console.log(dataLocation);
            // BUSCANDO NO BANCO QUAL A CIDADE CADASTRADA, CASO HAJA
            $.getJSON(MapasCulturais.baseURL+ 'location/city/', dataLocation,
                function (data, textStatus, jqXHR) {
                    console.log(data)
                    if(data.status == 200){
                        
                        if(document.URL.match(/edita/)) {
                            $('#En_Municipio').editable({
                                mode        : 'inline',
                                source      : {'value': data.message}
                            });
                            console.log(data.message);
                            $('#En_Municipio').html(data.message);
                        }else if(document.URL.match(/create/)) {
                            $('#En_Municipio').editable({
                                mode        : 'inline',
                                source      : {'value': data.message}
                            });
                            console.log(data.message);
                            $('#En_Municipio').html(data.message);
                        }
                        else{
                            //$('#En_Municipio').editable();
                            $('#En_Municipio').html(data.message);
                            $("#En_Municipio").removeClass('editable editable-click editable-empty');
                        }
                    }
                    else{
                        $('#En_Municipio').editable({
                            mode        : 'inline',
                            source      : {'value': data.message}
                        });
                        console.log(data.message);
                        $('#En_Municipio').html(data.message);
                    } 
                    
                }
            );
    }
    getCity();
});