<link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.4.5/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet"/>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.4.5/bootstrap-editable/js/bootstrap-editable.min.js"></script>
<style>
    table.table > tbody > tr > td {
        height: 30px;
        vertical-align: middle;
    }

    .test-edit{
        color:green;
    }
</style>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Descrição</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($objects as $object){ ?>
        <tr>
            <td>
                <?php echo $object->id?>
                <a class="test-edit" href="<?php echo $this->controller->createUrl('edit', array('id'=>$object->id))?>">edit</a>
            </td>
            <td>
                <a href="#" data-name="name" data-type="text"
                   data-pk="<?php echo $object->id?>"
                   data-original-title="Nome"
                   data-mode="inline"
                   class="editable editable-click" style="display: inline;">
                    <?php echo $object->name ?></a>
            </td>
            <td>
                <a href="#" data-name="short_description" data-type="textarea"
                   data-pk="1" data-placeholder="Your comments here..."
                   data-original-title="Enter comments"
                   data-showButtons="bottom"
                   data-placement="left"
                   data-mode="inline"
                   class="editable editable-pre-wrapped editable-click" style="display: inline;">
                    <?php echo $object->shortDescription ?>
                </a>
                <!--                    <div data-name="short_description" data-pk="--><?php //echo $space->id?><!--"-->
                <!--                         data-type="wysihtml5" data-toggle="manual" data-original-title="Descrição"-->
                <!--                         class="editable editable-click" tabindex="-1" style="display: block;">-->
                <!--                        -->
                <!--                    </div>-->
            </td>
        </tr>
    <?php
    }?>
    </tbody>
    <tfoot>
    <th></th>
    <th></th>
    </tfoot>
</table>

<script>
    jQuery(function(){
        //$.fn.editable.defaults.mode = 'inline';
        $('.editable').editable();
//        $('.editable-inline').editable({
//            mode: 'inline',
//            url: '/teste/save',
//            type: 'text',
//            pk: 1,
//            name: 'username',
//            title: 'Enter username'
//        });
    })
</script>