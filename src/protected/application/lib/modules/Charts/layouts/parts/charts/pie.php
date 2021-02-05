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
 * 
 */



$title = $title ?? null;
$chart_id = uniqid('chart-pie-');
if (isset($serie) && is_array($serie)) {    
    $data = array_map(function($item) { return $item['data']; }, $serie);
    $labels = array_map(function($item) { return $item['label']; }, $serie);
    if(isset($serie[0]['colors'])) {
        $colors = array_map(function($item) { return $item['colors']; }, $serie);
    }
}

$width = $width ?? '50vw';
$height = $height ?? '50vw';
$legends = $legends ?? null;
?>
<div class="chart-container chart-pie" style="position: relative; height:<?=$height?>; width:<?=$width?>;">
<!-- <div class="chart-container chart-pie"> -->
    <header>
        <?php if($title): ?>
            <div class="title">
                <h2><?= $title ?></h2>
            </div>
        <?php endif; ?>
        <!-- <button class="btn btn-default download"><?php //i::_e("Baixar em CSV"); ?></button> -->
    </header>
    
    <canvas id="<?= $chart_id ?>"></canvas>
    <?php $this->part('chart-legends', ["legends" => $legends, "colors" => $colors]); ?>
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
                legend: false,
                layout: {
                    padding: {
                        right: 0,
                        top: 35,
                        bottom: 25
                    }
                },      
                plugins: {
            datalabels: {
                display: 'auto',
               formatter: (value, ctx) => {
                  let sum = 0;
                  let dataArr = ctx.chart.data.datasets[0].data;
                  dataArr.map(data => {
                      sum += data;
                  });
                  let percentage = (value*100 / sum).toFixed(2)+"%";
                  return value + "\n"+"("+percentage+")";
                },
                anchor:"end",
                align: "end",
                color: '#000',

            }
        },
        
                
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