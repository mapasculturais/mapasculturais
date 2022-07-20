
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

    country: "br", // ISO 3166-1alpha2 code or list of codes
    geocoder: null,

    initialize: function() {
        // activate google service
        if (typeof google !== "undefined")
            this.geocoder = new google.maps.Geocoder();
        return;
    },
    geocode: function(addressElements, callback) {
        this.initialize();
        var address = false;
        var componentRestrictions = {};
        var hasRestrictions = false;
        if (addressElements.fullAddress) {
            address = addressElements.fullAddress;
        }
        if (addressElements.city) {
            componentRestrictions.locality = addressElements.city;
            hasRestrictions = true;
        }
        if (addressElements.state) {
            componentRestrictions.administrativeArea = addressElements.state;
            hasRestrictions = true;
        }
        if (addressElements.country) {
            componentRestrictions.country = addressElements.country;
            hasRestrictions = true;
        }
        if (!address) {
            address = addressElements.streetName + (addressElements.number ? (", " + addressElements.number) : "");
            if (addressElements.city)
                address += ", " + addressElements.city;
            if (addressElements.state)
                address += ", " + addressElements.state;
        }
        var parms = {
            "address": (address + (addressElements.country ? "" : MapasCulturais.geocoder.country)),
        };
        if (hasRestrictions) {
            parms["componentRestrictions"] = componentRestrictions;
        }
        console.log(parms);
        this.geocoder.geocode(parms, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var location = results[0].geometry.location;
                var response = {lat: location.lat(), lon: location.lng()};
            } else {
                var response = false;
            }
            console.log({"status": status, "results": results, "response": response});
            callback(response);
            return;
        });
        return;
    }
}


