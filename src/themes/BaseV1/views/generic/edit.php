<a href="<?php echo $this->controller->createUrl('list') ?>"><?php \MapasCulturais\i::_e("list");?></a>

<table id="user" class="table table-bordered table-striped" style="clear: both">
    <?php foreach ($fields as $field): ?>
        <tr>
            <td><?php echo $field['fieldLabel']?></td>
            <td>
                <?php if($field['fieldName'] == 'id'): ?>
                    <input type="hidden" name="id" value="<?php echo $object->id?>"/>
                    <?php echo $object->id; ?>
                <?php elseif($field['type'] == 'string' OR $field['type'] == 'integer' OR $field['type'] == 'smallint'): ?>
                    <input type="text" name="<?php echo $field['fieldName']?>" value="<?php echo $object->$field['fieldName']?>"/>
                <?php elseif($field['type'] == 'text'): ?>
                    <textarea name="<?php echo $field['fieldName']?>"><?php echo $object->$field['fieldName'] ?></textarea>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>