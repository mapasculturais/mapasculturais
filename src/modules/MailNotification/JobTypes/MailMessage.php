<?php
namespace MailNotification\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class MailMessage extends JobType
{
    const SLUG = "MailMessage";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $serialized_message = $data['message'] ?? null;

        if(!$serialized_message) {
            throw new \Exception("Message obrigatório");
        }

        return "MailMessage-" . md5($serialized_message);
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();

        $message = unserialize($job->message);
        
        $app->sendMailMessage($message);
    }
    
}