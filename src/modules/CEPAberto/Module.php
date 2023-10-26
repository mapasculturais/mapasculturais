<?php
namespace CEPAberto;
use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    function register() {}

    function _init() {
        $app = App::i();
        $module = $this;
        $app->hook("GET(site.address_by_postalcode)", function() use($app, $module) {
            $response = $module->getAddressByPostalCode($app->request->get('postalcode'));
            if ($response['success'] === true) {
                echo json_encode($response);
            } else {
                $app->halt(400, $response['error_msg']);
            }

        });
    }

    /*
     * This methods tries to fill the address fields using the postal code
     *
     * By default it relies on brazilian CEP, but you can override this methods
     * to use another API.
     *
     * It should return an Array with an item success set to true or false.
     *
     * If true, it has to return the following fields.
     * Note: lat & lon are optional, they are not being used yet but will probably be soon
     *
     * response example:
     *
     * [
     *    'success' => true,
     *      'lat' => $json->latitude,
     *      'lon' => $json->longitude,
     *      'streetName' => $json->logradouro,
     *      'neighborhood' => $json->bairro,
     *      'city' => $json->cidade,
     *      'state' => $json->estado
     * ]
     *
     */
    function getAddressByPostalCode($postalCode) {
        $app = App::i();
        if ($app->config['cep.token']) {
            $cep = str_replace('-', '', $postalCode);
            // $url = 'http://www.cepaberto.com/api/v2/ceps.json?cep=' . $cep;
            $url = sprintf($app->config['cep.endpoint'], $cep);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($app->config['cep.token_header']) {
                // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token token="' . $app->config['cep.token'] . '"'));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(sprintf($app->config['cep.token_header'], $app->config['cep.token'])));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $json = json_decode($output);

            if (isset($json->cep)) {
                $response = [
                    'success' => true,
                    'lat' => @$json->latitude,
                    'lon' => @$json->longitude,
                    'streetName' => @$json->logradouro,
                    'neighborhood' => @$json->bairro,
                    'city' => @$json->cidade,
                    'state' => @$json->estado
                ];
            } else {
                $response = [
                    'success' => false,
                    'error_msg' => 'Falha a buscar endereço'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error_msg' => 'No token for CEP'
            ];
        }

        return $response;
    }
}