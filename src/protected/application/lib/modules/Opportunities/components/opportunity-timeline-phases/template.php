<?php
use MapasCulturais\i;
$this->import('timeline');
?>

<div class="col-12 controller-opportunity__phases">
    <div>
        <timeline
                :timeline-items="[{
                                from: new Date(2017, 5, 2),
                                title: 'Inscrições',
                                description: 'de 05/03/2022 a 21/03/2022 às 12:00',
                                showDayAndMonth: true
                                }]"
                :message-when-no-items="'NO'"
                :unique-year="true"
                order="asc">
        </timeline>
    </div>
</div>