<?php
use MapasCulturais\i;
$section = '';
$groups = $this->getDictGroups();

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
$texts = \MapasCulturais\Themes\BaseV1\Theme::_dict();

?>
<div id="texts" class="aba-content">
    <p class="alert info">
        <?php i::_e('Nesta seção você configura os textos utilizados na interface do site. Cada texto tem uma explicação do local em que deverá aparecer a informação. A opção de “exibir opções avançadas” possibilita que outros campos apareçam para definição dos textos.'); ?>
    </p>

    <?php foreach($groups as $gname => $group): ?>
        <section class="filter-section">
            <header>
                <?php echo $group['title']; ?>
                <label class="show-all"><input class="js-exibir-todos" type="checkbox"> <?php i::_e('exibir opções avançadas'); ?></label>
            </header>
            <p class="help"><?php echo $group['description']; ?></p>
            <?php foreach ($texts as $key => $def):
                $skey = str_replace(' ', '+', $key);
                $section = substr($key, 0, strpos($key, ":"));
                if($section != $gname) continue; ?>

                <p class="js-text-config <?php if (isset($def['required']) && $def['required']): ?> required<?php else: ?> js-optional hidden<?php endif; ?>">
                    <span class="label">
                        <?php echo $def['name'] ?><?php if ($def['description']): ?><span class="info hltip" title="<?= htmlentities($def['description']) ?>"></span><?php endif; ?>:
                    </span>

                    <span class="js-editable js-editable--subsite-text"
                          data-edit="<?php echo "dict:" . $skey ?>"
                          data-original-title="<?php echo htmlentities($def['name']) ?>"
                          data-emptytext="<?php echo isset($entity->dict[$key]) && !empty($entity->dict[$key])? '': 'utilizando valor padrão (clique para definir)';?>"
                          <?php if (isset($def['examples']) && $def['examples']): ?>data-examples="<?= htmlentities(json_encode($def['examples'])) ?>" <?php endif; ?>
                          data-placeholder='<?php echo isset($entity->dict[$key]) && !empty($entity->dict[$key])? $entity->dict[$key]:$def['text'] ; ?>'><?php echo isset($entity->dict[$key]) ? $entity->dict[$key] : ''; ?></span>
                </p>

            <?php endforeach; ?>

        </section>
    <?php endforeach; ?>
</div>
