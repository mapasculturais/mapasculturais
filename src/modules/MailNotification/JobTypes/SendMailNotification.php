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
        $phase = $registration->opportunity;
        $first_phase = $registration->opportunity->firstPhase;


        $params = [
            'siteName' => $app->siteName,
            'baseUrl' => $app->getBaseUrl(),
            'userName' => $registration->owner->name,
            'projectId' => $first_phase->id,
            'projectName' => $first_phase->name,
            'phaseId' => $phase->id,
            'phaseName' => $phase->name,
            'registrationId' => $registration->id,
            'registrationNumber' => $registration->number,
            'statusTitle' => $registration->getStatusNameById($registration->status),
            'statusNum' => $registration->status,
        ];

        $params += $job->params ?? [];
        $template = $job->template;

        $app->applyHook("sendMailNotification.registrationStart",[&$registration, &$template, &$params]);

        $message = $app->renderMailerTemplate($template, $params);
      
        $email_params = [
            'from' => $app->config['mailer.from'],
            'to' => $registration->owner->emailPrivado,
            'subject' => $message['title'],
            'body' => $message['body'],
        ];

        return $app->createAndSendMailMessage($email_params);
    }
}
