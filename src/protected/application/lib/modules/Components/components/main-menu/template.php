<div class="main-menu">

    <popover openside="down-left"> 
        <template #btn="{ toggle }">
            <button :class="['openPopever', 'main-menu__btn']" @click="toggle()" > Menu </button>
        </template>

        <template #content>
            <ul class="main-menu__desktop">
                <slot name="default"></slot>
            </ul>
        </template>
    </popover>

    <!-- Menu mobile -->
    
    <a class="main-menu__mobile--btn">
        <iconify icon="icon-park-outline:hamburger-button" />
    </a>    
    <ul class="main-menu__mobile">
        <slot name="default"></slot>
    </ul>
    
</div>