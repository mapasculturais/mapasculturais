<?php

namespace OpportunityWorkplan\Services;

use MapasCulturais\App;
use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Workplan;

class WorkplanService 
{
    public function save($registration, $workplan, $data)
    {
        $app = App::i();

        if (!$workplan) {
            $workplan = new Workplan();
        }
        $dataWorkplan = $data['workplan'];

        if (array_key_exists('projectDuration', $dataWorkplan)) {
            $workplan->projectDuration = $dataWorkplan['projectDuration'];
        }

        if (array_key_exists('culturalArtisticSegment', $dataWorkplan)) {
            $workplan->culturalArtisticSegment = $dataWorkplan['culturalArtisticSegment'];
        }
    
        $workplan->registration = $registration;
        $workplan->save(true);

        if (array_key_exists('goals', $dataWorkplan)) {
            foreach ($dataWorkplan['goals'] as $g) {
                if (!empty($g['id'])) {
                    $goal = $app->repo(Goal::class)->find($g['id']);
                } else {
                    $goal = new Goal();
                }

                $goal->monthInitial = $g['monthInitial'] ?? null;
                $goal->monthEnd = $g['monthEnd'] ?? null;
                $goal->title = $g['title'] ?? null;
                $goal->description = $g['description'] ?? null;
                $goal->culturalMakingStage = $g['culturalMakingStage'] ?? null;
                $goal->amount = $g['amount'] ?? null;
                $goal->workplan = $workplan;
                $goal->save(true);


                foreach ($g['deliveries'] as $d) {
                    if (!empty($d['id']) > 0) {
                        $delivery = $app->repo(Delivery::class)->find($d['id']);
                    } else {
                        $delivery = new Delivery();
                    }
    
                    $delivery->name = $d['name'] ?? null;
                    $delivery->description = $d['description'] ?? null;
                    $delivery->type = $d['type'] ?? null;
                    $delivery->segmentDelivery = $d['segmentDelivery'] ?? null;
                    $delivery->budgetAction = $d['budgetAction'] ?? null;
                    $delivery->expectedNumberPeople = $d['expectedNumberPeople'] ?? null;
                    $delivery->generaterRevenue = $d['generaterRevenue'] ?? null;
                    $delivery->renevueQtd = $d['renevueQtd'] ?? null;
                    $delivery->unitValueForecast = $d['unitValueForecast'] ?? null;
                    $delivery->totalValueForecast = $d['totalValueForecast'] ?? null;
                    $delivery->goal = $goal;
                    $delivery->save(true);
                }  
            }      
        } 

        $workplan = $app->repo(Workplan::class)->find($workplan->id);

        return $workplan;        
    }
}