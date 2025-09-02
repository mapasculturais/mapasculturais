<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;

trait EntityOpportunityDuplicator {

    private $entityOpportunity;
    private $entityNewOpportunity;

    function ALL_duplicate(){
        $app = App::i();

        $this->requireAuthentication();
        $this->entityOpportunity = $this->requestedEntity;
        $this->entityNewOpportunity = $this->cloneOpportunity();


        $this->duplicateEvaluationMethods();
        $this->duplicatePhases();
        $this->duplicateMetadata();
        $this->duplicateRegistrationFieldsAndFiles();
        $this->duplicateMetalist();
        $this->duplicateFiles();
        $this->duplicateAgentRelations();
        $this->duplicateSealsRelations();

        $this->entityNewOpportunity->save(true);
       
        if($this->isAjax()){
            $this->json($this->entityOpportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    private function cloneOpportunity()
    {
        $app = App::i();

        $this->entityNewOpportunity = clone $this->entityOpportunity;

        $dateTime = new \DateTime();
        $now = $dateTime->format('d-m-Y H:i:s');
        $name = $this->entityOpportunity->name;
        $this->entityNewOpportunity->name = "$name  - [Cópia][$now]";
        $this->entityNewOpportunity->status = Entity::STATUS_DRAFT;
        $app->em->persist($this->entityNewOpportunity);
        $app->em->flush();

        $this->entityNewOpportunity->registrationCategories = $this->entityOpportunity->registrationCategories;
        $this->entityNewOpportunity->registrationProponentTypes = $this->entityOpportunity->registrationProponentTypes;
        $this->entityNewOpportunity->registrationRanges = $this->entityOpportunity->registrationRanges;
        $this->entityNewOpportunity->owner = $app->user->profile;
        $this->entityNewOpportunity->save(true);

        return $this->entityNewOpportunity;
    }

    private function duplicateEvaluationMethods() : void
    {
        $app = App::i();

        // duplica o método de avaliação para a oportunidade primária
        $evaluationMethodConfigurations = $app->repo('EvaluationMethodConfiguration')->findBy([
            'opportunity' => $this->entityOpportunity
        ]);
        foreach ($evaluationMethodConfigurations as $evaluationMethodConfiguration) {
            $newMethodConfiguration = clone $evaluationMethodConfiguration;
            $newMethodConfiguration->setOpportunity($this->entityNewOpportunity);
            $newMethodConfiguration->save(true);

            // duplica os metadados das configurações do modelo de avaliação
            foreach ($evaluationMethodConfiguration->getMetadata() as $metadataKey => $metadataValue) {
                $newMethodConfiguration->setMetadata($metadataKey, $metadataValue);
                $newMethodConfiguration->save(true);
            }

            foreach ($evaluationMethodConfiguration->getAgentRelations() as $agentRelation_) {
                $agentRelation = clone $agentRelation_;
                $agentRelation->owner = $newMethodConfiguration;
                $agentRelation->save(true);
            }
        }
    }

    private function duplicatePhases() : void
    {
        $app = App::i();

        $phases = $app->repo('Opportunity')->findBy([
            'parent' => $this->entityOpportunity
        ]);
        foreach ($phases as $phase) {
            if (!$phase->getMetadata('isLastPhase')) {
                $newPhase = clone $phase;
                $newPhase->setParent($this->entityNewOpportunity);

                // duplica os metadados das fases
                foreach ($phase->getMetadata() as $metadataKey => $metadataValue) {
                    if (!is_null($metadataValue) && $metadataValue != '') {
                        $newPhase->setMetadata($metadataKey, $metadataValue);
                        $newPhase->save(true);
                    }
                }

                $newPhase->save(true);

                // duplica os modelos de avaliações das fases
                $evaluationMethodConfigurations = $app->repo('EvaluationMethodConfiguration')->findBy([
                    'opportunity' => $phase
                ]);

                foreach ($evaluationMethodConfigurations as $evaluationMethodConfiguration) {
                    $newMethodConfiguration = clone $evaluationMethodConfiguration;
                    $newMethodConfiguration->setOpportunity($newPhase);
                    $newMethodConfiguration->save(true);

                    // duplica os metadados das configurações do modelo de avaliação para a fase
                    foreach ($evaluationMethodConfiguration->getMetadata() as $metadataKey => $metadataValue) {
                        $newMethodConfiguration->setMetadata($metadataKey, $metadataValue);
                        $newMethodConfiguration->save(true);
                    }

                    foreach ($evaluationMethodConfiguration->getAgentRelations() as $agentRelation_) {
                        $agentRelation = clone $agentRelation_;
                        $agentRelation->owner = $newMethodConfiguration;
                        $agentRelation->save(true);
                    }
                }
            }

            if ($phase->getMetadata('isLastPhase')) {
                $publishDate = $phase->publishTimestamp;
            }
        }

        if (isset($publishDate)) {
            $phases = $app->repo('Opportunity')->findBy([
                'parent' => $this->entityNewOpportunity
            ]);
    
            foreach ($phases as $phase) {
                if ($phase->getMetadata('isLastPhase')) {
                    $phase->setPublishTimestamp($publishDate);
                    $phase->save(true);
                }
            }
        }       
    }

    private function duplicateMetadata() : void
    {
        foreach ($this->entityOpportunity->getMetadata() as $metadataKey => $metadataValue) {
            if (!is_null($metadataValue) && $metadataValue != '') {
                $this->entityNewOpportunity->setMetadata($metadataKey, $metadataValue);
            }
        }

        $this->entityNewOpportunity->setTerms(['area' => $this->entityOpportunity->terms['area']]);
        $this->entityNewOpportunity->setTerms(['tag' => $this->entityOpportunity->terms['tag']]);
        $this->entityNewOpportunity->saveTerms();
    }
   
    private function duplicateRegistrationFieldsAndFiles(): void
    {
        // Criando um mapa de steps originais para os novos steps
        $stepMap = [];

        // Mapeando os steps existentes na nova Oportunidade
        $existingSteps = array_column($this->entityNewOpportunity->registrationSteps->toArray(), null, 'id');

        foreach ($this->entityOpportunity->registrationSteps as $oldStep) {
            // Reutilizando step existente ou criar um novo
            $stepMap[$oldStep->id] = $existingSteps[$oldStep->id] ?? (function () use ($oldStep) {
                $newStep = clone $oldStep;
                $newStep->setOpportunity($this->entityNewOpportunity);
                $newStep->save(true);
                return $newStep;
            })();
        }

        // Clonando os RegistrationFieldConfigurations e associar aos novos steps
        foreach ($this->entityOpportunity->getRegistrationFieldConfigurations() as $registrationFieldConfiguration) {
            $fieldConfiguration = clone $registrationFieldConfiguration;
            $fieldConfiguration->setOwnerId($this->entityNewOpportunity->id);

            // Atualizando o Step garantindo a correspondência correta
            if (isset($stepMap[$registrationFieldConfiguration->step->id])) {
                $fieldConfiguration->setStep($stepMap[$registrationFieldConfiguration->step->id]);
            }

            $fieldConfiguration->save(true);
        }

        // Clonando os RegistrationFileConfigurations e associar aos novos steps
        foreach ($this->entityOpportunity->getRegistrationFileConfigurations() as $registrationFileConfiguration) {
            $fileConfiguration = clone $registrationFileConfiguration;
            $fileConfiguration->setOwnerId($this->entityNewOpportunity->id);

            // Atualizando o Step garantindo a correspondência correta
            if (isset($stepMap[$registrationFileConfiguration->step->id])) {
                $fileConfiguration->setStep($stepMap[$registrationFileConfiguration->step->id]);
            }

            $fileConfiguration->save(true);
        }
    }

    private function duplicateMetalist() : void
    {
        foreach ($this->entityOpportunity->getMetaLists() as $metaList_) {
            foreach ($metaList_ as $metaList__) {
                $metalist = clone $metaList__;
                $metalist->setOwner($this->entityNewOpportunity);
            
                $metalist->save(true);
            }
        }
    }

    private function duplicateFiles() : void
    {
        $app = App::i();

        $opportunityFiles = $app->repo('OpportunityFile')->findBy([
            'owner' => $this->entityOpportunity
        ]);

        foreach ($opportunityFiles as $opportunityFile) {
            $newMethodOpportunityFile = clone $opportunityFile;
            $newMethodOpportunityFile->owner = $this->entityNewOpportunity;
            $newMethodOpportunityFile->save(true);
        }
    }

    private function duplicateAgentRelations() : void
    {
        foreach ($this->entityOpportunity->getAgentRelations() as $agentRelation_) {
            $agentRelation = clone $agentRelation_;
            $agentRelation->owner = $this->entityNewOpportunity;
            $agentRelation->save(true);
        }
    }

    private function duplicateSealsRelations() : void
    {
        foreach ($this->entityOpportunity->getSealRelations() as $sealRelation) {
            $this->entityNewOpportunity->createSealRelation($sealRelation->seal, true, true);
        }
    }
}
