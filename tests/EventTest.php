<?php
require_once __DIR__.'/bootstrap.php';

use MapasCulturais\Entities\Event;
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

    protected $eventName;

    function createEvent() {
        $this->eventName = 'Test Event ' . uniqid();
        $event = new Event;
        $event->name = $this->eventName;
        $event->shortDescription = 'Test Event Description';
        $event->ownerId = $this->app->user->profile->id;
        $event->save(true);
        return $event;
    }

    function createSpace() {
        $space = new Space;
        $space->name = 'Test Space';
        $space->shortDescription = 'Test 1Space Description';
        $space->type = 10;
        $space->ownerId = $this->app->user->profile->id;
        $space->save(true);
        return $space;
    }

    function testEvent(){
        $this->user = 1;

        $event = $this->createEvent();

        $event2 = $this->app->repo('Event')->findOneBy(array('name' => $this->eventName));

        $this->assertEquals($event, $event2);

        $events = $this->app->repo('Event')->findBy(array('name' => $this->eventName));

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
        $this->user = 1;

        $event = $this->createEvent();
        $space = $this->createSpace();

        $occurrence = new EventOccurrence;
        $occurrence->rule = $this->getEventRule();
        $occurrence->event = $event;
        $occurrence->space = $space;

        $date1 = '1950-09-30';
        $date2 = '1950-10-01';

        $occurrence->frequency = 'once';
        $occurrence->startsOn = new DateTime($date1);
        $occurrence->startsAt = new DateTime($date1 . ' 12:00:00');
        $occurrence->endsAt = new DateTime($date1 . ' 15:00:00');

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        $occurrence->save(true);

        $query = $this->createQuery($date1, $date2);
        $occurrence2 = $query->getOneOrNullResult();

        $this->assertEquals($date1, $occurrence2->startsOn->format('Y-m-d'));

        $query = $this->createQuery('1950-10-02', '1950-10-05');
        $occurrence3 = $query->getOneOrNullResult();

        $this->assertEmpty($occurrence3);
    }

    function testEventOccurrence_daily(){
        $this->user = 1;

        $event = $this->createEvent();
        $space = $this->createSpace();

        $occurrence = new EventOccurrence;
        $occurrence->rule = $this->getEventRule();
        $occurrence->event = $event;
        $occurrence->space = $space;

        $date1 = '1950-01-02';
        $date2 = '1950-01-09';

        $occurrence->frequency = 'daily';
        $occurrence->startsOn = new DateTime($date1);
        $occurrence->until = new DateTime($date2);

        $occurrence->startsAt = new DateTime($date1 . ' 12:00:00');
        $occurrence->endsAt = new DateTime($date1 . ' 15:00:00');

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
        $this->user = 1;

        $event = $this->createEvent();
        $space = $this->createSpace();

        $occurrence = new EventOccurrence;
        $occurrence->rule = $this->getEventRule();
        $occurrence->event = $event;
        $occurrence->space = $space;

        $date1 = '1950-01-02';
        $date2 = '1950-11-01';

        $occurrence->frequency = 'weekly';
        $occurrence->startsOn = new DateTime($date1);
        $occurrence->until = new DateTime($date2);

        $occurrence->startsAt = new DateTime($date1 . ' 12:00:00');
        $occurrence->endsAt = new DateTime($date1 . ' 15:00:00');

        $recurrence = new EventOccurrenceRecurrence;
        $recurrence->eventOccurrence = $occurrence;
        $recurrence->day = 1;

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        $occurrence->save(true);
        $recurrence->save(true);

        $date = '1950-01-2';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));

        $date = '1950-01-9';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));

        $date = '1950-01-16';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(1, count($occurrences));

        $date = '1950-01-3';
        $query = $this->createQuery($date, $date);
        $occurrences = $query->getArrayResult();
        $this->assertEquals(0, count($occurrences));

        $occurrences = $this->app->repo('EventOccurrence')->findOneBy(array('frequency' => 'weekly'));
        $this->assertNotNull($occurrences);
    }

    function testEventRules(){
        $this->user = 1;

        $event = $this->createEvent();
        $space = $this->createSpace();

        $occurrence = new EventOccurrence;
        $rule = '';
        $occurrence->event = $event;
        $occurrence->space = $space;

        $occurrence->rule = $this->getEventRule();

        $this->assertEmpty($occurrence->validationErrors, print_r($occurrence->validationErrors, true));
        $occurrence->save(true);

        $this->assertEquals('weekly', $occurrence->frequency);

        $this->assertEquals(2, count($occurrence->recurrences));
    }

    function getEventRule(){
        $rule = '{"spaceId":"31","startsAt":"12:31","duration":"00h01","frequency":"weekly","startsOn":"2014-02-10","until":"2014-02-19","day":{"1":"on","3":"on"},"monthly":"week","description":"test description"}';
        return json_decode($rule);
    }
}
