<?php

namespace LGPD;

use DateTime;
use MapasCulturais\App;

class Controller  extends \MapasCulturais\Controller
{

    function __construct()
    {
    }

    public function GET_accept()
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $term_slug = $this->data[0] ?? null;
            /** @todo Verificar term_slug */
            $config = $app->config['module.LGPD'][$term_slug];
    
            $url = $this->createUrl('accept', [$term_slug]);
            $title = $config['title'];
            $text = $config['text'];
            $hashText =  Module::createHash($text);
            $accepted = false;
            if (!$app->user->is('guest')) {
                $metadata_key = 'lgpd_' . $term_slug;
                $_accept_lgpd = $app->user->$metadata_key;
                if (is_object($_accept_lgpd)) {
                    foreach ($_accept_lgpd as $key => $value) {
                        if ($key == $hashText) {
                            $accepted = $value;
                            continue;
                        }
                    }
                }
            }
    
            if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
                $this->layout = 'lgpd';
                $app->view->enqueueStyle('app', 'lgpd-file', 'css/lgpd.css');
                $this->render('accept', ['url' => $url, 'title' => $title, 'text' => $text, 'accepted' => $accepted]);
            } else {
                $this->layout = 'lgpdV2';
                $this->render('terms', ['terms' => $app->config['module.LGPD']]);
            }
        } else {
            $this->layout = 'lgpdV2';
            $this->render('terms', ['terms' => $app->config['module.LGPD']]);
        }
        exit;
       
    }

    public function POST_accept()
    {
        $app = App::i();
        $term_slug = $this->data[0] ?? null;

        /** @todo Verificar term_slug*/
        $config = $app->config['module.LGPD'][$term_slug];
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
     * @param string $meta
     * @param array $accept_terms
     * @return void
     */
    private function verifiedTerms($meta, $accept_terms)
    {
        /** @var App $app */
        $app = App::i();

        $user = $app->user;
        $_accept_lgpd = $user->$meta ?: (object)[];
        $index = $accept_terms['md5'];
        if (!isset($_accept_lgpd->$index)) {
            $_accept_lgpd->$index = $accept_terms;
            $user->$meta = $_accept_lgpd;
            $user->save();
        }

        $url = $_SESSION[Module::key] ?? "/";
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            /** @todo Redirecionar pra url original */
            $app->redirect($url);
        }else{
            
            $this->_finishRequest([
                'redirect' => $url 
            ]);
        }
 

    }
	public function GET_teste(){
		/** @var App $app */
        $app = App::i();
		$acceptedsHash =[];
		$conn = $app->em->getConnection();
		
		$result=[];
		if($terms = $conn->fetchAll("SELECT * FROM user_meta WHERE key LIKE '%lgpd_%' AND object_id={$app->user->id}")){
			foreach($terms as $term){
				foreach(json_decode($term['value'], true) as $value){
					var_dump($value['md5']);
						$result[]= $value['md5'];
				}

			}

		}
		// array_push($app->user->lgpd)
		var_dump($result);
    }
}
