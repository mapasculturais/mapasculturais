<div class="entity-seals">
    <h4 class="entity-seals__title"> {{title}} </h4>

    <div class="entity-seals__seals">
        
        <div class="entity-seals__seals--seal" v-for="seal in entity.seals">
            <div v-if="seal.files?.avatar" class="image">
                <img :src="seal.files.avatar?.transformations?.avatarSmall?.url">
            </div>
            <span class="icon" v-if="editable">
                <mc-icon name="delete"></mc-icon>
            </span>
        </div>
        
        <div class="entity-seals__seals--addSeal" v-if="editable">
            <span class="icon">
                <mc-icon name="add"></mc-icon>
            </span>
        </div>

    </div>
   
</div>