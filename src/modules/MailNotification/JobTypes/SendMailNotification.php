<?php

namespace MailNotification\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Job;

class SendMailNotification extends JobType
{
    const SLUG = "sendmailnotification";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "sendmailnotification:{$data['registrationId']}".uniqid();
    }

    protected function _execute(Job $job)
    {

        $app = App::i();

        $registration = $app->repo("Registration")->find($job->registrationId);
       
        $message = $app->renderMailerTemplate($job->template, [
            'siteName' => $app->view->dict('site: name', false),
            'baseUrl' => $app->getBaseUrl(),
            'userName' => $registration->owner->name,
            'projectId' => $registration->opportunity->id,
            'projectName' => $registration->opportunity->name,
            'registrationId' => $registration->id,
            'registrationNumber' => $registration->number,
            'statusTitle' => $registration->getStatusNameById($registration->status),
            'statusNum' => $registration->status,
        ]);
      
        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => $registration->owner->emailPrivado,
            'subject' => $message['title'],
            'body' => $message['body'],
        ];

        return $app->createAndSendMailMessage($email_params);
    }
}
