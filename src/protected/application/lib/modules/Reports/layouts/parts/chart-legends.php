<?php
$result = [];

foreach ($legends as $key_l => $legend) {
    $result[$legend] = $colors[$key_l];
} ?>

<div class="legends-charts">

    <?php foreach ($result as $key => $value) : ?>
        <div class="each">
            <span class="dot" style="background-color:<?= $value ?>"></span><p><?= $key ?></p>
        </div>
    <?php endforeach; ?>

</div>

