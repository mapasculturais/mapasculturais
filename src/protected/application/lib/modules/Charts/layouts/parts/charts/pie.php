<?php 
/**
 * 
  $this->part('charts/pie', [
       'serie' => [
           ['label' => 'Preto', 'data' => 66, 'color' => 'black'],
           ['label' => 'Branco', 'data' => 120, 'color' => 'white'],
           ['label' => 'Amarelo', 'data' => 70, 'color' => 'yellow']
       ]
  ]);
  
  $this->part('charts/pie', [
       'labels' => ['Preto', 'Branco', 'Amarelo'],
       'data' => [66, 120, 70],
       'color' => ['black', 'white', 'yellow']
  ]);
 * 
 */
$title = $title ?? null;
$chart_id = uniqid('chart-pie-');
$colors = [];
if (isset($serie) && is_array($serie)) {
    $data = array_map(function($item) { return $item['data']; }, $serie);
    $labels = array_map(function($item) { return $item['label']; }, $serie);
    if(isset($serie[0]['color'])) {
        $colors = array_map(function($item) { return $item['color']; }, $serie);
    }
}

$width = $width ?? '50vw';
$height = $height ?? '50vw';
?>
<div class="chart-container chart-pie" style="position: relative; height:<?=$height?>; width:<?=$width?>;">
    <?php if($title): ?>
        <h2><?= $title ?></h2>
    <?php endif; ?>
    <canvas id="<?= $chart_id ?>"></canvas>
</div>

<script>
    $(window).on('load', function() {
        var dataset = {
            data: <?= json_encode($data) ?>,
            label: '<?= $title ?>',
            backgroundColor: <?= json_encode($colors) ?>
        };

        var config = {
            type: 'pie',
            data: {
                datasets: [dataset],
                labels: <?= json_encode($labels) ?>,
            },
            options: {
                responsive: true,
            }
        };

        if (dataset.backgroundColor.length == 0) {
            for (var i in dataset.data) {
                dataset.backgroundColor.push(MapasCulturais.Charts.dynamicColors());
            }
        }

        var ctx = document.getElementById("<?= $chart_id ?>").getContext('2d');
        MapasCulturais.Charts.charts["<?= $chart_id ?>"] = new Chart(ctx, config);
    });
</script>