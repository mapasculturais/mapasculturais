<div v-if="files" class="files-list">
    <h2> {{title}} </h2>
    <div class="files">
        <a v-for="file in files" :href="file.url">
            <iconify icon="el:download-alt" /> {{file.name}}
        </a>
    </div>
</div>