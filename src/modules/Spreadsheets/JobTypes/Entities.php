<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Subsite;
use MapasCulturais\Entities\User;
use MapasCulturais\i;
use Spreadsheets\SpreadsheetJob;

class Entities extends SpreadsheetJob
{
    protected function _getFileGroup() : string {
        return $this->slug;
    }

    protected function _getTargetEntities() : array {
        return [
            Agent::class,
            Event::class,
            Opportunity::class,
            Project::class,
            Space::class,
            Seal::class,
            Subsite::class,
            User::class,
        ];
    }

    protected function _getHeader(Job $job) : array {
        $header = [];

        $entity_class_name = $job->entityClassName;
        
        $query = $job->query;
        $properties = explode(',', $query['@select']);

        foreach($properties as $property) {
            if($property == 'terms') {
                $header['area'] = i::__('Área de interesse');
                $header['tag'] = i::__('Tags');
                continue;
            }

            if($property == 'files.avatar') {
                continue;
            }

            $header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
        }
        
        return $header;
    }

    protected function _getBatch(Job $job) : array {
        $entity_class_name = $job->entityClassName;

        $query = $job->query;
        $query['@limit'] = $this->limit;
        $query['@page'] = $this->page;

        $query = new ApiQuery($entity_class_name, $query);
        $result = $query->getFindResult();

        foreach($result as &$entity) {
            $terms = $entity['terms'] ?? null;

            $entity['type'] = $entity['type']->name;
            $entity['area'] = isset($terms['area']) ? implode(', ', $terms['area']) : null;
            $entity['tag'] = isset($terms['tag']) ? implode(', ', $terms['tag']) : null;
            $sealNames = array_map(function($seal) {
                return $seal['name'];
            }, $entity['seals']);
            $entity['seals'] = implode(', ', $sealNames);

            unset($entity['terms']);
            unset($entity['@entityType']);
        }
        
        return $result;
    }

    protected function _getFilename(Job $job) : string {
        $entity_class_name = $job->entityClassName;
        $label = $entity_class_name::getEntityTypeLabel(true);
        $extension = $job->extension;
        $date = date('Y-m-d H:i:s');

        $result = "{$label}-{$date}.{$extension}";

        return $result;
    }

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $md5 = md5(json_encode([
            $data,
            $start_string,
            $interval_string,
            $iterations
        ]));

        return "entitiesSpreadsheet:{$md5}";
    }

}