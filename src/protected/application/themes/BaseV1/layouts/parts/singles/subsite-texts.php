<?php
$section = '';
$groups = [
    'entities' => [
        'title' => 'Entidades',
        'description' => 'Textos usados para todas as entidades do site Mapas Culturais'
    ],
    'error' => [
        'title' => 'Erros',
        'description' => 'Textos de mensagens de erros exibidas no site do Mapas Culturais'
    ],
    'home' => [
        'title' => 'Página Inicial',
        'description' => 'Textos exibidos na seção principal do sistema'
    ],
    'roles' => [
        'title' => 'Perfis e Papéis',
        'description' => 'Textos referente a perfis e papéis de usuários'
    ],
    'search' => [
        'title' => 'Site',
        'description' => 'Textos usados em todo site do Mapas Culturais'
    ],
    'site' => [
        'title' => 'Site',
        'description' => 'Textos usados em todo site do Mapas Culturais'
    ],
    'taxonomies' => [
        'title' => 'Taxonomias',
        'description' => ''
    ]
];

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
$texts = \MapasCulturais\Themes\BaseV1\Theme::_dict();
ksort($texts);
?>
<div id="texts" class="aba-content">
    <p class="alert info">Maiores explicações aqui</p>
    <section class="filter-section">
    <?php foreach($texts as $key => $def):  $skey = str_replace(' ', '+', $key); ?>
        <?php if(substr($key,0,strpos($key,":")) != $section):?>
            <?php $section = substr($key,0,strpos($key,":"));?>
            <header><?php echo $groups[$section]['title'];?></header>
            <?php echo $groups[$section]['description'];?>
        <?php endif;?>

        <p>
            <span class="label"><?php echo $def['name'] ?>: </span>
            <span class="js-editable" data-edit="<?php echo "dict:" . $skey ?>" data-original-title="<?php echo htmlentities($def['name']) ?>" data-emptytext="<?php echo htmlentities($def['description'] ? $def['description'] : $def['name']) ?>"><?php echo isset($entity->dict[$key]) ? $entity->dict[$key] : ''; ?></span>
        </p>
    <?php endforeach; ?>
    </section>
</div>
