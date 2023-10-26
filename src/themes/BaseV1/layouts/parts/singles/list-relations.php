<?php

    $groups = $entities->getGroupRelationsAgent();

    if(is_array($groups) && count($groups) > 0): ?>
    
        <div class="widget">
            <h3><?php \MapasCulturais\i::_e("Grupos que participa");?></h3>
            <ul class="js-slimScroll widget-list">
                <?php foreach ($groups as $group): ?>
                    
                    <li class="widget-list-item">
                        <?php echo $group['group']; ?> <?php \MapasCulturais\i::_e("em"); ?> 
                        <a href="<?php echo $group['url']; ?>" style="display: initial;">
                            <?php echo $group['entitie']; ?>
                        </a>
                    </li>
                    
                <?php endforeach; ?>
            </ul>    
        </div>
    
<?php endif; ?>
