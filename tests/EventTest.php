<?php
require_once __DIR__.'/bootstrap.php';

use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\EventAttendance;
use MapasCulturais\Entities\EventOccurrence;
use MapasCulturais\Entities\EventOccurrenceRecurrence;
use MapasCulturais\Entities\Space;

// Calendário de 1950 para ajudar os testes
// Janeiro
//     Se  Te  Qu  Qu  Se  Sá  Do
//                             1
//     2   3   4   5   6   7   8
//     9   10  11  12  13  14  15
//     16  17  18  19  20  21  22
//     23  24  25  26  27  28  29
//     30  31
// Fevereiro
//     Se  Te  Qu  Qu  Se  Sá  Do
//             1   2   3   4   5
//     6   7   8   9   10  11  12
//     13  14  15  16  17  18  19
//     20  21  22  23  24  25  26
//     27  28
// Março
//     Se  Te  Qu  Qu  Se  Sá  Do
//             1   2   3   4   5
//     6   7   8   9   10  11  12
//     13  14  15  16  17  18  19
//     20  21  22  23  24  25  26
//     27  28  29  30  31
// Abril
//     Se  Te  Qu  Qu  Se  Sá  Do
//                         1   2
//     3   4   5   6   7   8   9
//     10  11  12  13  14  15  16
//     17  18  19  20  21  22  23
//     24  25  26  27  28  29  30

class EventTest extends MapasCulturais_TestCase{

    function __construct($name = null, $data = [], $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->factory->autoPersist = true;
    }

    function testEvent(){
        $this->user = 1;

        $event = $this->factory->createEvent();

        $event2 = $this->app->repo('Event')->findOneBy(array('name' => $event->name));

        $this->assertEquals($event, $event2);

        $events = $this->app->repo('Event')->findBy(array('name' => $event->name));

        $this->assertEquals(1,count($events));
    }

    function createQuery($date1, $date2) {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addEntityResult('MapasCulturais\Entities\EventOccurrence','e');

        $rsm->addFieldResult('e', 'id', 'id');

        $strNativeQuery = "SELECT * FROM recurring_event_occurrence_for('$date1', '$date2', 'Etc/UTC', NULL)";

        return $this->app->em->createNativeQuery($strNativeQuery, $rsm);
    }

    function testEventOccurrence_single(){
        $this->resetTransactions();
        $this->user = 1;

        $event = $this->factory->createEvent();
        $space = $this->factory->createSpace();

        $occurrence = $this->factory->createSingleEventOccurrence('1950-09-30 12:00', 120, $space, $event);
        $occurrence->save(true);

        $query = $this->createQuery('1950-09-30', '1950-10-01');
        $occurrence2 = $query->getOneOrNullResult();

        $this->assertEquals('1950-09-30', $occurrence2->startsOn->format('Y-m-d'));

        $query = $this->createQuery('1950-10-02', '1950-10-05');
        $occurrence3 = $query->getOneOrNullResult();

        $this->assertEmpty($occurrence3);
    }

    function testEventOccurrence_daily(){
        $this->resetTransactions();
        $this->user = 1;

        $event = $this->factory->createEvent();
        $space = $this->factory->createSpace();

        $occurrence = $this->factory->createDailyEventOccurrence('1950-01-02 12:00', 120, '1950-01-09', $space, $event);

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        $occurrence->save(true);

        for ($i=2; $i <= 9 ; $i++) {
            $date = '1950-01-0' . $i;
            $query = $this->createQuery($date, $date);
            $occurrences = $query->getArrayResult();
            $this->assertEquals(1, count($occurrences));
        }

        $date = '1950-01-10';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(0, count($occurrences));

        $date = '1950-01-01';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(0, count($occurrences));
    }

    function testEventOccurrence_weekly(){
        $this->resetTransactions();
        $this->user = 1;

        $event = $this->factory->createEvent();
        $space = $this->factory->createSpace();

        $occurrence = $this->factory->createWeeklyEventOccurrence('1950-01-02 12:00', 180, '1950-11-01', [1,3], $space, $event);

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        
        $date = '1950-01-02';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));

        $date = '1950-01-09';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));
        
        $date = '1950-01-16';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));

        $date = '1950-01-03';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(0, count($occurrences));

        $occurrences = $this->app->repo('EventOccurrence')->findOneBy(array('frequency' => 'weekly'));
        $this->assertNotNull($occurrences);

    }

    function testEventRules(){
        $this->resetTransactions();
        $this->user = 1;

        $event = $this->factory->createEvent();
        $space = $this->factory->createSpace();

        $occurrence = new EventOccurrence;
        
        $occurrence->event = $event;
        $occurrence->space = $space;

        $occurrence->rule = $this->getEventRule();

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        $occurrence->save(true);

        $this->assertEquals('weekly', $occurrence->frequency);

        $this->assertEquals(2, count($occurrence->recurrences));
    }

    function getEventRule(){
        return (object) [
            "spaceId"   => "31",
            "startsAt"  => "12:31",
            "duration"  => "00h01",
            "frequency" => "weekly",
            "startsOn"  => "2014-02-10",
            "until"     => "2014-02-19",
            "day"       => (object) [ "1" => "on", "3" => "on" ],
            "monthly"   => "week",
            "description" => "test description"
        ];
    }

    function testEventAttendance(){
        $this->resetTransactions();

        $this->user = $this->getUser('normal', 0);

        $space = $this->factory->createSpace();
        $event = $this->factory->createEvent();

        $event_occurrence_1 = $this->factory->createDailyEventOccurrence('1950-01-01 12:00', 120, '1950-02-22', $space, $event);

        $event_occurrence_2 = $this->factory->createWeeklyEventOccurrence('1950-01-10 18:00', 30, '1950-01-31', [1,3,5], $space, $event);
        
        $this->user = $this->getUser('normal', 1);

        $event_controller = $this->app->controller('event');

        $occurrences = $event_controller->apiFindOccurrences (['@from' => '1950-01-05', '@to' => '1950-01-31', '@limit'=>3]);

        $oc1 = (object) $occurrences[1];

        $attendance = new EventAttendance;

        $this->assertCount(2, $attendance->validationErrors, 'assert that empty event attendance returns validation errors');

        $attendance->setReccurrenceString('invalid');
        $this->assertArrayHasKey('reccurrenceString', $attendance->validationErrors, 'assert that invalid recurrenceString returns validation error');

        $attendance->setReccurrenceString($oc1->_reccurrence_string);
        $this->assertArrayNotHasKey('reccurrenceString', $attendance->validationErrors, 'assert that a valid recurrenceString don\'t return validation error');

        $attendance->type = 'invalid';
        $this->assertArrayHasKey('type', $attendance->validationErrors, 'assert that invalid type return validation error');

        $attendance->type = EventAttendance::TYPE_CONFIRMATION;
        $this->assertArrayNotHasKey('type', $attendance->validationErrors, 'assert that a valid type don\'t return validation error');

        $this->assertCount(0, $attendance->validationErrors, 'assert that a valid event attendance dont return validation errors');

        $attendance->save(true);

        $controller = $this->app->controller('eventAttendance');

        $attendances = $controller->apiQuery([
            '@select' => '*',
            'reccurrenceString' => "EQ({$oc1->_reccurrence_string})"
        ]);

        $this->assertCount(1, $attendances);

        $this->assertEquals($oc1->_reccurrence_string, $attendances[0]['reccurrenceString']);

    }
}
