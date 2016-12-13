<nav id="home-nav">
    <ul>
        <li><a class="up icon icon-arrow-up" href="#"></a></li>
        <li id="nav-intro">
            <a class="icon icon-home" href="#home-intro"></a>
            <span class="nav-title"><?php \MapasCulturais\i::_e("Introdução");?></span>
        </li>
        
        <?php if($app->isEnabled('events')): ?>
            <li id="nav-events">
                <a class="icon icon-event" href="#home-events"></a>
                <span class="nav-title"><?php $this->dict('entities: Events') ?></span>
            </li>
        <?php endif; ?>
            
        <?php if($app->isEnabled('agents')): ?>
            <li id="nav-agents">
                <a class="icon icon-agent" href="#home-agents"></a>
                <span class="nav-title"><?php $this->dict('entities: Agents') ?></span>
            </li>
        <?php endif; ?>
            
        <?php if($app->isEnabled('spaces')): ?>
            <li id="nav-spaces">
                <a class="icon icon-space" href="#home-spaces"></a>
                <span class="nav-title"><?php $this->dict('entities: Spaces') ?></span>
            </li>
        <?php endif; ?>
            
        <?php if($app->isEnabled('projects')): ?>
            <li id="nav-projects">
                <a class="icon icon-project" href="#home-projects"></a>
                <span class="nav-title"><?php $this->dict('entities: Projects') ?></span>
            </li>
        <?php endif; ?>
            
        <li id="nav-developers">
            <a class="icon icon-developers" href="#home-developers"></a>
            <span class="nav-title"><?php \MapasCulturais\i::_e("Desenvolvedores");?></span>
        </li>
        <li><a class="down icon icon-select-arrow" href="#"></a></li>
    </ul>
</nav>
