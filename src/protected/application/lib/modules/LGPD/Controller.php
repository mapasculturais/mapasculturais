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
    public function GET_policePrivacy ()
    {
        $app= App::i();
        $this->render('privacy-police');
    }
    
    public function POST_aceptPolicePrivacy ()
    {
        $app= App::i();
        $config = $app->config['module.LGPD'];
 
        $text = $config['privacyPolice'];
        $acept_terms[md5($text)] = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
        ];

        var_dump($acept_terms);
        exit;
                
    }
     
    public function POST_aceptTermsOfUsage ()
    {
        $app= App::i();
        $config = $app->config['module.LGPD'];
       
        $text = $config['termsOfUsage'];
        $acept_terms[md5($text)] = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
        
        ];
        
        var_dump($acept_terms);
        exit;

        $agent = $app->repo('Agent')->find(['id' => $app->user->profile->id]);
        if( $_acept_lgpd = json_decode($agent->acept_lgpd, true)){
            $flag = false;
            foreach($_acept_lgpd as $term){
                if($term['md5'] == $acept_terms['md5']){
                    $flag = true;
                }
            }
            if(!$flag){    
                $_acept_lgpd[] = $acept_terms;
                $aceptTerms =$_acept_lgpd;
            }
        }
        $agent->acept_lgpd = json_encode([$aceptTerms]);            
        $agent->save();
        echo json_encode($aceptTerms);
    }
    
}