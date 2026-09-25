<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Opportunity;

class ImportFields extends JobType
{
    public const SLUG = 'ImportFields';

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "ImportFields:{$data['opportunity']->id}";
    }

    protected function _execute(Job $job)
    {
        $app = App::i();

        /** @var Opportunity|null $opportunity */
        $opportunity        = $job->opportunity;
        $import_source      = $job->importSource ?: null;
        $authenticated_user = $job->authenticatedUser ?: null;

        if (!$opportunity || !$import_source) {
            $app->log->info('[ImportFieldsJob] Job sem oportunidade ou fonte de importação, finalizando.');
            return true;
        }

        if (is_array($import_source)) {
            $import_source = json_decode(json_encode($import_source));
        }

        try {
            $app->conn->beginTransaction();

            $app->log->info(sprintf(
                '[ImportFieldsJob] Iniciando importação de campos para oportunidade #%d',
                $opportunity->id,
            ));

            // Importa os campos da oportunidade
            $opportunity->importFields($import_source);

            $app->conn->commit();

            $app->log->info(sprintf(
                '[ImportFieldsJob] Importação de campos finalizada para oportunidade #%d.',
                $opportunity->id
            ));
        } catch (\Throwable $e) {
            if ($app->conn->isTransactionActive()) {
                $app->conn->rollBack();
            }

            $app->log->error(sprintf(
                '[ImportFieldsJob] Erro ao importar campos para oportunidade #%d: %s',
                $opportunity->id,
                $e->getMessage()
            ));

            $app->log->error($e->getTraceAsString());

            // Envia e-mail de erro
            if ($authenticated_user) {
                try {
                    $this->sendErrorMailNotification($authenticated_user, $opportunity);
                } catch (\Throwable $mail_error) {
                    $app->log->error(sprintf(
                        '[ImportFieldsJob] Erro ao enviar e-mail de erro para oportunidade #%d: %s',
                        $opportunity->id,
                        $mail_error->getMessage()
                    ));
                }
            }

            return false;
        }

        // Envia e-mail de sucesso
        if ($authenticated_user) {
            try {
                $this->sendSuccessMailNotification($authenticated_user, $opportunity);
            } catch (\Throwable $mail_error) {
                $app->log->error(sprintf(
                    '[ImportFieldsJob] Erro ao enviar e-mail de sucesso para oportunidade #%d: %s',
                    $opportunity->id,
                    $mail_error->getMessage()
                ));
            }
        }

        return true;
    }

    private function sendSuccessMailNotification($user, Opportunity $opportunity): void
    {
        $app = App::i();

        $template = 'import_fields_success';
        $data = [
            'userName'         => $user->profile->name,
            'opportunityTitle' => $opportunity->name,
        ];

        $message = $app->renderMailerTemplate($template, $data);

        $app->createAndSendMailMessage([
            'from'    => $app->config['mailer.from'],
            'to'      => $user->email,
            'subject' => sprintf("[{$app->siteName}] %s", $message['title']),
            'body'    => $message['body'],
        ]);
    }

    private function sendErrorMailNotification($user, Opportunity $opportunity): void
    {
        $app = App::i();

        $template = 'import_fields_error';
        $data = [
            'userName'         => $user->profile->name,
            'opportunityTitle' => $opportunity->name,
        ];

        $message = $app->renderMailerTemplate($template, $data);

        $app->createAndSendMailMessage([
            'from'    => $app->config['mailer.from'],
            'to'      => $user->email,
            'subject' => sprintf("[{$app->siteName}] %s", $message['title']),
            'body'    => $message['body'],
        ]);
    }
}

