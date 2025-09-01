<?php

namespace Tests\Directors;

use DateTime;
use Doctrine\DBAL\Exception;
use MapasCulturais\App;
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
    public function createDraftRegistrations(Opportunity $opportunity, int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null, array $data = [], bool $fill_requered_properties = true, bool $save = true, bool $flush = true): array
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

            $registration = $this->registrationBuilder->getInstance();
            $this->setRegistrationData($registration, $data);

            $registrations[] = $registration;
        }

        return $registrations;
    }

    public function createSentRegistrations(Opportunity $opportunity, int $number_of_registrations = 10, ?string $category = null, ?string $proponent_type = null, ?string $range = null, array $data = []): array
    {
        $registrations = [];
        for ($i = 0; $i < $number_of_registrations; $i++) {
            $registration = $this->registrationBuilder
                ->reset($opportunity)
                ->setCategory($category)
                ->setProponentType($proponent_type)
                ->setRange($range)
                ->fillRequiredProperties()
                ->save()
                ->send()
                ->getInstance();
            
            $this->setRegistrationData($registration, $data);
            $registrations[] = $registration;
        }

        return $registrations;
    }

    public function createSentRegistration(Opportunity $opportunity, array $data): Registration
    {
        $registration = $this->registrationBuilder
                ->reset($opportunity)
                ->fillRequiredProperties()
                ->getInstance();

        $this->setRegistrationData($registration, $data);

        return $registration->refreshed();
    }

    protected function setRegistrationData(Registration $registration, array $data): void
    {
        foreach($data as $key => $value) {
            if(in_array($key, ['sentTimestamp', 'createTimestamp', 'updateTimestamp']) && is_string($value)) {
                $value = new DateTime($value);
            }
            $registration->$key = $value;
        }
        
        $registration = $this->registrationBuilder
                ->save()
                ->send()
                ->getInstance();

        $field_to_column = [
            'score' => 'score', 
            'consolidatedResult' => 'consolidated_result'
        ];

        $app = App::i();
        foreach($field_to_column as $field => $column) {
            if(isset($data[$field])) {
                $value = $data[$field];
                $app->conn->executeQuery("UPDATE registration SET $column = :val WHERE id = :id", [
                    'id' => $registration->id, 
                    'val' => $value
                ]);
            }
        }
    }
}
