<div v-if="owner" class="entity-owner">
    <h4>{{title}}</h4>
    <a class="entity-owner__owner" :href="owner.singleUrl" :title="owner.shortDescription">
        <div class="entity-owner__owner--img">
            <img v-if="owner.files.avatar.url != ''" class="profile" :src="owner.files.avatar.url">
            <div v-else class="placeholder">
                <iconify icon="bi:image-fill" />
            </div>
        </div>
        <div class="entity-owner__owner--name">
            {{owner.name}}
        </div>
    </a>
    <div v-if="editable" class="entity-owner__edit" >
        <a class="entity-owner__edit--btn">
            <iconify icon="material-symbols:change-circle-outline"/><h4> Alterar Propriedade</h4>
        </a>
    </div>
</div>