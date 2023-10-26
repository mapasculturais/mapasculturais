/*
 * In this file you will find methods that you might want to change or override in your theme.
 *
 * In order to that, just re-declare them in a new JS file in your plugin or theme,
 * making sure its loaded after this file and that the methods returns information in the
 * sabe format the original methods did
 *
 */




/* Build Address
 *
 * This functions create a one string version of the address,
 * based on each element.
 *
 * It receives each separated element of the address and return it as one string, in the format you want.
 *
 *
 */
MapasCulturais.buildAddress = function(streetName, streetNumber, complement, neighborhood, city, state, postalCode) {
    return streetName + ", " + streetNumber + (complement ? ", " + complement : " ") + ", " + neighborhood + ", " + postalCode  + ", " + city + ", " + state;
}


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
 * or it can receive only the full addres already formatted
 *
 * MapasCulturais.geocoder.geocode({
 *      fullAddress: 'Rua da Gloria, 123, Liberdade',
 *  }, geocode_callback);
 *
 */
MapasCulturais.geocoder = {
    country: "br", // ISO 3166-1alpha2 code or list of codes
    initialize: function() {
        //
    },
    geocode: function(addressElements, callback) {
        this.initialize();
        var params = {
            format: "json",
            countrycodes: this.country
        };
        console.log(addressElements);
        var structured = false;
        if (addressElements.streetName) {
            params.street = (addressElements.number ? addressElements.number + " " : "") + addressElements.streetName;
            structured = true;
        }
        if (addressElements.city) {
            params.city = addressElements.city;
            structured = true;
        }
        if (addressElements.state) {
            params.state = addressElements.state;
            structured = true;
        }
        if (addressElements.country) {
            params.country = addressElements.country;
            structured = true;
        }
        // Parece que o nominatim não se dá bem com nosso CEP
        // if (addressElements.postalCode) {
        //     params.postalcode = addressElements.postalCode;
        //     structured = true;
        // }
        if (!structured && addressElements.fullAddress) {
            params.q = addressElements.fullAddress;
        }
        console.log(params);
        jQuery.get("https://nominatim.openstreetmap.org/search", params, function(r) {
            console.log(r);
            var response = false;
            // Consideramos o primeiro resultado
            if (r[0] && r[0].lat && r[0].lon) {
                response = {lat: r[0].lat, lon: r[0].lon};
            }
            callback(response);
            return;
        });
        return;
    }
}
