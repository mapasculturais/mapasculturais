<?php
namespace Tests\Interfaces;

use DateTime;
use MapasCulturais\Entities\Opportunity;

interface DataCollectionPeriodInterface {
    public function getRegistrationFrom(?Opportunity $reference_phase = null): \DateTime;
    public function getRegistrationTo(?Opportunity $reference_phase = null): \DateTime;
}