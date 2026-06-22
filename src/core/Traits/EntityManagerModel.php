<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\RegistrationStep;

/**
 * Trait para gerenciamento de modelos de oportunidades
 * 
 * Este trait fornece funcionalidades para criar, clonar e gerenciar modelos
 * de oportunidades e suas fases no sistema Mapas Culturais.
 * 
 * @package MapasCulturais\Traits
 */
trait EntityManagerModel {

    /**
     * @var \MapasCulturais\Entities\Opportunity Entidade de oportunidade original
     * @access private
     */
    private $entityOpportunity;

    /**
     * @var \MapasCulturais\Entities\Opportunity Modelo de oportunidade gerado
     * @access private
     */
    private $entityOpportunityModel;

    /**
     * @var \MapasCulturais\Entity|null Cache do ownerEntity para evitar re-fetch em changeObjectType
     * @access private
     */
    private $cachedOwnerEntity = null;

    /**
     * Gera um modelo a partir de uma oportunidade existente
     * 
     * Este método cria uma cópia da oportunidade como modelo, incluindo
     * todos os métodos de avaliação, fases, termos, metadados, campos de
     * inscrição, arquivos e relações com selos.
     * 
     * @api ALL generatemodel
     * @return void Retorna JSON da entidade (AJAX) ou redireciona
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @requiresAuthentication
     */
    function ALL_generatemodel(){
        $app = App::i();

        $this->requireAuthentication();
        $this->entityOpportunity = $this->requestedEntity;
        $this->entityOpportunityModel = $this->generateModel();

        $this->generateEvaluationMethods();
        $this->generatePhases();
        $this->generateTerms();
        $this->generateMetadata();
        $this->generateRegistrationFieldsAndFiles($this->entityOpportunity, $this->entityOpportunityModel);
        $this->generateSealsRelations();

        $this->entityOpportunity = $this->entityOpportunity->refreshed();
        $this->entityOpportunityModel = $this->entityOpportunityModel->refreshed();
        $this->syncRegistrationTaxonomiesFromSourceOntoModel();
        $this->entityOpportunityModel->save(true);

        if($this->isAjax()){
            $this->json($this->entityOpportunity);
        }else{
            $app->redirect($app->request->getReferer());
        }
    }

    /**
     * Gera uma nova oportunidade a partir de um modelo
     * 
     * Este método cria uma nova oportunidade baseada em um modelo existente,
     * desabilitando temporariamente o controle de acesso para permitir
     * a criação completa da estrutura.
     * 
     * @api ALL generateopportunity
     * @return void Retorna JSON da nova oportunidade gerada
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @requiresAuthentication
     */
    function ALL_generateopportunity(){
        $app = App::i();

        $this->requireAuthentication();
        $this->entityOpportunity = $this->requestedEntity;

        if (!$this->entityOpportunity->getMetadata('isModelPublic')) {
            $this->entityOpportunity->checkPermission('@control');
        }

        if (isset($this->postData['objectType']) && isset($this->postData['ownerEntity'])) {
            $ownerEntity = $app->repo($this->postData['objectType'])->find($this->postData['ownerEntity']);
            $ownerEntity->checkPermission('@control');
        }

        $app->disableAccessControl();
        try {
            $this->entityOpportunityModel = $this->generateOpportunity();

            $this->generateEvaluationMethods();
            $this->generatePhases();
            $this->generateTerms();
            $this->generateMetadata(0, 0);
            $this->generateRegistrationFieldsAndFiles($this->entityOpportunity, $this->entityOpportunityModel);

            $this->entityOpportunity = $this->entityOpportunity->refreshed();
            $this->entityOpportunityModel = $this->entityOpportunityModel->refreshed();
            $this->syncRegistrationTaxonomiesFromSourceOntoModel();
            $this->entityOpportunityModel->save(true);
        } finally {
            $app->enableAccessControl();
        }

        $this->json($this->entityOpportunityModel); 
    }

