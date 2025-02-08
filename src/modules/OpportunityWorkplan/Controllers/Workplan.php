<?php

namespace OpportunityWorkplan\Controllers;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Workplan as EntitiesWorkplan;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Services\WorkplanService;

class Workplan extends \MapasCulturais\Controller
{
    public function GET_index()
    {
        $this->requireAuthentication();

        $app = App::i();

        if (!$this->data['id']) {
            $app->pass();
        }

        $registration = $app->repo(Registration::class)->find($this->data['id']);
        $workplan = $app->repo(EntitiesWorkplan::class)->findOneBy(['registration' => $registration->id]);

        $data = [
            'workplan' => null
        ];

        if ($workplan) {
            $data = [
                'workplan' => $workplan->jsonSerialize(),
            ];
        }
        

        $this->json($data);
    }

    public function POST_save()
    {
        $this->requireAuthentication();

        $app = App::i();

        $app->disableAccessControl();

        if (!$this->data['registrationId']) {
            $app->pass();
        }

        $registration = $app->repo(Registration::class)->find($this->data['registrationId']);
        $workplan = $app->repo(EntitiesWorkplan::class)->findOneBy(['registration' => $registration->id]);
        $app->em->beginTransaction();
        try {
            $workplanService = new WorkplanService();
            $workplan = $workplanService->save($registration, $workplan, $this->data);
            $app->em->commit();
        } catch(\Exception $e) {
            $app->em->rollback();
            $this->json(['error' => $e->getMessage()], 400);
        }
        
        $app->enableAccessControl();

        $this->json([
            'workplan' => $workplan->jsonSerialize(),
        ]);
    }
  

    public function DELETE_goal()
    {
        $this->requireAuthentication();

        $app = App::i();

        if (!$this->data['id']) {
            $app->pass();
        }

        $goal = $app->repo(Goal::class)->find($this->data['id']);

        if ($goal) {
            $app->em->remove($goal);
            $app->em->flush(); 

            $this->json(true);
        }
    }

    public function DELETE_delivery()
    {
        $this->requireAuthentication();

        $app = App::i();

        if (!$this->data['id']) {
            $app->pass();
        }

        $delivery = $app->repo(Delivery::class)->find($this->data['id']);

        if ($delivery) {
            $app->em->remove($delivery);
            $app->em->flush(); 

            $this->json(true);
        }
    }
}