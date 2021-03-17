<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<?php foreach($data as $report_type => $d): ?>
    <?php $this->part('reports/entity-' . $report_type, ['data' => $d, 'entity_class' => $entity_class]) ?>
<?php endforeach; ?>