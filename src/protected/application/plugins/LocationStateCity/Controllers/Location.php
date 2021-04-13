<?php
namespace LocationStateCity\Controllers;
use \MapasCulturais\App;

class Location extends \MapasCulturais\Controller{

    function GET_city() {
        $app = App::i();
        $id = 0;
        //  QUANDO VAI CRIAR UM AGENTE NOVO O ID É DO AGENTE LOGADO
        if( empty($this->getData['idAgente']) ){
            $id = $app->user->id;
        }else{
            $id = $this->getData['idAgente'];
        }
        $key = $this->getData['key'];
        //dump($this->getData);
        if($this->getData['params'] == 'agent') {
            $taxoUp = $app->repo('AgentMeta')->findBy([
                'owner' => $id,
                'key' => $key 
            ]);
        }else {
            $taxoUp = $app->repo('SpaceMeta')->findBy([
                'owner' => $id,
                'key' => $key 
            ]);
        }
        
        if(empty($taxoUp)){
            return $this->json(['message' => 'Insira o municipio', 'status' => 500]);
        }else{
            return $this->json(['message' => $taxoUp[0]->value, 'status' => 200]);
        }
    }
    function POST_state() {
        $app = App::i();
        $id = $this->postData['idAgente'];
        $key = $this->postData['key'];
       
        $taxoUp = $app->repo('AgentMeta')->findBy([
            'owner' => $id,
            'key' => $key 
        ]);
        if(empty($taxoUp)){
            return $this->json(['message' => '-- Selecione --', 'status' => 500]);
        }else{
            return $this->json(['message' => $taxoUp[0]->value, 'status' => 200]);
        }
        //dump($taxoUp[0]->value);
        //return $taxoUp[0]->value;
    }

    function POST_alterAgente() {
        echo '$this->postData';
        dump($this->postData);
        $app = App::i();
        $taxoUp = $app->repo('AgentMeta')->findBy([
            'owner' => $this->postData['idAgente'],
            'key' => 'En_Estado' 
        ]);
        dump($taxoUp[0]->value);
    }

    function POST_saveOrUpdate() {
        $app = App::i();

        // try {
        //     $taxoUp = $app->repo('AgentMeta')->findBy([
        //         'owner' => $this->postData['idAgente'],
        //         'key' => $this->postData['key'] 
        //     ]);
        //     if(empty($taxoUp)) {
        //         // BUSCANDO INSTANCIA DO AGENTE
        //         $agent = $app->repo('Agent')->find($this->postData['idAgente']);
        //         $taxo = new \MapasCulturais\Entities\AgentMeta;
        //         $taxo->key = $this->postData['key'];
        //         $taxo->value = $this->postData['value'];
        //         $taxo->owner = $agent;
        //         $app->em->persist($taxo);
        //         $app->em->flush();
        //         return $this->json(true);
        //     }else{
        //         $taxoUp[0]->key = $this->postData['key'];
        //         $taxoUp[0]->value = $this->postData['value'];
        //         $app->em->persist($taxoUp[0]);
        //         $app->em->flush();
        //         return $this->json(true);
        //     }
        // } catch (\Throwable $th) {
        //     echo $th->getMessage();
        // }
    }

    function GET_locationState() {
        dump($this->getData);
    }
}