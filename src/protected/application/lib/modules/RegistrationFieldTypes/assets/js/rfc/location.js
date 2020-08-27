$(function(){
    function setAddress($container){
        clearTimeout(window._address_timeout);

        window._address_timeout = setTimeout(function(){
            var country = MapasCulturais.pais ? MapasCulturais.pais : 'BR';
            var state = $container.find('.js-rfc-input-En_Estado').val();
            var city = $container.find('.js-rfc-input-En_Municipio').val();
            var street = $container.find('.js-rfc-input-En_Nome_Logradouro').val();
            var neighborhood = $container.find('.js-rfc-input-En_Bairro').val();
            var number = $container.find('.js-rfc-input-En_Num').val();
    
            if (state && city && street) {
                var address = neighborhood ? 
                    street + ' ' + number + ', ' + neighborhood + ', ' + city + ', ' + state + ', ' + country : 
                    street + ' ' + number + ', ' + city + ', ' + state + ', ' + country;
    
                
                $container.find('.js-rfc-input-endereco').val(address).trigger('change');
            }
        },500);
    }

    function changeCEP($cep, $container, timeout) {
        clearTimeout(window._cep_timeout);

        window._cep_timeout = setTimeout(function() {
            if(/^\d{5}-\d{3}$/.exec($cep.val())){
                $container.find('.js-rfc-input-En_Nome_Logradouro').val('').attr('placeholder', 'carregando...');
                $container.find('.js-rfc-input-En_Bairro').val('').attr('placeholder', 'carregando...');
                $container.find('.js-rfc-input-En_Estado').val('').attr('placeholder', 'carregando...');
                $container.find('.js-rfc-input-En_Municipio').val('').attr('placeholder', 'carregando...');

                $.getJSON('/site/address_by_postalcode?postalcode='+$cep.val(), function(r){
                    $container.find('.js-rfc-input-En_Nome_Logradouro').attr('placeholder', '');
                    $container.find('.js-rfc-input-En_Bairro').attr('placeholder', '');
                    $container.find('.js-rfc-input-En_Estado').attr('placeholder', '');
                    $container.find('.js-rfc-input-En_Municipio').attr('placeholder', '');
                    if (r.success) {
                        $container.find('.js-rfc-input-_lat').val(r.lat).trigger('change');
                        $container.find('.js-rfc-input-_lon').val(r.lon).trigger('change');

                        $container.find('.js-rfc-input-En_Nome_Logradouro').val(r.streetName).trigger('change');
                        $container.find('.js-rfc-input-En_Bairro').val(r.neighborhood).trigger('change');
                        $container.find('.js-rfc-input-En_Estado').val(r.state.sigla).trigger('change');
                        
                        $container.find('.js-rfc-input-En_Municipio').val(r.city.nome).trigger('change');

                        setAddress($container);
                    }
                });
            }
        },timeout);

        setAddress($container);
    }

    $('body').on('change', 'input.js-rfc-input-En_CEP', function() {
        var $cep = $(this);
        var $container = $cep.parents('.js-rfc-location');

        changeCEP($cep, $container, 10);
    });

    $('body').on('keypress', 'input.js-rfc-input', function() {
        clearTimeout(window._geocoding_timeout);

        var $container = $(this).parents('.js-rfc-location');
        
        window._geocoding_timeout = setTimeout(function(){
            var country = MapasCulturais.pais ? MapasCulturais.pais : 'BR';
            var state = $container.find('.js-rfc-input-En_Estado').val();
            var city = $container.find('.js-rfc-input-En_Municipio').val();
            var street = $container.find('.js-rfc-input-En_Nome_Logradouro').val();
            var neighborhood = $container.find('.js-rfc-input-En_Bairro').val();
            var number = $container.find('.js-rfc-input-En_Num').val();

            if (state && city && street) {
                var address = neighborhood ? 
                    street + ' ' + number + ', ' + neighborhood + ', ' + city + ', ' + state + ', ' + country : 
                    street + ' ' + number + ', ' + city + ', ' + state + ', ' + country;

                
                MapasCulturais.geocoder.geocode({ fullAddress: address }, function(r) {
                    
                    $container.find('.js-rfc-input-_lat').val(r.lat).trigger('change');
                    $container.find('.js-rfc-input-_lon').val(r.lon).trigger('change');
                    
                    setTimeout(function(){
                        $container.find('.js-rfc-input:first').trigger('change');
                    },10);

                    setAddress($container);

                });
            }
        },1000);
    });
});