    /**
     * Encontra todos os modelos de oportunidades disponíveis
     * 
     * Este método busca todas as oportunidades marcadas como modelos
     * e retorna informações sobre elas, incluindo número de fases,
     * descrição, tempo estimado e se são modelos oficiais.
     * 
     * @api GET findOpportunitiesModels
     * @return array Lista de modelos de oportunidades
     */
    function GET_findOpportunitiesModels()
    {
        $app = App::i();
        $conn = $app->em->getConnection();
        $verifiedSealIds = $app->config['app.verifiedSealsIds'] ?? [];
        $verifiedSealIds = is_array($verifiedSealIds) ? $verifiedSealIds : [$verifiedSealIds];
        $verifiedSealIds = array_values(array_filter($verifiedSealIds, fn($id) => is_numeric($id)));
        $modelIsOfficialSql = 'FALSE AS model_is_official';

        if (!empty($verifiedSealIds)) {
            $placeholders = implode(',', array_fill(0, count($verifiedSealIds), '?'));
            $modelIsOfficialSql = "EXISTS (
                SELECT 1
                FROM seal_relation sr
                WHERE sr.object_id = o.id
                  AND sr.object_type = 'MapasCulturais\\Entities\\Opportunity'
                  AND sr.seal_id IN ($placeholders)
                LIMIT 1
            ) AS model_is_official";
        }

        $rows = $conn->fetchAllAssociative("
            SELECT
                o.id,
                o.short_description,
                o.registration_from,
                o.registration_proponent_types,
                $modelIsOfficialSql,
                (
                    SELECT COUNT(*)
                    FROM opportunity child
                    LEFT JOIN opportunity_meta om_last
                        ON om_last.object_id = child.id AND om_last.key = 'isLastPhase'
                    WHERE child.parent_id = o.id
                      AND child.status != -10
                      AND (om_last.value IS NULL OR om_last.value != '1')
                ) AS numero_fases,
                (
                    SELECT lp.publish_timestamp
                    FROM opportunity lp
                    JOIN opportunity_meta om_lp
                        ON om_lp.object_id = lp.id AND om_lp.key = 'isLastPhase' AND om_lp.value = '1'
                    WHERE lp.parent_id = o.id
                    LIMIT 1
                ) AS last_phase_publish_timestamp
            FROM opportunity o
            JOIN opportunity_meta om_model
                ON om_model.object_id = o.id AND om_model.key = 'isModel' AND om_model.value = '1'
            WHERE o.status != -10
        ", $verifiedSealIds);

        $dataModels = [];
        foreach ($rows as $row) {
            $days = 'N/A';
            if ($row['registration_from'] && $row['last_phase_publish_timestamp']) {
                $regFrom = new \DateTime($row['registration_from']);
                $pubTs   = new \DateTime($row['last_phase_publish_timestamp']);
                $days    = $pubTs->diff($regFrom)->days . ' Dia(s)';
            }

            $tipoAgente = 'N/A';
            if ($row['registration_proponent_types']) {
                $decoded    = json_decode($row['registration_proponent_types'], true);
                $tipoAgente = is_array($decoded) ? implode(', ', $decoded) : $row['registration_proponent_types'];
                $tipoAgente = $tipoAgente ?: 'N/A';
            }

            $dataModels[] = [
                'id'             => (int) $row['id'],
                'numeroFases'    => (int) $row['numero_fases'],
                'descricao'      => $row['short_description'],
                'tempoEstimado'  => $days,
                'tipoAgente'     => $tipoAgente,
                'modelIsOfficial'=> (bool) $row['model_is_official'],
            ];
        }

        $this->json($dataModels);
    }

