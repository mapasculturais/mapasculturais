<?php

namespace LGPD;

use LGPD\Module;
use DateTime;
use MapasCulturais\App;
use MapasCulturais\i;

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

        $hashText = md5($text);
        $accepted = false;
        if(!$app->user->is('guest')) {
            $metadata_key = 'lgpd_'.$term_slug;
            $_accept_lgpd = $app->user->$metadata_key;
            if( is_array($_accept_lgpd) ) {                
                foreach($_accept_lgpd as $key => $value) {
                    if($key == $hashText) {
                        $accepted = $value;
                        continue;
                    }
                }
            }
        }

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
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
            
        ];
        $this->verifiedTerms("lgpd_{$term_slug}", $accept_terms);
      
    }
    
    public function POST_acceptprivacypolice ()
    {
        $app= App::i();
        
        $config = $app->config['module.LGPD'];
 
        $text = $config['privacyPolice']['text'];
        $accept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
        ];

        $this->verifiedTerms('lgpd_privacyPolice', $accept_terms);
    }
     
    public function POST_accepttermsofusage ()
    {
        $app= App::i();
        $config = $app->config['module.LGPD'];
        
        $text = $config['termsOfUsage']['text'];
        $accept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
        ];
        
        $this->verifiedTerms('lgpd_termsOfUsage', $accept_terms);
            
        
    }
    
    /** 
     * Funcao para verificar se o termo existe e se nao houver, atualiza a chave.
     */
    private function verifiedTerms($meta, $accept_terms ) 
    {
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
    
}
