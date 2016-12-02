<?php
$section = '';
$groups = $this->getDictGroups();

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
$texts = \MapasCulturais\Themes\BaseV1\Theme::_dict();
?>


<div id="texts" class="aba-content">
    <p class="alert info">Nesta seção você configura os textos utilizados na interface do site.</p>
    <section class="filter-section">
        <?php foreach ($texts as $key => $def): $skey = str_replace(' ', '+', $key); ?>
            <?php if (substr($key, 0, strpos($key, ":")) != $section): ?>
            </section>
            <section class="filter-section">
                <?php $section = substr($key, 0, strpos($key, ":")); ?>
                <header>
                    <?php echo $groups[$section]['title']; ?>
                    <label class="show-all"><input class="js-exibir-todos" type="checkbox"> exibir opções avançadas</label>
                </header>
                <p class="help"><?php echo $groups[$section]['description']; ?></p>

            <?php endif; ?>

            <p class="js-text-config <?php if (isset($def['required']) && $def['required']): ?> required<?php else: ?> js-optional hidden<?php endif; ?>">
                <span class="label"><?php echo $def['name'] ?>: </span>
                <span class="js-editable js-editable--subsite-text" 
                      data-edit="<?php echo "dict:" . $skey ?>" 
                      data-original-title="<?php echo htmlentities($def['name']) ?>" 
                      data-emptytext="utilizando valor padrão (clique para definir)"
                      <?php if (isset($def['examples']) && $def['examples']): ?>data-examples="<?= htmlentities(json_encode($def['examples'])) ?>" <?php endif; ?>
                      data-placeholder="<?= htmlentities($def['text']) ?>"><?php echo isset($entity->dict[$key]) ? $entity->dict[$key] : ''; ?></span>
                <?php if ($def['description']): ?> <span class="info hltip" title="<?= htmlentities($def['description']) ?>"></span> <?php endif; ?>
            </p>
        <?php endforeach; ?>
    </section>
</div>
