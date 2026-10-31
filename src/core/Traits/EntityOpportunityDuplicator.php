<?php
namespace MapasCulturais\Traits;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationStep;

trait EntityOpportunityDuplicator {

    private $entityOpportunity;
    private $entityNewOpportunity;

    /** @var array<string, string> fieldName/fileGroupName original => da cópia */
    private $registrationFieldNameMap = [];

    /** @var array<int, Opportunity> mapa id da fase original => fase clonada */
    private $opportunityPhaseMap = [];

    function ALL_duplicate(){
        $app = App::i();

        $this->requireAuthentication();
        $this->registrationFieldNameMap = [];
        $this->opportunityPhaseMap = [];
        $this->entityOpportunity = $this->requestedEntity;
        $this->entityNewOpportunity = $this->cloneOpportunity();
        $this->opportunityPhaseMap[(int) $this->entityOpportunity->id] = $this->entityNewOpportunity;


        $this->duplicateEvaluationMethods();
        $this->duplicatePhases();
        $this->duplicateMetadata();
        $this->relinkDuplicatedAppealPhases();
        $this->duplicateRegistrationFieldsAndFiles();
        $this->remapDuplicatedEvaluationFields();
        $this->duplicateMetalist();
        $this->duplicateFiles();
        /** @todo Verificar a possibilidade de perguntar no momento da duplicação se deve ou não duplicar selos e agentes relacionados ao edital */
        // $this->duplicateAgentRelations();
        // $this->duplicateSealsRelations();

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
        $this->resetDuplicatedEvaluationMethodConfiguration($this->entityNewOpportunity);

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
        $this->entityNewOpportunity->createTimestamp = new DateTime('now');
        $this->entityNewOpportunity->save(true);

        return $this->entityNewOpportunity;
    }

