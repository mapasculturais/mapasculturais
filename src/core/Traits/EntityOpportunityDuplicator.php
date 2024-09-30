<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entity;
use Exception;

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
   
    private function duplicateRegistrationFieldsAndFiles() : void
    {
        foreach ($this->entityOpportunity->getRegistrationFieldConfigurations() as $registrationFieldConfiguration) {
            $fieldConfiguration = clone $registrationFieldConfiguration;
            $fieldConfiguration->setOwnerId($this->entityNewOpportunity->id);
            $fieldConfiguration->save(true);
        }

        foreach ($this->entityOpportunity->getRegistrationFileConfigurations() as $registrationFileConfiguration) {
            $fileConfiguration = clone $registrationFileConfiguration;
            $fileConfiguration->setOwnerId($this->entityNewOpportunity->id);
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

        $src = PUBLIC_PATH . 'files/opportunity/' . $this->entityOpportunity->id;
        $dst = PUBLIC_PATH . 'files/opportunity/' . $this->entityNewOpportunity->id;
        $this->copyDir($src, $dst);
        
        $conn = $app->em->getConnection();
        $files = $conn->fetchAll("SELECT * FROM file WHERE object_id = {$this->entityOpportunity->id} ORDER BY id ASC");
        foreach ($files as $file) {
            if (is_null($file['parent_id'])) {
                $parentId = null;
            } else if (isset($futureParentId) && !is_null($file['parent_id'])) {
                $parentId = $futureParentId;
            } else {
                throw new Exception('File parent_id is null or not exists');
            }

            $sql = 'INSERT INTO file (md5, mime_type, name, object_type, object_id, create_timestamp, grp, description, parent_id, path) VALUES (:md5, :mime_type, :name, :object_type, :object_id, :create_timestamp, :grp, :description, :parent_id, :path)';
            $stmt = $conn->prepare($sql);
            $stmt->bindValue('md5', $file['md5']);
            $stmt->bindValue('mime_type', $file['mime_type']);
            $stmt->bindValue('name', $file['name']);
            $stmt->bindValue('object_type', $file['object_type']);
            $stmt->bindValue('object_id', $this->entityNewOpportunity->id);
            $stmt->bindValue('create_timestamp', $file['create_timestamp']);
            $stmt->bindValue('grp', $file['grp']);
            $stmt->bindValue('description', $file['description']);
            $stmt->bindValue('parent_id', $parentId);

            $path = str_replace('opportunity/'.$this->entityOpportunity->id, 'opportunity/'.$this->entityNewOpportunity->id, $file['path']);
            $path = str_replace('file/'.$file['parent_id'], 'file/'.$parentId, $path);

            $diretorioAtual = $dst . '/file/' . $file['parent_id'];
            $novoDiretorio = $dst . '/file/' . $parentId;
            
            if (is_dir($diretorioAtual)) {
                if (!is_dir($novoDiretorio)) {
                    if (rename($diretorioAtual, $novoDiretorio)) {
                    }
                } 
            }

            $stmt->bindValue('path', $path);
            $stmt->execute();

            if (is_null($file['parent_id'])) {
                $futureParentId = $conn->lastInsertId();
            }
        }
    }

    private function copyDir($src, $dst) {
        if (!file_exists($src)) {
            return false;
        }
        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    
        return true;
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
