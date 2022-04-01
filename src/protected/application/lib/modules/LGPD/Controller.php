<?php

namespace LGPD;

use LGPD\Module;
use DateTime;
use MapasCulturais\App;

class Controller  extends \MapasCulturais\Controller{


    function __construct()
    {
        parent::construct();
     
    }

    public function GET_termsOfUsage(){ 
        $app= App::i();
        $this->render('terms-of-usage');
    }
    public function POST_acept ()
    {
        $app= App::i();
   
    
        $aceptTerms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            // 'md5' => md5($text),
            // 'texto' => $text,
        
        ];
        $agent = $app->repo('Agent')->find(['id' => $app->user->profile->id]);
        
        if( $_acept_lgpd = json_decode($agent->acept_lgpd, true)){
            $flag = false;
            foreach($_acept_lgpd as $term){
                if($term['md5'] == $aceptTerms['md5']){
                    $flag = true;
                }
            }
            if(!$flag){    
                $_acept_lgpd[] = $aceptTerms;
                $aceptTerms =$_acept_lgpd;
            }
        }
        $agent->acept_lgpd = json_encode([$aceptTerms]);            
        $agent->save();
        echo json_encode($aceptTerms);
    }
    
}