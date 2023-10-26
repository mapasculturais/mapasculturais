<?php

namespace LGPD;

use DateTime;
use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    const key = "lgpd_redirect_referer";

    function __construct($config = [])
    {
        $config += [];

        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        $self  = $this;
        $app->hook('mapas.printJsObject:before', function () use ($app, $self) {
            /** @var \MapasCulturais\Theme $this */
            $terms = [];
            foreach ($app->config['module.LGPD'] as $slug => $term) {
                $term['md5'] = Module::createHash($term['text']);
                $terms[$slug] = $term;
            }
            $this->jsObject['config']['LGPD'] = $terms;
            $this->jsObject['hashAccepteds'] = $self->hashAccepteds();
        });

        $app->hook('GET(<<*>>):before,-GET(<<lgpd|auth>>.<<*>>):before', function () use ($app) {

            if ($app->user->is('guest'))
                return;

            $skip_routes = [
                ["lgpd", "accept"],
                ["site", "search"]
            ];

            $route = [$this->id, $this->action];

            if (!in_array($route, $skip_routes) && !$app->request->isAjax()) {
                $_SESSION[self::key] = $_SERVER['REQUEST_URI'] ?? "";
            }

            $user = $app->user;
            $config = $app->config['module.LGPD'];

            foreach ($config as $key => $value) {
                $term_hash = self::createHash($value['text']);
                $accept_terms = $user->{"lgpd_{$key}"};
                if (!isset($accept_terms->$term_hash)) {
                    if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
                        $url =  $app->createUrl('lgpd', 'accept', [$key]);
                    } else {
                        $url =  $app->createUrl('lgpd', 'accept') . "#{$key}";
                    }
                    $app->redirect($url);
                }
            }
        });
    }

    /**
     * @param string $meta
     * @param array $accepted_terms
     * @return void
     */
    public function acceptTerms($slugs, $user = null) {
        /**
         * @var App $app
         */
        $app = App::i();
        $user = $user ?: $app->user;
        
        if($slugs){
            foreach ($slugs as $slug) {
                $config = $app->config['module.LGPD'][$slug];
                $text = $config['text'];    
                $accepted_terms["lgpd_{$slug}"] = [
                    'timestamp' => (new DateTime())->getTimestamp(),
                    'md5' => Module::createHash($text),
                    'text' => $text,
                    'ip' => $app->request->getIp(),
                    'userAgent' => $app->request->getUserAgent(),
                ];
            }

            foreach ($accepted_terms as $meta => $values) {
                $_accepted_terms = $user->$meta ?: (object)[];
                $index = $values['md5'];
                if (!isset($_accepted_terms->$index)) {
                    $_accepted_terms->$index = $values;
                    $user->$meta = $_accepted_terms;
                }
        
                foreach ($accepted_terms as $meta => $values) {
                    $_accepted_terms = $user->$meta ?: (object)[];
                    $index = $values['md5'];
                    if (!isset($_accepted_terms->$index)) {
                        $_accepted_terms->$index = $values;
                        $user->$meta = $_accepted_terms;
                    }
                }
                $user->save();
            }
        }
    }

    /**
     * @param string $text
     * @return string
     */
    public static function createHash($text)
    {
        $text = str_replace(" ", "", trim($text));
        $text = strip_tags($text);
        $text = str_replace("\n", "", trim($text));
        $text = str_replace("\t", "", trim($text));
        $text = strtolower($text);
        return md5($text);
    }

    public function hashAccepteds()
    {
        /** @var App $app */
        $app = App::i();
        $acceptedsHash = [];
        $conn = $app->em->getConnection();

        $result = [];
        if ($terms = $conn->fetchAll("SELECT * FROM user_meta WHERE key LIKE '%lgpd_%' AND object_id={$app->user->id}")) {
            foreach ($terms as $term) {
                foreach (json_decode($term['value'], true) as $value) {
                    $result[] = $value['md5'];
                }
            }
        }
        return $result;
    }


    public function register()
    {
        $app = App::i();
        $app->registerController('lgpd', Controller::class);
        $config = $app->config['module.LGPD'];
        foreach ($config as $key => $value) {
            $this->registerUserMetadata("lgpd_{$key}", [
                'label' => $value['title'],
                'type' => 'json',
                'private' => true,
                'default' => '{}',
            ]);
        }
    }
}
