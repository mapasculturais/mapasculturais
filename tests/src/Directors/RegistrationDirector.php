<?php

namespace Tests\Directors;

use Doctrine\DBAL\Exception;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\Director;
use Tests\Traits\RegistrationBuilder;

class RegistrationDirector extends Director
{
    use RegistrationBuilder;

    /**
     * @param Opportunity $opportunity 
     * @param int $number_of_registrations 
     * @param null|string $category 
     * @param null|string $proponent_type 
     * @param null|string $range 
     * @param bool $fill_requered_properties 
     * @param bool $save 
     * @param bool $flush 
     * @return Registration[]
     * @throws Exception 
     */
    public function createDraftRegistrations(Opportunity $opportunity, int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true): array
    {
        $registrations = [];
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $this->registrationBuilder
                ->reset($opportunity)
                ->setCategory($category)
                ->setProponentType($proponent_type)
                ->setRange($range);

            if ($fill_requered_properties) {
                $this->registrationBuilder->fillRequiredProperties();
            }

            if($save) {
                $this->registrationBuilder->save($flush);
            }

            $registrations[] = $this->registrationBuilder->getInstance();
        }

        return $registrations;
    }

    public function createSentRegistrations(Opportunity $opportunity, int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null): array
    {
        $registrations = [];
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $this->registrationBuilder
                ->reset($opportunity)
                ->setCategory($category)
                ->setProponentType($proponent_type)
                ->setRange($range)
                ->fillRequiredProperties()
                ->save()
                ->send();
            

            $registrations[] = $this->registrationBuilder->getInstance();
        }

        return $registrations;
    }

}