    /**
     * Define se um modelo é público ou não
     * 
     * Este método altera a visibilidade pública de um modelo de oportunidade.
     * 
     * @api POST modelpublic
     * @return bool Valor definido para isModelPublic
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @requiresAuthentication
     */
    function POST_modelpublic(){
        $app = App::i();

        $this->requireAuthentication();
        $this->entityOpportunity = $this->requestedEntity;

        $isModelPublic = $this->postData['isModelPublic'];
    
        $this->entityOpportunity->setMetadata('isModelPublic', $isModelPublic);
        $this->entityOpportunity->saveTerms();
        $this->entityOpportunity->save(true);
       
        $this->json($isModelPublic); 
    }

    /**
     * Gera um novo modelo clonando a oportunidade atual
     * 
     * @return \MapasCulturais\Entities\Opportunity Modelo gerado
     * @access private
     */
    /**
     * Replica categorias, tipos de proponente e faixas da oportunidade de referência (origem ou modelo) para a nova.
     * Necessário após o pipeline de cópia: saves/refreshes intermediários podem deixar a entidade destino sem esses JSON.
     */
    private function syncRegistrationTaxonomiesFromSourceOntoModel(): void
    {
        $this->entityOpportunityModel->registrationCategories = $this->entityOpportunity->registrationCategories;
        $this->entityOpportunityModel->registrationProponentTypes = $this->entityOpportunity->registrationProponentTypes;
        $this->entityOpportunityModel->registrationRanges = $this->entityOpportunity->registrationRanges;
    }

    private function generateModel()
    {
        $app = App::i();

        $postData = $this->postData;

        $name = $postData['name'];
        $description = $postData['description'];

        $this->entityOpportunityModel = clone $this->entityOpportunity;
        $this->resetClonedEvaluationMethodConfiguration($this->entityOpportunityModel);

        $this->entityOpportunityModel->name = $name;
        $this->entityOpportunityModel->status = -1;
        $this->entityOpportunityModel->shortDescription = $description;

        $now = new \DateTime('now');
        $this->entityOpportunityModel->createTimestamp = $now;

        $app->em->persist($this->entityOpportunityModel);
        $app->em->flush();

        // necessário adicionar as categorias, proponetes e ranges após salvar devido a trigger public.fn_propagate_opportunity_insert
        $this->entityOpportunityModel->registrationCategories = $this->entityOpportunity->registrationCategories;
        $this->entityOpportunityModel->registrationProponentTypes = $this->entityOpportunity->registrationProponentTypes;
        $this->entityOpportunityModel->registrationRanges = $this->entityOpportunity->registrationRanges;
        $this->entityOpportunityModel->save(true);

        return $this->entityOpportunityModel;

        
    }

    /**
     * Gera uma nova oportunidade a partir do modelo atual
     * 
     * @return \MapasCulturais\Entities\Opportunity Oportunidade gerada
     * @access private
     */
    private function generateOpportunity()
    {
        $app = App::i();
        $postData = $this->postData;

        $name = $postData['name'];
        
        $this->entityOpportunityModel = clone $this->entityOpportunity;
        $this->resetClonedEvaluationMethodConfiguration($this->entityOpportunityModel);
        $this->entityOpportunityModel->name = $name;
        $this->entityOpportunityModel->status = Entity::STATUS_DRAFT;
        $this->entityOpportunityModel->owner = $app->user->profile;

        $now = new \DateTime('now');
        $this->entityOpportunityModel->createTimestamp = $now;

        $app->em->persist($this->entityOpportunityModel);
        $app->em->flush();

        // necessário adicionar as categorias, proponetes e ranges após salvar devido a trigger public.fn_propagate_opportunity_insert
        $this->entityOpportunityModel->registrationCategories = $this->entityOpportunity->registrationCategories;
        $this->entityOpportunityModel->registrationProponentTypes = $this->entityOpportunity->registrationProponentTypes;
        $this->entityOpportunityModel->registrationRanges = $this->entityOpportunity->registrationRanges;
        
        $this->changeObjectType($this->entityOpportunityModel->id);
        
        $this->entityOpportunityModel->save(true);

        return $this->entityOpportunityModel;
    }

