<div>
    <p>Critérios de Avaliação</p>
    <div v-for="section in sections" :key="section.id">
        <h3>{{ section.name }}</h3>
        <div v-for="crit in section.criteria" :key="crit.id">
            <label>
                {{ crit.name }}
            </label>
            <div>
                <select>
                    <option>selecione</option>
                    <option v-for="option in crit.options" :key="option">{{ option }}</option>
                </select>
            </div>
        </div>
    </div>
</div>