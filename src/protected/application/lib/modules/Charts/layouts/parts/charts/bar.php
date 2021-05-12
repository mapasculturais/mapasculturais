<?php

/**
 * 
    $this->part('charts/bar', [
        'labels' => ['Draft', 'Pending', 'Publish'],
        'series' => [
            ['label' => 'Preto', 'data' => [66,120,70], 'color' => 'black'],
            ['label' => 'Branco', 'data' => [22,44,66], 'color' => 'white'],
            ['label' => 'Amarelo', 'data' => [77,55,34], 'color' => 'yellow']
        ]
    ]);


    $this->part('charts/bar', [
        'horizontal' => true,
        'labels' => ['Draft', 'Pending', 'Publish'],
        'series' => [
            ['label' => 'Preto', 'data' => [66,120,70], 'color' => 'black'],
            ['label' => 'Branco', 'data' => [22,44,66], 'color' => 'white'],
            ['label' => 'Amarelo', 'data' => [77,55,34], 'color' => 'yellow']
        ]
    ]);

    // uma série exibida como linha
    $this->part('charts/bar', [
        // 'horizontal' => true,
        'labels' => ['Draft', 'Pending', 'Publish'],
        'series' => [
            ['label' => '#800', 'data' => [66,88,22], 'color' => '#800', 'type' => 'line', 'fill' => false],
            ['label' => '#a80', 'data' => [22,120, 191], 'color' => '#a80'],
            ['label' => '#f80', 'data' => [70, 128, 11], 'color' => '#f80']
        ]
    ]);
 * 
 */

use MapasCulturais\i;

$title = $title ?? null;
$chart_id = uniqid('chart-bar-');
$colors = [];
$datasets = [];

if (isset($series) && is_array($series)) {
    $datasets = array_map(function ($dataset) {
        $type = $dataset['type'] ?? 'bar';
        $color_key = 'backgroundColor';
        if ($type == 'line') {
            $color_key = 'borderColor';
            $dataset['fill'] = $dataset['fill'] ?? false;
            $dataset['borderWidth'] = $dataset['borderWidth'] ?? 2;
        }
        if (isset($dataset['color'])) {
            $dataset[$color_key] = $dataset['color'];
            unset($dataset['color']);
        }
        return $dataset;
    }, $series);
}

/**
 * Calcula a largura em porcentagem para o gráfico, baseado na quantidade de dados
 */
$count_data = function ( $data ) {
    if ( count( $data ) < 30 || (count( $data ) * 2) < 100) {
        return 100;
    } else {
        return count( $data ) * 2;
    }
};


$width = $width ?? '100%';
$height = $height ?? '50vw';

$horizontal = $horizontal ?? false;

$route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity_id' => $opportunity->id, 'action' => $action]);

?>

<div class="chart-wrap">

    <header>
        <?php if ($title) : ?>
            <h3><?= $title ?></h3>
        <?php endif; ?>
        <a href="<?= $route ?>" name="<?= $chart_id ?>" class="hltip download" title="<?php i::_e("Baixar em CSV"); ?>"></a>
    </header>
    <div class="chart-scroll">
        <div class="chart-container chart-bar" style="position: relative; height:<?= $height ?>; width:<?= $count_data($series[0]['data']) ?>%;">
            <canvas id="<?= $chart_id ?>"></canvas>
        </div>
    </div>

</div><!-- /.chart-wrap -->

<script>
    $(window).on('load', function() {

        Chart.elements.Rectangle.prototype.draw = function() {
            var ctx = this._chart.ctx;
            var vm = this._view;
            var left, right, top, bottom, signX, signY, borderSkipped, radius;
            var borderWidth = vm.borderWidth;

            // Set Radius Here
            var cornerRadius = 5;

            if (!vm.horizontal) {
                // bar
                left = vm.x - vm.width / 2;
                right = vm.x + vm.width / 2;
                top = vm.y;
                bottom = vm.base;
                signX = 1;
                signY = bottom > top ? 1 : -1;
                borderSkipped = vm.borderSkipped || 'bottom';
            } else {
                // horizontal bar
                left = vm.base;
                right = vm.x;
                top = vm.y - vm.height / 2;
                bottom = vm.y + vm.height / 2;
                signX = right > left ? 1 : -1;
                signY = 1;
                borderSkipped = vm.borderSkipped || 'left';
            }

            ctx.beginPath();
            ctx.fillStyle = vm.backgroundColor;
            ctx.lineWidth = borderWidth;

            var corners = [
                [left, bottom],
                [left, top],
                [right, top],
                [right, bottom]
            ];

            var borders = ['bottom', 'left', 'top', 'right'];
            var startCorner = borders.indexOf(borderSkipped, 0);
            if (startCorner === -1) {
                startCorner = 0;
            }

            function cornerAt(index) {
                return corners[(startCorner + index) % 4];
            }

            var corner = cornerAt(0);
            ctx.moveTo(corner[0], corner[1]);

            for (var i = 1; i < 4; i++) {
                corner = cornerAt(i);
                nextCornerId = i + 1;
                if (nextCornerId == 4) {
                    nextCornerId = 0
                }

                nextCorner = cornerAt(nextCornerId);

                width = corners[2][0] - corners[1][0];
                height = corners[0][1] - corners[1][1];
                x = corners[1][0];
                y = corners[1][1];

                var radius = cornerRadius;

                // Fix radius being too large
                if (radius > height / 2) {
                    radius = height / 2;
                }
                if (radius > width / 2) {
                    radius = width / 2;
                }

                var lastVisible = 0;
                for (var findLast = 0, findLastTo = this._chart.data.datasets.length; findLast < findLastTo; findLast++) {
                    if (!this._chart.getDatasetMeta(findLast).hidden) {
                        lastVisible = findLast;
                    }
                }
                var rounded = this._datasetIndex === lastVisible;

                if (rounded) {
                    ctx.moveTo(x + radius, y);
                    ctx.lineTo(x + width - radius, y);
                    ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                    ctx.lineTo(x + width, y + height);
                    ctx.lineTo(x, y + height);
                    ctx.lineTo(x, y + radius);
                    ctx.quadraticCurveTo(x, y, x + radius, y);
                } else {
                    ctx.moveTo(x, y);
                    ctx.lineTo(x + width, y);
                    ctx.lineTo(x + width, y + height);
                    ctx.lineTo(x, y + height);
                    ctx.lineTo(x, y);
                }
            }

            ctx.fill();
        };

        var config = {
            type: '<?= $horizontal ? 'horizontalBar' : 'bar' ?>',
            data: {
                datasets: <?= json_encode($datasets) ?>,
                labels: <?= json_encode($labels) ?>,
            },
            options: {
                responsive: true,
                legend: false,
                plugins: {
                    datalabels: {
                        display: false
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            borderDash: [5, 5],
                        }
                    }]
                },
            }
        };

        config.data.datasets.forEach(function(dataset) {
            dataset.backgroundColor = dataset.backgroundColor || MapasCulturais.getChartColors();
        });

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);

    });
</script>