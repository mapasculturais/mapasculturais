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
$title = $title ?? null;
$chart_id = uniqid('chart-line-');
$colors = [];
$datasets = [];

if (isset($series) && is_array($series)) {
    $datasets = array_map(function($dataset) {
        $color_key = 'borderColor';
        $dataset['fill'] = $dataset['fill'] ?? false;
        $dataset['borderWidth'] = $dataset['borderWidth'] ?? 2;
        if(isset($dataset['color'])) {
            $dataset[$color_key] = $dataset['color'];
            unset($dataset['color']);
        }
        return $dataset;
    }, $series);
}

$width = $width ?? '50vw';
$height = $height ?? '50vw';

?>
<div class="chart-container chart-line" style="position: relative; height:<?= $height ?>; width:<?= $width ?>;">
    <header>
        <?php if ($title) : ?>
            <h3><?= $title ?></h3>
        <?php endif; ?>
        <button class="btn btn-default download"><?php i::_e("Baixar em CSV"); ?></button>
    </header>
    <canvas id="<?= $chart_id ?>"></canvas>
</div>

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
            }
        };
        
        config.data.datasets.forEach(function(dataset) {
            dataset.backgroundColor = dataset.backgroundColor || MapasCulturais.Charts.dynamicColors();
        });

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        ctx.canvas.width = 1000;
		ctx.canvas.height = 300;
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);
    });
</script>