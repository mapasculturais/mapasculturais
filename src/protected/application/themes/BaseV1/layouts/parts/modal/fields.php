<?php
$__known_types = ['name', 'shortDescription', 'type'];
if (in_array($field, $__known_types)) {
    $title = $this->entityRequiredFields()[$field];

    $this->part("modal/title", ['title' => $title]);

    switch ($field) {
        case "name":
            $className = mb_strtolower($entity->getEntityTypeLabel());
            $placeholder = $this->modalFieldPlaceholder($title,$className);
            echo "<input type='text' name='$field' placeholder='$placeholder' required>";
            break;
        case "shortDescription":
            $this->part("modal/short-description");
            break;
        case "type":
            $_types = $app->getRegisteredEntityTypes($entity);
            if (!is_null($_types) && is_array($_types)) {
                $this->part("modal/entity-type", ['entity' => $entity, 'modal_id' => $modal_id, 'types' => $_types]);
            }
            break;
    }
}