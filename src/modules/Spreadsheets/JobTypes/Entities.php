<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\User;
use Spreadsheets\SpreadsheetJob;

class Entities extends SpreadsheetJob
{
    protected function _getFileGroup() : string {
        return $this->slug;
    }

    protected function _getTargetEntities() : array {
        return [User::class];
    }

    protected function _getHeader(Job $job) : array {
        $header = [];

        $entity_class_name = $job->entityClassName;
        
        $query = $job->query;
        $properties = explode(',', $query['@select']);

        foreach($properties as $property) {
            $header[$property] = $entity_class_name::getPropertyLabel($property);
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