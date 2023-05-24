class EntityMetalist {
    constructor(owner, group, data) {
        this.$PK = 'id';
        this.id = data.id;
        this.title = data.title;
        this.description = data.description;
        this.value = data.value;

        this._owner = owner;
        this._group = group;

        this.API = new API('metalist', this._owner.__scope || 'default');

        this.text = Utils.getTexts('entity');
    }

    get singleUrl() {
        return Utils.createUrl('metalist', 'single', [this.id]);
    }

    get objectType() {
        return 'metalist';
    }

    data() {
        return {
            id: this.id,
            title: this.title,
            description: this.description
        };
    }

    async save() {
        const owner = this._owner;
        owner.__processing = this.text('salvando');
        try {
            const res = await this.API.persistEntity(this);
            owner.doPromise(res, () => null);
        } catch (error) {
            owner.doCatch(error);
        }
    }

    async delete() {
        const owner = this._owner;
        owner.__processing = this.text('removendo');
        try {
            const res = await this.API.deleteEntity(this);

            owner.doPromise(res, () => {
                const group = owner.metalists[this._group]
                let index = group.indexOf(this);
                group.splice(index,1);
            });
        } catch (error) {
            owner.doCatch(error);
        }
    }
}