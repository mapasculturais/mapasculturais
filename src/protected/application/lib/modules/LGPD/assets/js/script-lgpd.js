function Terms(){

    var url = MapasCulturais.createUrl('lgpd','aceptTerms' );
    $.post(url, {suggest: 'TesteString' }, function(result){
      console.log(result);
      });

    }