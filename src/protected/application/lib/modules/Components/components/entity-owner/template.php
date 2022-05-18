<div class="entity-owner">
    <h4>{{title}}</h4>
    <a :href="owner.singleUrl" :title="owner.shortDescription">
        <div class="owner">
            <div class="owner-img">
                <img v-if="owner.files.avatar.url != ''" class="owner-img-profile" :src="owner.files.avatar.url">
                <div v-else class="owner-img-placeholder">
                    <iconify icon="bi:image-fill" />
                </div>
            </div>
            <div class="owner-name">
                {{owner.name}}
            </div>
        </div>
    </a>
</div>