<div class="entity-seals">
    <h4> {{title}} </h4>

    <div class="seals">
        <div class="seal" v-for="seal in entity.seals">
            <img v-if="seal.files?.avatar" :src="seal.files.avatar?.transformations?.avatarSmall?.url">
        </div>
    </div>
   
</div>