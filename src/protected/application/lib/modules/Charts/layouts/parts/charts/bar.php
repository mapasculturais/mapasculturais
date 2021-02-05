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
$title = $title ?? null;
$chart_id = uniqid('chart-bar-');
$colors = [];
$datasets = [];

if (isset($series) && is_array($series)) {
    $datasets = array_map(function($dataset) {
        $type = $dataset['type'] ?? 'bar';
        $color_key = 'backgroundColor';
        if($type == 'line') {
            $color_key = 'borderColor';
            $dataset['fill'] = $dataset['fill'] ?? false;
            $dataset['borderWidth'] = $dataset['borderWidth'] ?? 2;
        }
        if(isset($dataset['color'])) {
            $dataset[$color_key] = $dataset['color'];
            unset($dataset['color']);
        }
        return $dataset;
    }, $series);
}

$width = $width ?? '50vw';
$height = $height ?? '50vw';

$horizontal = $horizontal ?? false;
?>
<div class="chart-container chart-bar" style="position: relative; height:<?=$height?>; width:<?=$width?>;">
    <header>
        <?php if($title): ?>
            <div class="title">
                <h2><?= $title ?></h2>
            </div>
        <?php endif; ?>
        <!-- <button class="btn btn-default download"><?php //i::_e("Baixar em CSV"); ?></button> -->
    </header>
    
    <?php $this->part('chart-legends', ["legends" => $legends, "colors" => $colors]); ?>

    <canvas id="<?= $chart_id ?>"></canvas>
</div>

<script>
    $(window).on('load', function() {
        var config = {
            type: '<?= $horizontal ? 'horizontalBar' : 'bar' ?>',
            data: {
                datasets: <?= json_encode($datasets) ?>,
                labels: <?= json_encode($labels) ?>,
            },
            options: {
                responsive: true,
                legend: false
            }
        };
        console.log(config);

        
        config.data.datasets.forEach(function(dataset) {
            dataset.backgroundColor = dataset.backgroundColor || MapasCulturais.Charts.dynamicColors();
        });

        console.log(config);

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);
    });
</script>