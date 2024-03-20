<div>
    <div v-for="(section, index) in homeTexts" :key="index">
        <h2>{{ section.sectionName }}</h2>
        <div class="field" v-for="(text, textIndex) in section.texts" :key="textIndex">
            <label :for="text.slug">{{ text.description }}</label>
            <input :id="text.slug" v-model="subsite.homeTexts[text.slug]" @change="subsite.save()" />
        </div>
    </div>
</div>