    /**
     * O nome difere de EntityManagerModel::resetClonedEvaluationMethodConfiguration
     * porque ambos os traits são usados no mesmo controller.
     */
    private function resetDuplicatedEvaluationMethodConfiguration(Opportunity $opportunity): void
    {
        $opportunity->evaluationMethodConfiguration = null;

        if (is_object($opportunity->__magicGetterCache ?? null)) {
            unset($opportunity->__magicGetterCache->evaluationMethodConfiguration);
        }
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

            /** @todo Verificar possibilidade de questionar se deve ou não copiar a commisão de avaliação no momento da duplicação */
            // foreach ($evaluationMethodConfiguration->getAgentRelations() as $agentRelation_) {
            //     $agentRelation = clone $agentRelation_;
            //     $agentRelation->owner = $newMethodConfiguration;
            //     $agentRelation->save(true);
            // }
        }
    }

    private function duplicatePhases() : void
    {
        $app = App::i();

        $phases = $app->repo('Opportunity')->findBy([
            'parent' => $this->entityOpportunity
        ]);
        foreach ($phases as $phase) {
            // Fases de recurso são clonadas em relinkDuplicatedAppealPhases / duplicateAppealPhaseChild,
            // com parent apontando para a fase de mérito correspondente na cópia — não para a 1ª fase.
            if ($this->isAppealPhaseOpportunity($phase)) {
                continue;
            }

            if (!$phase->getMetadata('isLastPhase')) {
                $newPhase = $this->cloneOpportunityPhase($phase, $this->entityNewOpportunity);
                $this->opportunityPhaseMap[(int) $phase->id] = $newPhase;
                $this->duplicatePhaseEvaluationMethods($phase, $newPhase);
                $this->duplicateAppealPhaseChild($phase, $newPhase);
            }

            if ($phase->getMetadata('isLastPhase')) {
                $publishDate = $phase->publishTimestamp;
            }
        }

        // Recurso cujo parent é a própria oportunidade principal (avaliação na 1ª fase)
        $this->duplicateAppealPhaseChild($this->entityOpportunity, $this->entityNewOpportunity);

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

    /**
     * Indica se a opportunity é (ou deveria ser tratada como) fase de recurso.
     */
    private function isAppealPhaseOpportunity(Opportunity $phase): bool
    {
        return (bool) $phase->isAppealPhase
            || (int) $phase->status === Opportunity::STATUS_APPEAL_PHASE;
    }

    /**
     * Clona uma fase (exceto vínculo appealPhase, remapeado depois) sob o parent informado.
     */
    private function cloneOpportunityPhase(Opportunity $phase, Opportunity $newParent): Opportunity
    {
        $newPhase = clone $phase;
        $this->resetDuplicatedEvaluationMethodConfiguration($newPhase);
        $newPhase->setParent($newParent);

        foreach ($phase->getMetadata() as $metadataKey => $metadataValue) {
            if ($metadataKey === 'appealPhase') {
                continue;
            }
            if (!is_null($metadataValue) && $metadataValue != '') {
                $newPhase->setMetadata($metadataKey, $metadataValue);
                $newPhase->save(true);
            }
        }

        $newPhase->save(true);

        return $newPhase;
    }

    private function duplicatePhaseEvaluationMethods(Opportunity $phase, Opportunity $newPhase): void
    {
        $app = App::i();

        $evaluationMethodConfigurations = $app->repo('EvaluationMethodConfiguration')->findBy([
            'opportunity' => $phase
        ]);

        foreach ($evaluationMethodConfigurations as $evaluationMethodConfiguration) {
            $newMethodConfiguration = clone $evaluationMethodConfiguration;
            $newMethodConfiguration->setOpportunity($newPhase);
            $newMethodConfiguration->save(true);

            foreach ($evaluationMethodConfiguration->getMetadata() as $metadataKey => $metadataValue) {
                // appealPhase no EMC é espelho do da opportunity; relinkDuplicatedAppealPhases cuida disso
                if ($metadataKey === 'appealPhase') {
                    continue;
                }
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

    /**
     * Duplica a fase de recurso filha de $phase (se existir) com parent = $newPhase.
     */
    private function duplicateAppealPhaseChild(Opportunity $phase, Opportunity $newPhase): void
    {
        $app = App::i();

        $appeals = $app->repo('Opportunity')->findBy([
            'parent' => $phase,
            'status' => Opportunity::STATUS_APPEAL_PHASE,
        ]);

        // Fallback: metadata isAppealPhase (caso status divergente)
        if (!$appeals) {
            foreach ($app->repo('Opportunity')->findBy(['parent' => $phase]) as $child) {
                if ($this->isAppealPhaseOpportunity($child) && !isset($this->opportunityPhaseMap[(int) $child->id])) {
                    $appeals[] = $child;
                }
            }
        }

        foreach ($appeals as $appeal) {
            if (isset($this->opportunityPhaseMap[(int) $appeal->id])) {
                continue;
            }

            $newAppeal = $this->cloneOpportunityPhase($appeal, $newPhase);
            $newAppeal->status = Opportunity::STATUS_APPEAL_PHASE;
            $newAppeal->isAppealPhase = true;
            $newAppeal->save(true);

            $this->opportunityPhaseMap[(int) $appeal->id] = $newAppeal;
            $this->duplicatePhaseEvaluationMethods($appeal, $newAppeal);

            $newPhase->appealPhase = $newAppeal;
            $newPhase->save(true);
        }
    }

    /**
     * Garante que metadados appealPhase da cópia apontem para as fases de recurso clonadas
     * (e que o parent de cada recurso seja a fase de mérito correspondente na cópia).
     */
    private function relinkDuplicatedAppealPhases(): void
    {
        foreach ($this->opportunityPhaseMap as $oldPhaseId => $newPhase) {
            $oldPhase = $oldPhaseId == $this->entityOpportunity->id
                ? $this->entityOpportunity
                : App::i()->repo('Opportunity')->find($oldPhaseId);

            if (!$oldPhase) {
                continue;
            }

            $oldAppeal = $oldPhase->appealPhase;
            if (!$oldAppeal) {
                continue;
            }

            $newAppeal = $this->opportunityPhaseMap[(int) $oldAppeal->id] ?? null;
            if (!$newAppeal) {
                continue;
            }

            if (!$newAppeal->parent || (int) $newAppeal->parent->id !== (int) $newPhase->id) {
                $newAppeal->setParent($newPhase);
                $newAppeal->save(true);
            }

            $newPhase->appealPhase = $newAppeal;
            $newPhase->save(true);
        }
    }

    private function duplicateMetadata() : void
    {
        foreach ($this->entityOpportunity->getMetadata() as $metadataKey => $metadataValue) {
            // appealPhase é religado em relinkDuplicatedAppealPhases para a cópia do recurso
            if ($metadataKey === 'appealPhase') {
                continue;
            }
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
        $stepMap = [];

        $reusableQueue = $this->findReusableRegistrationStepsWithoutFieldsOrFilesForDuplicator($this->entityNewOpportunity);

        $existingSteps = [];
        foreach ($this->entityNewOpportunity->registrationSteps as $newStep) {
            if ((int) $newStep->opportunity->id !== (int) $this->entityNewOpportunity->id) {
                continue;
            }
            $existingSteps[$newStep->id] = $newStep;
        }

        $oldSteps = $this->entityOpportunity->registrationSteps->toArray();
        usort($oldSteps, function ($a, $b) {
            $c = $a->displayOrder <=> $b->displayOrder;

            return $c !== 0 ? $c : $a->id <=> $b->id;
        });

        foreach ($oldSteps as $oldStep) {
            if ($reusableQueue !== []) {
                $target = array_shift($reusableQueue);
                $this->copyRegistrationStepPropertiesFromToForDuplicator($oldStep, $target);
                $stepMap[$oldStep->id] = $target;

                continue;
            }

            $stepMap[$oldStep->id] = $existingSteps[$oldStep->id] ?? (function () use ($oldStep) {
                $newStep = clone $oldStep;
                $newStep->setOpportunity($this->entityNewOpportunity);
                $newStep->save(true);
                return $newStep;
            })();
        }

        // Clonando os RegistrationFieldConfigurations e associar aos novos steps
        foreach ($this->entityOpportunity->getRegistrationFieldConfigurations() as $registrationFieldConfiguration) {
            $originalFieldName = $registrationFieldConfiguration->fieldName;
            $fieldConfiguration = clone $registrationFieldConfiguration;
            $fieldConfiguration->setOwnerId($this->entityNewOpportunity->id);

            // Atualizando o Step garantindo a correspondência correta
            if ($registrationFieldConfiguration->step && isset($stepMap[$registrationFieldConfiguration->step->id])) {
                $fieldConfiguration->setStep($stepMap[$registrationFieldConfiguration->step->id]);
            }

            $fieldConfiguration->save(true);
            $this->registrationFieldNameMap[$originalFieldName] = $fieldConfiguration->fieldName;
        }

        // Clonando os RegistrationFileConfigurations e associar aos novos steps
        foreach ($this->entityOpportunity->getRegistrationFileConfigurations() as $registrationFileConfiguration) {
            $originalGroupName = $registrationFileConfiguration->fileGroupName;
            $fileConfiguration = clone $registrationFileConfiguration;
            $fileConfiguration->setOwnerId($this->entityNewOpportunity->id);

            // Atualizando o Step garantindo a correspondência correta
            if ($registrationFileConfiguration->step && isset($stepMap[$registrationFileConfiguration->step->id])) {
                $fileConfiguration->setStep($stepMap[$registrationFileConfiguration->step->id]);
            }

            $fileConfiguration->save(true);
            $this->registrationFieldNameMap[$originalGroupName] = $fileConfiguration->fileGroupName;
        }

        $this->deleteOrphanEmptyRegistrationStepsForDuplicator($this->entityNewOpportunity, $stepMap);
    }

    /**
     * Reescreve a configuração de campos visíveis da cópia e de suas fases
     *
     * @return void
     * @access private
     */
    private function remapDuplicatedEvaluationFields(): void
    {
        $app = App::i();

        $this->remapDuplicatedAvaliableEvaluationFields($this->entityNewOpportunity);

        foreach ($app->repo('Opportunity')->findBy(['parent' => $this->entityNewOpportunity]) as $phase) {
            $this->remapDuplicatedAvaliableEvaluationFields($phase);
        }
    }

    /**
     * Espelho de EntityManagerModel::remapAvaliableEvaluationFields, usando o
     * registrationFieldNameMap preenchido por este trait (private não é compartilhado entre traits).
     */
    private function remapDuplicatedAvaliableEvaluationFields(Opportunity $opportunity): void
    {
        $stored = $opportunity->getStoredAvaliableEvaluationFields();

        if (!$stored) {
            return;
        }

        $remapped = [];
        foreach ($stored as $name => $value) {
            if (isset($this->registrationFieldNameMap[$name])) {
                $remapped[$this->registrationFieldNameMap[$name]] = $value;
            } elseif (!preg_match('#^(field|rfc)_\d+$#', (string) $name)) {
                $remapped[$name] = $value;
            }
        }

        $opportunity->avaliableEvaluationFields = $remapped;

        if (is_object($opportunity->__magicGetterCache ?? null)) {
            unset($opportunity->__magicGetterCache->avaliableEvaluationFields);
        }

        $opportunity->save(true);
    }

    /**
     * @return RegistrationStep[]
     */
    private function findReusableRegistrationStepsWithoutFieldsOrFilesForDuplicator(Opportunity $opportunity): array
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $oppId = (int) $opportunity->id;

        $ids = $conn->fetchFirstColumn(
            'SELECT rs.id FROM registration_step rs
             WHERE rs.opportunity_id = ?
             AND NOT EXISTS (SELECT 1 FROM registration_field_configuration rfc WHERE rfc.step_id = rs.id)
             AND NOT EXISTS (SELECT 1 FROM registration_file_configuration rfile WHERE rfile.step_id = rs.id)
             ORDER BY rs.display_order ASC, rs.id ASC',
            [$oppId]
        );

        $steps = [];
        foreach ($ids as $id) {
            $step = $app->repo('RegistrationStep')->find((int) $id);
            if ($step && (int) $step->opportunity->id === $oppId) {
                $steps[] = $step;
            }
        }

        return $steps;
    }

    private function copyRegistrationStepPropertiesFromToForDuplicator(RegistrationStep $from, RegistrationStep $to): void
    {
        $to->name = $from->name;
        $to->displayOrder = $from->displayOrder;
        $meta = $from->metadata;
        $to->metadata = is_object($meta)
            ? json_decode(json_encode($meta), false) ?: new \stdClass()
            : new \stdClass();
        $to->save(true);
    }

    /**
     * @param array<int, RegistrationStep> $stepMap
     */
    private function deleteOrphanEmptyRegistrationStepsForDuplicator(Opportunity $opportunity, array $stepMap): void
    {
        $app = App::i();
        $usedIds = [];
        foreach ($stepMap as $step) {
            $usedIds[(int) $step->id] = true;
        }

        $conn = $app->em->getConnection();
        $rows = $conn->fetchAllAssociative(
            'SELECT rs.id FROM registration_step rs
             WHERE rs.opportunity_id = ?
             AND NOT EXISTS (SELECT 1 FROM registration_field_configuration rfc WHERE rfc.step_id = rs.id)
             AND NOT EXISTS (SELECT 1 FROM registration_file_configuration rfile WHERE rfile.step_id = rs.id)',
            [(int) $opportunity->id]
        );

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (isset($usedIds[$id])) {
                continue;
            }
            $orphan = $app->repo('RegistrationStep')->find($id);
            if ($orphan) {
                $orphan->delete(true);
            }
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
        $sourceOpportunityId = (int) $this->entityOpportunity->id;
        $targetOpportunityId = (int) $this->entityNewOpportunity->id;
        $targetPrefix = "opportunity/{$targetOpportunityId}/";

        $opportunityFiles = $app->repo('OpportunityFile')->findBy([
            'owner' => $this->entityOpportunity
        ]);

        $fileMap = [];

        foreach ($opportunityFiles as $opportunityFile) {
            $group = (string) $opportunityFile->getGroup();
            if (str_starts_with($group, 'img:')) {
                continue;
            }

            $newMethodOpportunityFile = clone $opportunityFile;
            $newMethodOpportunityFile->owner = $this->entityNewOpportunity;
            $newMethodOpportunityFile->save(true);
            $fileMap[(int) $opportunityFile->id] = $newMethodOpportunityFile;
        }

        foreach ($opportunityFiles as $opportunityFile) {
            $newMethodOpportunityFile = $fileMap[(int) $opportunityFile->id] ?? null;
            if (!$newMethodOpportunityFile) {
                continue;
            }

            $oldRelativePath = (string) $opportunityFile->getRelativePath();
            $newRelativePath = preg_replace(
                "#(^|/)opportunity/{$sourceOpportunityId}/#",
                '$1' . $targetPrefix,
                $oldRelativePath
            );
            if (!$newRelativePath || $newRelativePath === $oldRelativePath) {
                $newRelativePath = $targetPrefix . basename($oldRelativePath);
            }

            // Relaciona transformações/derivados ao novo parent quando aplicável.
            if ($opportunityFile->parent && isset($fileMap[(int) $opportunityFile->parent->id])) {
                $newMethodOpportunityFile->parent = $fileMap[(int) $opportunityFile->parent->id];
                $newMethodOpportunityFile->save(true);

                $oldParentId = (int) $opportunityFile->parent->id;
                $newParentId = (int) $newMethodOpportunityFile->parent->id;
                $newRelativePath = preg_replace(
                    "#/file/{$oldParentId}/#",
                    "/file/{$newParentId}/",
                    $newRelativePath
                );
            }

            if ($newRelativePath !== $oldRelativePath) {
                $app->em->getConnection()->update(
                    'file',
                    ['path' => $newRelativePath],
                    ['id' => (int) $newMethodOpportunityFile->id]
                );
            }

            $oldFullPath = $opportunityFile->getPath();
            $newFullPath = preg_replace(
                "#(^|/)opportunity/{$sourceOpportunityId}/#",
                '$1' . $targetPrefix,
                $oldFullPath
            );
            if (!$newFullPath || $newFullPath === $oldFullPath) {
                $newFullPath = str_replace($oldRelativePath, $newRelativePath, $oldFullPath);
            }

            if ($opportunityFile->parent && isset($fileMap[(int) $opportunityFile->parent->id])) {
                $oldParentId = (int) $opportunityFile->parent->id;
                $newParentId = (int) $newMethodOpportunityFile->parent->id;
                $newFullPath = preg_replace(
                    "#/file/{$oldParentId}/#",
                    "/file/{$newParentId}/",
                    $newFullPath
                );
            }

            if (!file_exists($oldFullPath)) {
                $app->log->warning("Arquivo não encontrado ao duplicar oportunidade: {$oldFullPath}");
                continue;
            }

            $destinationDir = dirname($newFullPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0775, true);
            }

            if (!copy($oldFullPath, $newFullPath)) {
                $app->log->warning("Falha ao copiar arquivo da oportunidade: {$oldFullPath} -> {$newFullPath}");
            }
        }

        $newOpportunityFiles = $app->repo('OpportunityFile')->findBy([
            'owner' => $this->entityNewOpportunity
        ]);

        foreach ($newOpportunityFiles as $newOpportunityFile) {
            $currentRelativePath = (string) $newOpportunityFile->getRelativePath();
            $normalizedRelativePath = preg_replace(
                "#(^|/)opportunity/\d+/#",
                '$1' . $targetPrefix,
                $currentRelativePath
            );

            if ($newOpportunityFile->parent) {
                $normalizedRelativePath = preg_replace(
                    "#/file/\d+/#",
                    "/file/" . (int) $newOpportunityFile->parent->id . "/",
                    $normalizedRelativePath,
                    1
                );
            }

            if (!$normalizedRelativePath || $normalizedRelativePath === $currentRelativePath) {
                continue;
            }

            $currentFullPath = $newOpportunityFile->getPath();
            $normalizedFullPath = str_replace($currentRelativePath, $normalizedRelativePath, $currentFullPath);

            $app->em->getConnection()->update(
                'file',
                ['path' => $normalizedRelativePath],
                ['id' => (int) $newOpportunityFile->id]
            );

            if (file_exists($currentFullPath)) {
                $destinationDir = dirname($normalizedFullPath);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0775, true);
                }

                if (!file_exists($normalizedFullPath) && !copy($currentFullPath, $normalizedFullPath)) {
                    $app->log->warning("Falha ao normalizar cópia de arquivo: {$currentFullPath} -> {$normalizedFullPath}");
                }
            }
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
