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
                    $delivery->typeDelivery = $d['typeDelivery'] ?? null;
                    $delivery->segmentDelivery = $d['segmentDelivery'] ?? null;
                    $delivery->expectedNumberPeople = $d['expectedNumberPeople'] ?? null;
                    $delivery->generaterRevenue = $d['generaterRevenue'] ?? null;
                    $delivery->renevueQtd = $d['renevueQtd'] ?? null;
                    $delivery->unitValueForecast = $d['unitValueForecast'] ?? null;
                    $delivery->totalValueForecast = $d['totalValueForecast'] ?? null;
                    
                    // Novos campos de planejamento
                    $delivery->artChainLink = $d['artChainLink'] ?? null;
                    $delivery->totalBudget = $d['totalBudget'] ?? null;
                    $delivery->numberOfCities = $d['numberOfCities'] ?? null;
                    $delivery->numberOfNeighborhoods = $d['numberOfNeighborhoods'] ?? null;
                    $delivery->mediationActions = $d['mediationActions'] ?? null;
                    $delivery->paidStaffByRole = $d['paidStaffByRole'] ?? null;
                    $delivery->teamCompositionGender = $d['teamCompositionGender'] ?? null;
                    $delivery->teamCompositionRace = $d['teamCompositionRace'] ?? null;
                    $delivery->revenueType = $d['revenueType'] ?? null;
                    $delivery->commercialUnits = $d['commercialUnits'] ?? null;
                    $delivery->unitPrice = $d['unitPrice'] ?? null;
                    $delivery->hasCommunityCoauthors = $d['hasCommunityCoauthors'] ?? null;
                    $delivery->hasTransInclusionStrategy = $d['hasTransInclusionStrategy'] ?? null;
                    $delivery->transInclusionActions = $d['transInclusionActions'] ?? null;
                    $delivery->hasAccessibilityPlan = $d['hasAccessibilityPlan'] ?? null;
                    $delivery->expectedAccessibilityMeasures = $d['expectedAccessibilityMeasures'] ?? null;
                    $delivery->hasEnvironmentalPractices = $d['hasEnvironmentalPractices'] ?? null;
                    $delivery->environmentalPracticesDescription = $d['environmentalPracticesDescription'] ?? null;
                    $delivery->hasPressStrategy = $d['hasPressStrategy'] ?? null;
                    $delivery->communicationChannels = $d['communicationChannels'] ?? null;
                    $delivery->hasInnovationAction = $d['hasInnovationAction'] ?? null;
                    $delivery->innovationTypes = $d['innovationTypes'] ?? null;
                    $delivery->documentationTypes = $d['documentationTypes'] ?? null;
                    
                    $delivery->goal = $goal;
                    $delivery->save(true);
                }  
            }      
        } 

        $workplan = $app->repo(Workplan::class)->find($workplan->id);

        return $workplan;        
    }
}