    /**
     * Altera o tipo de objeto da oportunidade no banco de dados
     * 
     * @param int $id ID da oportunidade
     * @return void
     * @access private
     */
    private function changeObjectType($id)
    {
        $app = App::i();
        $postData = $this->postData;

        if (isset($postData['objectType']) && isset($postData['ownerEntity'])) {
            if ($this->cachedOwnerEntity === null) {
                $this->cachedOwnerEntity = $app->repo($postData['objectType'])->find($postData['ownerEntity']);
            }
            $ownerEntity = $this->cachedOwnerEntity;
            $app->em->beginTransaction();
            $app->em->getConnection()->update('opportunity', [
                    'object_type' => $ownerEntity->getClassName(),
                    'object_id' => $ownerEntity->id
                ], ['id' => $id]);
            $app->em->commit();
        }
    }

    /**
     * Duplica os métodos de avaliação da oportunidade original para o modelo
     * 
     * @return void
     * @access private
     */
    private function generateEvaluationMethods() : void
    {
        $app = App::i();

        // duplica o método de avaliação para a oportunidade primária
        $evaluationMethodConfigurations = $app->repo('EvaluationMethodConfiguration')->findBy([
            'opportunity' => $this->entityOpportunity
        ]);
        foreach ($evaluationMethodConfigurations as $evaluationMethodConfiguration) {
            $newMethodConfiguration = clone $evaluationMethodConfiguration;
            $newMethodConfiguration->setOpportunity($this->entityOpportunityModel);
            $newMethodConfiguration->save(true);

            // duplica os metadados das configurações do modelo de avaliação
            foreach ($evaluationMethodConfiguration->getMetadata() as $metadataKey => $metadataValue) {
                $newMethodConfiguration->setMetadata($metadataKey, $metadataValue);
            }
            $this->saveWithSingleFlush($newMethodConfiguration);
        }
    }

    /**
     * Duplica as fases da oportunidade original para o modelo
     * 
     * @return void
     * @access private
     */
    private function generatePhases() : void
    {
        $app = App::i();
        $postData = $this->postData;

        $phases = $app->repo('Opportunity')->findBy([
            'parent' => $this->entityOpportunity
        ]);

        $publishDate = null;
        $publishSubsite = null;
        $newLastPhase = null;

        foreach ($phases as $phase) {
            if ($phase->getMetadata('isLastPhase')) {
                $publishDate = $phase->publishTimestamp;
                $publishSubsite = $phase->subsite;
                continue;
            }

            $newPhase = clone $phase;
            $this->resetClonedEvaluationMethodConfiguration($newPhase);
            $newPhase->setParent($this->entityOpportunityModel);
            $newPhase->owner = $app->user->profile;

            foreach ($phase->getMetadata() as $metadataKey => $metadataValue) {
                if (!is_null($metadataValue) && $metadataValue != '') {
                    $newPhase->setMetadata($metadataKey, $metadataValue);
                }
            }

            $now = new \DateTime('now');
            $newPhase->createTimestamp = $now;
            $newPhase->subsite = $phase->subsite;

            $this->saveWithSingleFlush($newPhase);

            $this->generateRegistrationFieldsAndFiles($phase, $newPhase);

            $this->changeObjectType($newPhase->id);

            $evaluationMethodConfigurations = $app->repo('EvaluationMethodConfiguration')->findBy([
                'opportunity' => $phase
            ]);

            foreach ($evaluationMethodConfigurations as $evaluationMethodConfiguration) {
                $newMethodConfiguration = clone $evaluationMethodConfiguration;
                $newMethodConfiguration->setOpportunity($newPhase);
                $newMethodConfiguration->save(true);

                foreach ($evaluationMethodConfiguration->getMetadata() as $metadataKey => $metadataValue) {
                    $newMethodConfiguration->setMetadata($metadataKey, $metadataValue);
                }
                $this->saveWithSingleFlush($newMethodConfiguration);
            }
        }

        if ($publishDate !== null) {
            $newPhases = $app->repo('Opportunity')->findBy([
                'parent' => $this->entityOpportunityModel
            ]);

            foreach ($newPhases as $newPhase) {
                if ($newPhase->getMetadata('isLastPhase')) {
                    $newPhase->setPublishTimestamp($publishDate);
                    $newPhase->subsite = $publishSubsite;
                    $newPhase->save(true);
                    $this->changeObjectType($newPhase->id);
                    break;
                }
            }
        }
    }

