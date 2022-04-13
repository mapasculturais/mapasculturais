<?php

namespace LGPD;

use DateTime;
use MapasCulturais\App;
class Controller  extends \MapasCulturais\Controller{

    function __construct()
    {
        parent::construct();
        $this->layout = 'lgpd'; 
    }

    public function GET_accept()
    {
        
        $app = App::i();
        $term_slug = $this->data[0] ?? null;
        /** @todo Verificar term_slug */ 
        $config = $app->config['module.LGPD'][$term_slug] ;
        
        $url = $this->createUrl('accept', [$term_slug]);
        $title = $config['title'];
        $text = $config['text'];
        $hashText =  Module::createHash($text);
        $accepted = false;
        if(!$app->user->is('guest')) {
            $metadata_key = 'lgpd_'.$term_slug;
            $_accept_lgpd = $app->user->$metadata_key;
            if( is_object($_accept_lgpd) ) { 
                foreach($_accept_lgpd as $key => $value) {
                    if($key == $hashText) {
                        $accepted = $value;
                        continue;
                    }
                }
            }
        }
        $app->view->enqueueStyle('app','lgpd-file','css/lgpd.css');
        $this->render('accept', ['url' => $url, 'title' => $title, 'text' => $text, 'accepted' => $accepted]);
    }    
    
    public function POST_accept()
    {
        $app = App::i();
        $term_slug = $this->data[0] ?? null;
        
        /** @todo Verificar term_slug*/ 
        $config = $app->config['module.LGPD'][$term_slug] ;
        $text = $config['text'];

        $accept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => Module::createHash($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
            
        ]; 
        $this->verifiedTerms("lgpd_{$term_slug}", $accept_terms);
    }
    /** 
     * Funcao para verificar se o termo existe e se nao houver, atualiza a chave.
     */
    private function verifiedTerms($meta, $accept_terms ) 
    {
        /** @var App $app */
        $app= App::i();
      
        $user = $app->user;
        $_accept_lgpd = $user->$meta ?: (object)[];
        $index = $accept_terms['md5'];
        
        if(!isset($_accept_lgpd->$index)){
            $_accept_lgpd->$index = $accept_terms;
            $user->$meta = $_accept_lgpd;
            $user->save();

        }
       /** @todo Redirecionar pra url original */
        $url= $app->createUrl('panel'); 
        $app->redirect($url);
    }
    /**
     * @var string $text 
     * @return string
     *
     */
  
}
