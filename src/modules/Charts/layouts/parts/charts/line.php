<?php

use MapasCulturais\i;

/**
 * 
    $this->part('charts/line', [
        'vertical',
        'labels' => ['Draft', 'Pending', 'Publish'],
        'series' => [
           ['label' => 'Preto', 'data' => [66,120,70], 'color' => 'black'],
           ['label' => 'Branco', 'data' => [22,44,66], 'color' => 'white'],
           ['label' => 'Amarelo', 'data' => [77,55,34], 'color' => 'yellow']
        ]
    ]);
 * 
 */
$title    = $title ?? null;
$chart_id = uniqid('chart-line-');
$datasets = [];
$print    = $print ?? false;

if (isset($series) && is_array($series)) {
    $datasets = array_map(function ($dataset) {

        $dataset['fill'] = $dataset['fill'] ?? false;
        $dataset['pointBorderWidth'] = $dataset['pointBorderWidth'] ?? '0';
        $dataset['radius'] = $dataset['radius'] ?? '4';
        $dataset['hoverRadius'] = $dataset['hoverRadius'] ?? $dataset['radius'] + 1;

        if (isset($dataset['colors'])) {
            $dataset['borderColor'] = $dataset['borderColor'] ?? $dataset['colors'];
            $dataset['pointBackgroundColor'] = $dataset['pointBackgroundColor'] ?? $dataset['colors'];
            unset($dataset['colors']);
        }
        return $dataset;
    }, $series);
}

/**
 * Calcula a largura em porcentagem para o gráfico, baseado na quantidade de dados
 */
$count_data = function ( $data ) use ( $print ) {

    if ( $print ) {
        return 100;
    }

    if ( count( $data ) < 30 || (count( $data ) * 2) < 100) {
        return 100;
    } else {
        return count( $data ) * 2;
    }
};

$width = $width ?? '50vw';
$height = $height ?? '50vw';

$route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity_id' => $opportunity->id, 'action' => $action]);

?>

<div class="chart-wrap type-line">

    <header>
        <?php if ($title) : ?>
            <h3><?= $title ?></h3>
        <?php endif; ?>
        <a href="<?= $route ?>" name="<?= $chart_id ?>" class="btn btn-default hltip download" title="<?php i::_e("Baixar em CSV"); ?>"><?php i::_e("Baixar em CSV"); ?></a>
    </header>

    <div class="chart-scroll">
        <div class="chart-container chart-line" style="position: relative; height:<?= $height ?>; width:<?= $count_data($series[0]['data']) ?>%;">
            <canvas id="<?= $chart_id ?>"></canvas>
        </div>
    </div>

    <footer>
        <?php $this->part('chart-legends', ["legends" => $legends, "colors" => $colors, 'opportunity' => $opportunity]); ?>
    </footer>

</div><!-- /.chart-wrap -->

<script>
    $(window).on('load', function() {
        var config = {
            type: 'line',
            data: {
                datasets: <?= json_encode($datasets) ?>,
                labels: <?= json_encode($labels) ?>,
            },
            options: {
                responsive: true,
                legend: false,
                plugins: {
                    datalabels: {
                        display: false,

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
                tooltips: {

                    // Desabilita o tooltip padrão
                    enabled: false,

                    // Adiciona o tooltip personalizado
                    custom: function(tooltipModel) {

                        // Tooltip wrap
                        var tooltipWrap = document.getElementById('chartjs-tooltip');

                        // Cria o tooltip na primeira renderização
                        if (!tooltipWrap) {
                            tooltipWrap = document.createElement('div');
                            tooltipWrap.id = 'chartjs-tooltip';
                            tooltipWrap.innerHTML = '<section></section><div class="point-tooltip"></div>';
                            document.body.appendChild(tooltipWrap);
                        }

                        // Exibe o tooltip apenas no hover
                        if (tooltipModel.opacity === 0) {
                            tooltipWrap.style.opacity = 0;
                            return;
                        }

                        // Retorna os itens do tooltip
                        function getBody(bodyItem) {
                            return bodyItem.lines;
                        }

                        // Define o conteúdo do tooltip
                        if (tooltipModel.body) {

                            var bodyLines = tooltipModel.body.map(getBody);

                            innerHtml = '<div class="custom-tooltip">';
                            bodyLines.forEach(function(body, i) {
                                innerHtml += '<span><b>' + body + '</b></span>';
                            });
                            innerHtml += '</div>';

                            var tooltipContent = tooltipWrap.querySelector('section');
                            tooltipContent.innerHTML = innerHtml;

                        }

                        // Section do tooltip
                        tooltipContent.style.backgroundColor = 'rgba(17,17,17,0.8)';
                        tooltipContent.style.padding = '15px';

                        // Seta inferior do tooltip
                        var pointTooltip = tooltipWrap.querySelector('.point-tooltip');
                        pointTooltip.style.width = 0;
                        pointTooltip.style.height = 0;
                        pointTooltip.style.borderLeft = '10px solid transparent';
                        pointTooltip.style.borderRight = '10px solid transparent';
                        pointTooltip.style.borderTop = '10px solid rgba(17,17,17,0.8)';
                        pointTooltip.style.margin = '0 auto';

                        // Posição do tooltip
                        var position = this._chart.canvas.getBoundingClientRect();

                        // Posicionamento e demais personalizações do tooltip
                        tooltipWrap.style.opacity = 1;
                        tooltipWrap.style.position = 'absolute';
                        tooltipWrap.style.left = ((position.left + window.pageXOffset + tooltipModel.caretX) - tooltipWrap.offsetWidth / 2) + 'px';
                        tooltipWrap.style.top = ((position.top + window.pageYOffset + tooltipModel.caretY) - tooltipWrap.offsetHeight - 25) + 'px';
                        tooltipWrap.style.fontSize = '14px';
                        tooltipWrap.style.color = '#ffffff';
                        tooltipWrap.style.pointerEvents = 'none';

                    }
                }
            }
        };

        config.data.datasets.forEach(function(dataset) {
            dataset.backgroundColor = dataset.backgroundColor || MapasCulturais.getChartColors();
        });

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        ctx.canvas.width = 1000;
        ctx.canvas.height = 300;
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);
    });
</script>