    private function saveWithSingleFlush(Entity $entity): void
    {
        $entity->save(false);
        App::i()->em->flush();
    }

    private function resetClonedEvaluationMethodConfiguration(Opportunity $opportunity): void
    {
        $opportunity->evaluationMethodConfiguration = null;

        if (is_object($opportunity->__magicGetterCache ?? null)) {
            unset($opportunity->__magicGetterCache->evaluationMethodConfiguration);
        }
    }


    /**
     * Duplica os metadados da oportunidade original para o modelo
     * 
     * @param int $isModel Define se é um modelo (padrão: 1)
     * @param int $isModelPublic Define se o modelo é público (padrão: 0)
     * @return void
     * @access private
     */
    private function generateMetadata($isModel = 1, $isModelPublic = 0) : void
    {
        $app = App::i();
        $em = $app->em;
        $conn = $em->getConnection();

        $sql = "
            SELECT 
                om.*
            FROM
                opportunity_meta om
            WHERE om.object_id = {$this->entityOpportunity->id}
        ";
        $stmt = $conn->query($sql);

        while (($row = $stmt->fetchAssociative()) !== false) {
            $this->entityOpportunityModel->setMetadata($row['key'], $row['value']);
        }

        $this->entityOpportunityModel->setMetadata('isModel', $isModel);
        $this->entityOpportunityModel->setMetadata('isModelPublic', $isModelPublic);

        $this->entityOpportunityModel->saveTerms();
    }

    /**
     * Duplica as configurações de campos e arquivos de inscrição
     * 
     * @param \MapasCulturais\Entities\Opportunity $opportunityCurrent Oportunidade original
     * @param \MapasCulturais\Entities\Opportunity $opportunityNew Oportunidade nova/modelo
     * @return void
     * @access private
     */
    private function generateRegistrationFieldsAndFiles($opportunityCurrent, $opportunityNew) : void
    {
        $stepMap = [];
        $fieldNameMap = [];
        $conditionalFields = [];
        $conditionalFiles = [];

        $reusableQueue = $this->findReusableRegistrationStepsWithoutFieldsOrFiles($opportunityNew);

        $existingSteps = [];
        foreach ($opportunityNew->registrationSteps as $newStep) {
            if ((int) $newStep->opportunity->id !== (int) $opportunityNew->id) {
                continue;
            }
            $existingSteps[$newStep->id] = $newStep;
        }

        $app = App::i();

        $oldSteps = $opportunityCurrent->registrationSteps->toArray();
        usort($oldSteps, function ($a, $b) {
            $c = $a->displayOrder <=> $b->displayOrder;

            return $c !== 0 ? $c : $a->id <=> $b->id;
        });

        foreach ($oldSteps as $oldStep) {
            if ($reusableQueue !== []) {
                $target = array_shift($reusableQueue);
                $this->copyRegistrationStepPropertiesFromTo($oldStep, $target);
                $stepMap[$oldStep->id] = $target;

                continue;
            }

            $stepMap[$oldStep->id] = $existingSteps[$oldStep->id] ?? (function () use ($oldStep, $opportunityNew) {
                $newStep = clone $oldStep;
                $newStep->setOpportunity($opportunityNew);
                $newStep->save(false);

                return $newStep;
            })();
        }

        foreach ($opportunityCurrent->getRegistrationFieldConfigurations() as $registrationFieldConfiguration) {
            $originalFieldName = $registrationFieldConfiguration->fieldName;
            $fieldConfiguration = clone $registrationFieldConfiguration;
            $fieldConfiguration->setOwnerId($opportunityNew->id);

            if ($registrationFieldConfiguration->step && isset($stepMap[$registrationFieldConfiguration->step->id])) {
                $fieldConfiguration->setStep($stepMap[$registrationFieldConfiguration->step->id]);
            }

            $fieldConfiguration->save(false);
            $fieldNameMap[$originalFieldName] = $fieldConfiguration->fieldName;

            if ($fieldConfiguration->conditionalField) {
                $conditionalFields[] = $fieldConfiguration;
            }
        }

        foreach ($opportunityCurrent->getRegistrationFileConfigurations() as $registrationFileConfiguration) {
            $fileConfiguration = clone $registrationFileConfiguration;
            $fileConfiguration->setOwnerId($opportunityNew->id);

            if ($registrationFileConfiguration->step && isset($stepMap[$registrationFileConfiguration->step->id])) {
                $fileConfiguration->setStep($stepMap[$registrationFileConfiguration->step->id]);
            }

            $fileConfiguration->save(false);

            if ($fileConfiguration->conditionalField) {
                $conditionalFiles[] = $fileConfiguration;
            }
        }

        $app->em->flush();

        foreach (array_merge($conditionalFields, $conditionalFiles) as $configuration) {
            if (isset($fieldNameMap[$configuration->conditionalField])) {
                $configuration->conditionalField = $fieldNameMap[$configuration->conditionalField];
                $configuration->save(false);
            }
        }

        if (!empty($conditionalFields) || !empty($conditionalFiles)) {
            $app->em->flush();
        }

        $this->deleteOrphanEmptyRegistrationSteps($opportunityNew, $stepMap);
    }

