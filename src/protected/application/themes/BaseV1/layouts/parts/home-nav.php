<nav id="home-nav">
    <?php $this->applyTemplateHook('home-nav','begin'); ?>
    <ul>
        <li><a class="up icon icon-arrow-up" href="#" rel='noopener noreferrer'></a></li>
        <li id="nav-intro">
            <a class="icon icon-home" href="#home-intro" rel='noopener noreferrer'></a>
            <span class="nav-title"><?php \MapasCulturais\i::_e("Introdução");?></span>
        </li>

        <?php if($app->isEnabled('events')): ?>
            <li id="nav-events">
                <a class="icon icon-event" href="#home-events" rel='noopener noreferrer'></a>
                <span class="nav-title"><?php $this->dict('entities: Events') ?></span>
            </li>
        <?php endif; ?>

        <?php if($app->isEnabled('agents')): ?>
            <li id="nav-agents">
                <a class="icon icon-agent" href="#home-agents" rel='noopener noreferrer'></a>
                <span class="nav-title"><?php $this->dict('entities: Agents') ?></span>
            </li>
        <?php endif; ?>

        <?php if($app->isEnabled('spaces')): ?>
            <li id="nav-spaces">
                <a class="icon icon-space" href="#home-spaces" rel='noopener noreferrer'></a>
                <span class="nav-title"><?php $this->dict('entities: Spaces') ?></span>
            </li>
        <?php endif; ?>

        <?php if($app->isEnabled('projects')): ?>
            <li id="nav-projects">
                <a class="icon icon-project" href="#home-projects" rel='noopener noreferrer'></a>
                <span class="nav-title"><?php $this->dict('entities: Projects') ?></span>
            </li>
        <?php endif; ?>

        <?php if($app->isEnabled('opportunities')): ?>
            <li id="nav-opportunities">
                <a class="icon icon-opportunity" href="#home-opportunities" rel='noopener noreferrer'></a>
                <span class="nav-title"><?php $this->dict('entities: Opportunities') ?></span>
            </li>
        <?php endif; ?>

        <li id="nav-developers">
            <a class="icon icon-developers" href="#home-developers" rel='noopener noreferrer'></a>
            <span class="nav-title"><?php \MapasCulturais\i::_e("Desenvolvedores");?></span>
        </li>
        <li><a class="down icon icon-select-arrow" href="#" rel='noopener noreferrer'></a></li>
    </ul>
    <?php $this->applyTemplateHook('home-nav','end'); ?>
</nav>
