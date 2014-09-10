<?php
namespace MapasCulturais\ApiOutputs;

class Json extends \MapasCulturais\ApiOutput{
    protected function getContentType() {
        return 'application/json';
    }

    protected function _outputError($data) {
        echo json_encode(array('error' => true, 'data' => $data));
    }

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        header('Access-Control-Allow-Origin: '. \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }

    protected function _outputItem($data, $object_name = 'Entity') {
        header('Access-Control-Allow-Origin: '. \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }
}