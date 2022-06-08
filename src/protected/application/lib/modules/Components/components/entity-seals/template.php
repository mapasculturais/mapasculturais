<div class="entity-seals">
    <h4 class="entity-seals__title"> {{title}} </h4>

    <div class="entity-seals__seals">
        
        <div class="entity-seals__seals--seal" v-for="seal in entity.seals">
            <div v-if="seal.files?.avatar" class="image">
                <img :src="seal.files.avatar?.transformations?.avatarSmall?.url">
            </div>
            <span class="icon" v-if="editable"><iconify icon="gg:close"/></span>
        </div>
        
        <div class="entity-seals__seals--addSeal" v-if="editable">
            <span class="icon"><iconify icon="fluent:add-20-filled"/></span>
        </div>

    </div>
   
</div>