    /**
     * Etapas da oportunidade sem campos nem anexos (ex.: criadas pelo hook insert:after), em ordem estável.
     *
     * @return RegistrationStep[]
     */
    private function findReusableRegistrationStepsWithoutFieldsOrFiles(Opportunity $opportunity): array
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

    private function copyRegistrationStepPropertiesFromTo(RegistrationStep $from, RegistrationStep $to): void
    {
        $to->name = $from->name;
        $to->displayOrder = $from->displayOrder;
        $meta = $from->metadata;
        $to->metadata = is_object($meta)
            ? json_decode(json_encode($meta), false) ?: new \stdClass()
            : new \stdClass();
        $to->save(false);
    }

    /**
     * Remove etapas vazias que não foram usadas no mapa (ex.: sobra de múltiplos placeholders).
     *
     * @param array<int, RegistrationStep> $stepMap
     */
    private function deleteOrphanEmptyRegistrationSteps(Opportunity $opportunity, array $stepMap): void
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

    /**
     * Duplica as relações com selos da oportunidade original para o modelo
     * 
     * @return void
     * @access private
     */
    private function generateSealsRelations() : void
    {
        foreach ($this->entityOpportunity->getSealRelations() as $sealRelation) {
            $this->entityOpportunityModel->createSealRelation($sealRelation->seal, true, true);
        }
    }

    /**
     * Duplica os termos (áreas e tags) da oportunidade original para o modelo
     * 
     * @return void
     * @access private
     */
    private function generateTerms() : void
    {
        $original_terms = $this->entityOpportunity->getTerms();
        $terms_to_set = [];
        
        if (isset($original_terms['area']) && !empty($original_terms['area'])) {
            $area_terms = is_array($original_terms['area']) ? $original_terms['area'] : (array) $original_terms['area'];
            $terms_to_set['area'] = $area_terms;
            
        }
        
        if (isset($original_terms['tag']) && !empty($original_terms['tag'])) {
            $tag_terms = is_array($original_terms['tag']) ? $original_terms['tag'] : (array) $original_terms['tag'];
            $terms_to_set['tag'] = $tag_terms;
        }
        
        if (!empty($terms_to_set)) {
            $this->entityOpportunityModel->setTerms($terms_to_set);
        }
    }
}
