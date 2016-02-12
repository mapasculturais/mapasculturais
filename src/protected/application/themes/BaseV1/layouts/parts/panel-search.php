<?php
if ($meta->count < 7 && empty($meta->keyword)){
    return;
}
?>
<div>
    <form>
        <div style="float:left">
            <input placeholder="Buscar por palabra clave" name="keyword" value="<?php echo isset($meta->keyword) ? $meta->keyword : '';?>" autofocus size="50">
            <input type="submit" value="Ok">
        </div>
        <div style="float:right">
            Ordenar por: <select name="order" onchange="this.form.submit()">
                <option value="name ASC" selected>Nombre</option>
                <option value="name DESC" <?php if($meta->order === 'name DESC') echo 'selected';?>>Nombre (Z-A)</option>
                <option value="createTimestamp DESC" <?php if($meta->order === 'createTimestamp DESC') echo 'selected';?>>Dato más reciente</option>
                <option value="createTimestamp ASC" <?php if($meta->order === 'createTimestamp ASC') echo 'selected';?>>Dato más antiguo</option>
            </select>
        </div>
    </form>
    <div class="clear"></div>
    <div style="float: left; margin-bottom: 15px;">
        <?php echo $meta->count; ?> <?php $meta->count > 1 ? $this->dict("entities: {$search_entity}s found") : $this->dict("entities: $search_entity found")?>:
    </div>
    <?php
    if ($meta->numPages > 1) {
        $key = isset($meta->keyword) ? $meta->keyword : '';
        $order = isset($meta->order) ? $meta->order : '';
        $pg = $meta->page;
        $tot = $meta->numPages;
        $prevLink = $pg - 1 > 0    ? "href='?keyword=$key&order=$order&page=" . ($pg - 1) . "'": "";
        $nextLink = $pg + 1 < $tot ? "href='?keyword=$key&order=$order&page=" . ($pg + 1) . "'": "";
        $prevStyle = $pg - 1 > 0    ? "class='btn btn-default'" : "class='btn btn-disabled' style='color:lightgrey'";
        $nextStyle = $pg + 1 < $tot ? "class='btn btn-default'" : "class='btn btn-disabled' style='color:lightgrey'";
        echo "
        <div style='float:right'>
            Página:
            <a $prevStyle href='?keyword=$key&order=$order&page=1'>&laquo;</a>
            <a $prevLink $prevStyle>&lsaquo;</a>
            <input value='$pg' size='3' style='text-align: center; font-size: 0.8rem;'>
            <a $nextLink $nextStyle>&rsaquo;</a>
            <a $nextStyle href='?keyword=$key&order=$order&page=$tot'>&raquo;</a>
        </div>";
    }
    ?>
    <div class='clear'></div>
</div>
