<?php

namespace MapasCulturais\Themes\BaseV2;

use MapasCulturais\i;
use MapasCulturais\App;

/**
 * @method void import(string $components) Importa lista de componentes Vue. * 
 */
class Theme extends \MapasCulturais\Theme
{
    function getVersion() {
        return 2;
    }

    static function getThemeFolder()
    {
        return __DIR__;
    }

    function _init()
    {
        $app = App::i();
        $this->bodyClasses[] = 'base-v2';
        $this->enqueueStyle('app-v2', 'main', 'css/theme-BaseV2.css');
        $this->assetManager->publishFolder('fonts');

        // Manifest do five icon
        $app->hook('GET(site.webmanifest)', function() use ($app) {
            /** @var \MapasCulturais\Controller $this */
            $this->json([
                'icons' => [
                    [ 'src' => $app->view->asset('img/favicon-192x192.png', false), 'type' => 'image/png', 'sizes' => '192x192' ],
                    [ 'src' => $app->view->asset('img/favicon-512x512.png', false), 'type' => 'image/png', 'sizes' => '512x512' ],
                ],
            ]);
        });

        $app->hook('template(<<*>>.head):end', function () {
            echo "<script>
                    document.addEventListener('DOMContentLoaded', (e) => {
                        let opacity = 0.01;
                        globalThis.opacityInterval = setInterval(() => {
                            if(opacity >= 1) {
                                clearInterval(globalThis.opacityInterval);
                            }
                            document.body.style.opacity = opacity;
                            opacity += 0.02;
                        },5);
                    });
                </script>";
        });

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->addDocumentMetas();
        });
    }

    function register()
    {
    }

    function addDocumentMetas() {
        $app = App::i();
        $entity = $this->controller->requestedEntity;

        $site_name = $app->siteName;
        $image_url_twitter = $app->view->asset($app->config['share.image_twitter'], false);
        $image_url = $app->view->asset($app->config['share.image'], false);

        $title = $app->view->getTitle($entity);
        if ($entity) {
            $description = $entity->shortDescription ? $entity->shortDescription : $title;
            if ($entity->avatar && $entity->avatar->transform('avatarBig')){
                $image_url = $entity->avatar->transform('avatarBig')->url;
                $image_url_twitter = $entity->avatar->transform('avatarBig')->url;
            }
        }else {
            $description = $app->siteDescription;
        }
        // for responsive
        $this->documentMeta[] = array("name" => 'viewport', 'content' => 'width=device-width, initial-scale=1, maximum-scale=1.0');
        // for google
        $this->documentMeta[] = array("name" => 'description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'keywords', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'author', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'copyright', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'application-name', 'content' => $site_name);

        // for twitter
        $this->documentMeta[] = array("name" => 'twitter:card', 'content' => 'photo');
        $this->documentMeta[] = array("name" => 'twitter:title', 'content' => $title);
        $this->documentMeta[] = array("name" => 'twitter:description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'twitter:image', 'content' => $image_url_twitter);

        // for facebook/Linkedin
        $this->documentMeta[] = array("property" => 'og:title', 'content' => $title);
        $this->documentMeta[] = array("property" => 'og:type', 'content' => 'article');
        $this->documentMeta[] = array("property" => 'og:image', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:image:url', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:description', 'content' => $description);
        $this->documentMeta[] = array("property" => 'og:site_name', 'content' => $site_name);
        $this->documentMeta[] = array("property" => 'og:image:width', 'content' => "1200");
        $this->documentMeta[] = array("property" => 'og:image:height', 'content' => "630");
        
        if ($entity) {
            $this->documentMeta[] = array("property" => 'og:url', 'content' => $entity->singleUrl);
            $this->documentMeta[] = array("property" => 'og:published_time', 'content' => $entity->createTimestamp->format('Y-m-d'));

            // @TODO: modified time is not implemented
            // $this->documentMeta[] = array( "property" => 'og:modified_time',   'content' => $entity->modifiedTimestamp->format('Y-m-d'));
        }
    }
}
