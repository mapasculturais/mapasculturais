<div class="sidebar-left sidebar registration">
    <?php if($entity->canUser('evaluate')): ?>
    <style>
        .registration-list .registration-item.current {
            background: #DDE;
        }
    </style>
        
    <ul class="registration-list">
        <?php foreach($opportunity->getSentRegistrations() as $registration): ?>
            <li class="registration-item <?php if($entity->equals($registration)): ?>current <?php endif; ?>" >
                <a href="<?php echo $registration->singleUrl ?>"><?php echo $registration->getNumber() ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>