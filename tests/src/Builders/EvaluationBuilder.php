<?php

namespace Tests\Builders;

use MapasCulturais\App;
use Tests\Abstract\Builder;
use MapasCulturais\Entities\User;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;

class EvaluationBuilder extends Builder
{
    protected RegistrationEvaluation $instance;

    public function reset(User $user, ?Registration $registration = null, ?Opportunity $opportunity = null): static
    {
        $app = App::i();

        if(!$registration && !$opportunity) {
            throw new \Exception('Informe uma inscrição ou uma oportunidade');
        }

        if(!$registration) {
            $registration_id =  $app->conn->fetchScalar("
                SELECT r.id
                FROM registration r
                WHERE 
                    r.id IN (SELECT registration_id FROM evaluations WHERE valuer_user_id = :user_id) AND
                    r.id NOT IN (SELECT registration_id FROM registration_evaluation WHERE user_id = :user_id) AND
                    r.opportunity_id = :opportunity_id
                    ORDER BY r.id ASC
                LIMIT 1", [
                    'opportunity_id' => $opportunity->id,
                    'user_id' => $user->id,
                ]);

            if(!$registration_id) {
                throw new \Exception('Não foram encontradas inscrições para o usuário');
            }

            $registration = $app->repo('Registration')->find($registration_id);
        }

        $this->instance = new RegistrationEvaluation();
        $this->instance->registration = $registration;
        $this->instance->user = $user;

        return $this;
    }

    public function getInstance(): RegistrationEvaluation
    {
        return $this->instance;
    }

    public function fillRequiredProperties(): static
    {
        return $this;
    }

    public function setEvaluationData(array $evaluation_data): static
    {
        $this->instance->evaluationData = $evaluation_data;

        return $this;
    }

    public function conclude(bool $save = true, bool $flush = true): static
    {
        $this->instance->status = RegistrationEvaluation::STATUS_EVALUATED;

        if($save) {
            $this->instance->save($flush);
        }

        return $this;
    }

    public function send(bool $flush = true): static
    {
        $this->instance->send($flush);

        return $this;
    }
   
}