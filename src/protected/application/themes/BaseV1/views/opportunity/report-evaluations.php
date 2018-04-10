<?php
use MapasCulturais\i;


$cfg = $entity->getEvaluationMethod()->getReportConfiguration($entity);

$columns = [];
?>
<table width="100%">
    <thead>
        <tr>
            <?php foreach($cfg as $section): ?>
            <th colspan="<?php echo count($section->columns) ?>" bgcolor="<?php echo $section->color ?>">
                <h3><?php echo $section->label ?></h3>
            </th>
            <?php endforeach; ?>
        </tr>
        <tr>
            <?php foreach($cfg as $section): ?>
                <?php foreach($section->columns as $column): ?>
                    <th bgcolor="<?php echo $section->color ?>">
                        <?php echo $column->label ?>
                    </th>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($evaluations as $evaluation): ?>
            <tr>
            <?php foreach($cfg as $section): ?>
                <?php foreach($section->columns as $column): $getter = $column->getValue; ?>
                    <td style="text-align: center;"><?php echo $getter($evaluation) ?></td>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>