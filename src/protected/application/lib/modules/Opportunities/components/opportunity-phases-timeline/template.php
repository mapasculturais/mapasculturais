<?php
use MapasCulturais\i;
?>


<section :class="['timeline', {'center': center}, {'big': big}]">
    <div v-for="item in phases" :class="['item', {'active': isActive(item.id)}, {'happened': itHappened(item.id)}]">
        <div class="item__dot"> <span class="dot"></span> </div>

        <div class="item__content">
            <div v-if="item.isFirstPhase" class="item__content--title"> <?= i::__('Fase de inscrições') ?> </div>
            <div v-if="!item.isFirstPhase" class="item__content--title"> {{item.name}} </div>

            <div v-if="!item.isLastPhase" class="item__content--description">
                <?= i::__('de') ?> <span v-if="dateFrom(item.id)">{{dateFrom(item.id)}}</span>
                <?= i::__('a') ?> <span v-if="dateTo(item.id)">{{dateTo(item.id)}}</span>
                <?= i::__('às') ?> <span v-if="hour(item.id)">{{hour(item.id)}}</span>
            </div>

            <div v-if="item.isLastPhase" class="item__content--description">
                <span v-if="item.publishTimestamp">
                    {{item.publishTimestamp.date('2-digit year')}}
                    <?= i::__('às') ?> 
                    {{item.publishTimestamp.time()}}
                </span>
            </div>
        </div>
    </div>
</section>