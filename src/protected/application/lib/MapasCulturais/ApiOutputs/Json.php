<?php
namespace MapasCulturais\ApiOutputs;
use MapasCulturais\App;

class Json extends \MapasCulturais\ApiOutput{
    protected function getContentType() {
        return 'application/json';
    }

    protected function _outputError($data) {
        echo json_encode(['error' => true, 'data' => $data]);
    }

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $app = App::i();
        $app->response()->header('Access-Control-Allow-Origin', \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }

    protected function _outputItem($data, $object_name = 'Entity') {
        $app = App::i();
        $app->response()->header('Access-Control-Allow-Origin', \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }
}