<?php
$controller = $this->controller;

$days = $controller->getDays('agent');

$types = $controller->extractDistinctGroups($daily_data, '_type');

$series = [];

// foreach($types as $type) {
//     $series[] = [
//         'label' => $type,
//         'data' => $controller->extractDailyData('agent', $daily_data, '_type', $type)
//     ];
// }
$series[] = [
    'label' => 'Total',
    'data' => $controller->extractDailyData('agent', $daily_data, 'total'),
    'pointRadius'=> '0'
];
// var_dump($series);

$this->part('charts/line', [
    // 'horizontal' => true,
    'labels' => $days,
    'series' => $series,
    'width' => '1600px',
]);

return;
?>

<section class="main-section">
    <h1><?= $entity_class::getEntityTypeLabel() ?></h1>

    <?php foreach ($data as $i => $serie) :
            if ($i == 0) continue;

            $this->controller->sortDataByNum($serie->data);
            $data = $this->controller->extractData($serie->data);
            $labels = $this->controller->extractGroupLabels($serie->data, $entity_class, $serie->field); ?>
            <?php $this->part('charts/pie', ['data' => $data, 'labels' => $labels]) ?>
    <?php endforeach; ?>
</section>

<?php 
$this->part('charts/line', [
    // 'horizontal' => true,
    'labels' => ['Draft', 'Pending', 'Publish'],
    'series' => [
        ['label' => '#800', 'data' => [66,88,22], 'color' => '#800', 'type' => 'line', 'fill' => false],
        ['label' => '#a80', 'data' => [22,120, 191], 'color' => '#a80'],
        ['label' => '#f80', 'data' => [70, 128, 11], 'color' => '#f80']
    ]
]);
?>
