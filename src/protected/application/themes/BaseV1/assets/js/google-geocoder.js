
/* 
 * Mapas Culturais geocoder
 * 
 * Example:
 * 
 * MapasCulturais.geocoder.geocode({
 *      streetName: streetName,
 *      number: number,
 *      neighborhood: neighborhood,
 *      city: city,
 *      state: state,
 *      postalCode: cep
 *  }, geocode_callback);
 * 
 */ 
MapasCulturais.geocoder = {

    country: 'br', // ISO 3166-1alpha2 code or list of codes
    geocoder: null,
    
    initialize: function() {
        // activate google service
        if(typeof google !== 'undefined')
            this.geocoder = new google.maps.Geocoder();
    },
    geocode: function(addressElements, callback) {
        
        this.initialize();
        
        this.googleCallback = callback;
        
        var address = false;
        
        if (addressElements.fullAddress)
            address = addressElements.fullAddress;
            
        if (!address) {
            address = addressElements.streetName + (addressElements.number ? ', ' + addressElements.number : '');
            
            if (addressElements.city)
                address += ', ' + addressElements.city;
                
            if (addressElements.state)
                address += ', ' + addressElements.state;
        }
        
        this.geocoder.geocode({'address': address + ', Brasil'}, this.googleCallback);
        
    },
    
    googleCallback: function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var location = results[0].geometry.location;
            var response = { lat: location.lat(), lon: location.lng() };
        } else {
            var response = false;
        }
        
        this.callback(response);
        
    }

}


