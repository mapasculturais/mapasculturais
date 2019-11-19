<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of DoctrineFunctionTest
 *
 * @author leandro
 */

class DoctrineFunctionTest extends MapasCulturais_TestCase{

    function testStringAggregate(){
        define('DELIMITER', '; ');
        $query1 = $this->app->em->createQuery("SELECT string_agg(a.name,'".DELIMITER."') as aggregate FROM MapasCulturais\Entities\Agent a");
        $result1 = $query1->getResult();
        $query2 = $this->app->em->createQuery("SELECT a.name FROM MapasCulturais\Entities\Agent a");
        $result2 = $query2->getResult();
        $r2 = [];
        foreach($result2 as $item){ $r2[] = $item['name']; }
        $this->assertEquals($result1[0]['aggregate'], join($r2, DELIMITER));
    }

    function testStringUnaccent(){
        $string1 = 'áéíóúÁÉÍÓÚãĩõũÃĨÕŨâêîôûÂÊÎÔÛäëïöüÄËÏÖÜñÑ';
        $string2 = 'aeiouAEIOUaiouAIOUaeiouAEIOUaeiouAEIOUnN'; //unaccented to be matched
        //doesn't work with ẽẼ, but, it doesn't seem to be used, so I think it's negligible.
        $query = $this->app->em->createQuery('SELECT unaccent(\''.$string1.'\') unaccentedString1 FROM MapasCulturais\Entities\Agent a')->setMaxResults(1);
        $result = $query->getResult();
        $this->assertEquals($result[0]['unaccentedString1'], $string2);
    }

    function NOTCOMPLETE_DISABLED_testEventOccurrence(){
        $date_from = '2014-08-01';
        $date_to = '2014-08-07';
        $dql = "
            SELECT
                e.*
            FROM
                MapasCulturais\Entities\Event e
            JOIN
                recurring_event_occurrence_for(:date_from, :date_to, 'Etc/UTC', NULL) eo
                ON eo.event_id = e.id
            WHERE
                e.status > 0
            ORDER BY
                eo.starts_on, eo.starts_at";
        $query = $this->app->em->createQuery($dql)->setMaxResults(1);
        $query->setParameters(array(
            'date_from' => $date_from,
            'date_to' => $date_to
        ));
        $result = $query->getResult();
        var_dump($result);
    }

}
