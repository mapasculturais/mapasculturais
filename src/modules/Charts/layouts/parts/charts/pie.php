<?php

/**
 * 
$this->part('charts/pie', [
    'serie' => [
        ['label' => 'Preto', 'data' => 66, 'colors' => 'black'],
        ['label' => 'Branco', 'data' => 120, 'colors' => 'white'],
        ['label' => 'Amarelo', 'data' => 70, 'colors' => 'yellow']
    ]
]);

$this->part('charts/pie', [
    'labels' => ['Preto', 'Branco', 'Amarelo'],
    'data' => [66, 120, 70],
    'colors' => ['black', 'white', 'yellow']
]);

 */

use MapasCulturais\i;

$title = $title ?? null;
$chart_id = uniqid('chart-pie-');
if (isset($serie) && is_array($serie)) {
    $data = array_map(function ($item) {
        return $item['data'];
    }, $serie);
    $labels = array_map(function ($item) {
        return $item['label'];
    }, $serie);
    if (isset($serie[0]['colors'])) {
        $colors = array_map(function ($item) {
            return $item['colors'];
        }, $serie);
    }
    $total = array_sum($data);
}

$width = $width ?? '50vw';
$height = $height ?? '50vw';
$legends = $legends ?? null;
$right = $right ?? 0;
$top = $top ?? 0;
$bottom = $bottom ?? 0;
$total = $total ?? 0;

$route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity_id' => $opportunity->id, 'action' => $action]);

?>

<div class="chart-wrap">

    <header>
        <?php if ($title) : ?>
            <h3><?= $title ?></h3>
        <?php endif; ?>
        <a href="<?= $route ?>" name="<?= $chart_id ?>" class="hltip download" title="<?php i::_e("Baixar em CSV"); ?>"></a>
    </header>
    <div class="chart-container chart-pie" style="position: relative; height:<?= $height ?>; width:<?= $width ?>;">
        <canvas id="<?= $chart_id ?>"></canvas>
    </div>
    <footer>
        <?php $this->part('chart-legends', ["legends" => $legends, "colors" => $colors]); ?>
    </footer>

</div><!-- /.chart-wrap -->

<script>
    $(window).on('load', function() {
        var dataset = {
            data: <?= json_encode($data) ?>,
            label: '<?= $title ?>',
            backgroundColor: <?= json_encode($colors) ?>,
            borderWidth: 0,
            total: <?= json_encode($total) ?>
        };

        var config = {
            type: 'pie',
            data: {
                datasets: [dataset],
                labels: <?= json_encode($labels) ?>,
            },
            options: {
                responsive: true,
                legend: false,
                layout: {
                    padding: {
                        right: <?= $right ?>,
                        top: <?= $top ?>,
                        bottom: <?= $bottom ?>
                    },
                },
                plugins: {
                    datalabels: {
                        display: false
                    }
                },
                tooltips: {
                    callbacks: {
                        title: function(tooltipItem, data) {
                            return data['labels'][tooltipItem[0]['index']];
                        },
                        label: function(tooltipItem, data) {
                            var dataset = data['datasets'][0];
                            if (data['datasets'][0]['total'] > 0) {
                                var percent = Number(Math.round((dataset['data'][tooltipItem['index']] / data['datasets'][0]['total']) * 100 + 'e2') + 'e-2');
                                return ' ' + data['datasets'][0]['data'][tooltipItem['index']] + ' (' + percent + '%)';
                            } else {
                                return ' ' + data['datasets'][0]['data'][tooltipItem['index']];
                            }
                        }
                    }
                }
            }
        };

        if (dataset.backgroundColor.length == 0) {
            for (var i in dataset.data) {
                var color = MapasCulturais.getChartColors();
                dataset.backgroundColor.push(color[0]);
            }
        }

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);
    });
</script>