<?php
$columns_counter = $status_order = 0;
$columns = [];

$sum = count($cfg["registration"]->columns) + count($cfg["committee"]->columns);

$get_pending_registration_value = function(array $registration, string $column) {
    if ($column === 'owner') {
        return $registration['owner']['name'] ?? '--';
    }

    return $registration[$column] ?? '--';
};

$get_pending_committee_value = function(array $valuer, string $column) {
    if ($column === 'evaluator') {
        return $valuer['name'] ?? '--';
    }

    return $valuer[$column] ?? '--';
};
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
            <?php foreach($cfg as $section):
                foreach($section->columns as $column):
                    if ("Status" == $column->label) { $status_order = $columns_counter; } ?>
                    <th bgcolor="<?php echo $section->color ?>"> <?php echo $column->label; ?> </th>
                <?php
                    $columns_counter++;
                endforeach;
            endforeach;
            $total = $columns_counter - $sum;
            ?>
        </tr>
    </thead>
    <tbody>
        <?php

        $label_result = '';
        $array = [];

        foreach ($evaluations as $evaluation) :

            $chave = $evaluation->registration->id . ':' . $evaluation->user->id;
            if ($array[$chave] ?? false) {
                continue;
            }
            $array[$chave] = true;

        ?>
        <tr>
            <?php foreach ($cfg as $section) :

                if (isset($section->columns['result'])) {
                    $label_result = $section->columns['result']->label;
                }

            ?>
                <?php foreach ($section->columns as $column) : $getter = $column->getValue; ?>
                    <td style="text-align: center;">
                        <?php

                        if ($label_result == $column->label) {
                            if (empty($getter($evaluation))) {
                                echo 'Pendente';
                            } else {
                                echo $getter($evaluation);
                            }
                        } else {
                            echo $getter($evaluation);
                        }

                        ?>
                    </td>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tr>
        <?php endforeach;

        foreach ($pending_evaluations as $valuer_pending) :

            $chaveValuer = $valuer_pending["registration"]["id"] . ':' . $valuer_pending['valuer']['user'];
            if ($array[$chaveValuer] ?? false) {
                continue;
            }


            if (is_array($valuer_pending) && is_null($valuer_pending["evaluation"])) {
                echo '<tr>';
                foreach ($cfg["registration"]->columns as $key => $column) { ?>
                    <td style="text-align: center;"> <?php echo $get_pending_registration_value($valuer_pending["registration"], $key); ?> </td>
                <?php }

                foreach ($cfg["committee"]->columns as $key => $column) { ?>
                    <td style="text-align: center;"> <?php echo $get_pending_committee_value($valuer_pending["valuer"], $key); ?> </td>
                <?php }

                for($i=0; $i < $total; $i++) { ?>
                    <td style="text-align: center; font-style: italic;">
                        <?php
                        if ($i + $sum === $status_order) {
                            echo "Não avaliado";
                        } else {
                            echo "--";
                        } ?>
                    </td>
                <?php }
                echo '</tr>';
            }            
        endforeach;
        ?>
    </tbody>
</table>