<?php
namespace MailNotification;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{

    public function _init()
    {
        $app = App::i();
    
        $self = $this; 

        $app->hook("entity(Registration).send:after", function () {

        });

        $app->hook("entity(Registration).save:finish", function () use($self){
            
        });
    }

    public function register()
    {

    }

    public function sendEmail($template, $params = [])
    {
        $app = App::i();

        $filename = $app->view->resolveFilename("templates/pt_BR", $template);
        
        $_template = file_get_contents($filename);
        
        $params += ['teste' => 'olegario'];

        $mustache = new \Mustache_Engine();

        $content = $mustache->render($_template, $params);

        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => "email@email.com.br",
            'subject' => "Assunto",
            'body' => $content,
        ];

        $app->createAndSendMailMessage($email_params);
    }

}
