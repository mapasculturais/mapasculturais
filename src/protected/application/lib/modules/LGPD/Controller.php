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
        $config_lgpd = $app->config['module.LGPD'];
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $term_slug = $this->data[0] ?? null;
            /** @todo Verificar term_slug */
            $config = $config_lgpd[$term_slug];
    
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
    
            $this->layout = 'lgpd';
            $app->view->enqueueStyle('app', 'lgpd-file', 'css/lgpd.css');
            $this->render('accept', ['url' => $url, 'title' => $title, 'text' => $text, 'accepted' => $accepted]);
        } else {
            $this->layout = 'lgpdV2';
            $this->render('terms', ['terms' => $config_lgpd]);
        }
       
    }

    public function POST_accept()
    {
        $app = App::i();        
        $term_slug = null;
        $term_slug = !is_array($this->data[0]) ? [$this->data[0]] : $this->data[0];
        $id = $this->data[1] ?? null;
        $accept_terms = [];
        foreach ($term_slug as $slug) {
            $config = $app->config['module.LGPD'][$slug];
            $text = $config['text'];
    
            $accept_terms["lgpd_{$slug}"] = [
                'timestamp' => (new DateTime())->getTimestamp(),
                'md5' => Module::createHash($text),
                'text' => $text,
                'ip' => $app->request()->getIp(),
                'userAgent' => $app->request()->getUserAgent(),
            ];
        }
        $this->verifiedTerms($accept_terms, $id);
    }

    /**
     * @param string $meta
     * @param array $accept_terms
     * @return void
     */
    private function verifiedTerms($accept_terms, $id = null)
    {
        /** @var App $app */
        $app = App::i();

        if ($id) {
            $user = $app->repo('User')->find($id);
        } else {
            $user = $app->user;
        }

        foreach ($accept_terms as $meta=>$values) {
            $_accept_lgpd = $user->$meta ?: (object)[];
            $index = $values['md5'];
            if (!isset($_accept_lgpd->$index)) {
                $_accept_lgpd->$index = $values;
                $user->$meta = $_accept_lgpd;
            }
        }
        $app->disableAccessControl();
        $user->save();
        $app->enableAccessControl();

        $url = $_SESSION[Module::key] ?? "/";
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            /** @todo Redirecionar pra url original */
            $app->redirect($url);
        }else{
            $this->json(['redirect' =>  $url]);
        }
    }
}
