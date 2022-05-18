<div class="entity-links">
    <h2> {{title}} </h2>

    <div class="links">
        <a v-for="link in entity.metalists.links" :href="link.value" target="_blank" >
            <iconify icon="eva:link-outline" /> {{link.title}}
        </a>
    </div>
</div>

<!-- :href="file.url" -->