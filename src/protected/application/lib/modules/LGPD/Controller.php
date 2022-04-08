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
    public function GET_acept()
    {
        // eval(\psy\sh());
        $app = App::i();
        $term_slug = $this->data[0] ?? null;
        /** @todo Verificar term_slug*/ 
        $config = $app->config['module.LGPD'][$term_slug] ;
        
        $url = $this->createUrl('acept', [$term_slug]);
        $title = $config['title'];
        $text = $config['text'];

        $hashText = md5($text);
    // verificacao
    /** o timestamp precisa usar o i */
        $accepted = false;
        if(!$app->user->is('guest')){
            $metadata_key = 'lgpd_'.$term_slug;
            $_acept_lgpd = $app->user->$metadata_key;
            if( is_array($_acept_lgpd) ):
                foreach($_acept_lgpd as $key => $value){
                    if($key == $hashText){
                        $accepted = $value;
                        continue;
                    }
                }
            endif;
        }

        $this->render('acept', ['url' => $url, 'title' => $title, 'text' => $text, 'accepted' => $accepted]);
        
    }    
    

  public function POST_acept()
  {
        $app = App::i();
        $term_slug = $this->data[0] ?? null;
    /** @todo Verificar term_slug*/ 
        $config = $app->config['module.LGPD'][$term_slug] ;
        $text = $config['text'];

        $acept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
            
        ];
        $this->verifiedTerms("lgpd_{$term_slug}", $acept_terms);
      
  }
    
    public function POST_aceptprivacypolice ()
    {
        $app= App::i();
        
        $config = $app->config['module.LGPD'];
 
        $text = $config['privacyPolice']['text'];
        $acept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
        ];

        $this->verifiedTerms('lgpd_privacyPolice', $acept_terms);
    }
     
    public function POST_acepttermsofusage ()
    {
        $app= App::i();
        $config = $app->config['module.LGPD'];
        
        $text = $config['termsOfUsage']['text'];
        $acept_terms = [
            'timestamp' => (new DateTime())->getTimestamp(),
            'md5' => md5($text),
            'text' => $text,
            'ip' => $app->request()->getIp(),
            'userAgent' => $app->request()->getUserAgent(),
        ];
        
        $this->verifiedTerms('lgpd_termsOfUsage', $acept_terms);
            
        
    }
    /** 
     * Funcao para verificar se o termo existe e se nao houver, atualiza a chave.
     */
    private function verifiedTerms($meta, $acept_terms ) {
        $app= App::i();
        // var_dump($acept_terms);
        // die;
        $user = $app->user;
        $_acept_lgpd = $user->$meta ?: (object)[];

        $index = $acept_terms['md5'];
        
        if(!isset($_acept_lgpd->$index)){
            $_acept_lgpd->$index = $acept_terms;
            $user->$meta = $_acept_lgpd;
            $user->save();
        }
        
       /** @todo Redirecionar pra url original */
        $url= $app->createUrl('panel');
        $app->redirect($url);
    }
}

/** @todo 
 *  
 * 
 * * Caso contrário mostrar quando aceitou timestamp
 * Issue2: So exibir o botao aceitar se o usuario nao tiver aceito ainda 
 
 * Para usuarios não logados não mostrar o botão $app->user->is('guest');
 * Estilo do menu: Classe na tag a, ver como esta na pagina "como usar"
 *Trocar 'accept' onde esta 'acept';
*/