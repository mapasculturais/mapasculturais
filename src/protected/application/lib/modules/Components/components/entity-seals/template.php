<div class="entity-seals">
    <h4> {{title}} </h4>

    <div class="seals">
        <div class="seal" v-for="seal in entity.seals">
            <img v-if="seal.files?.avatar" :src="seal.files.avatar?.transformations?.avatarSmall?.url">
            <span class="icon" v-if="editable"><iconify icon="codicon:chrome-close"/></span>
        </div>
        <div class="addSeal " v-if="editable">
            <span class="icon"><iconify icon="ant-design:plus-outlined"/></span>

        </div>

    </div>
   
</div>