class EntityFile {
    constructor(owner, group, data) {
        this.$PK = 'id';
        this.id = data.id;
        this.name = data.name;
        this.description = data.description;
        this.url = data.url;
        this.mimeType = data.mimeType;
        this.transformations = data.transformations || data.files || {};
        this.createTimestamp = data.createTimestamp ? new McDate (data.createTimestamp.date) : null;

        this._owner = owner;
        this._group = group;

        this.API = new API('file', this._owner.__scope || 'default');

        this.text = Utils.getTexts('entity');
    }

    get singleUrl() {
        return Utils.createUrl('file', 'single', [this.id]);
    }

    data() {
        return {
            id: this.id,
            description: this.description
        };
    }

    async save() {
        const owner = this._owner;
        owner.__processing = this.text('salvando arquivo');
        try {
            const res = await this.API.persistEntity(this);
            owner.doPromise(res, () => null);
        } catch (error) {
            owner.doCatch(error);
        }
    }

    async delete() {
        const owner = this._owner;
        owner.__processing = this.text('removendo arquivo');
        try {
            const res = await this.API.deleteEntity(this);

            owner.doPromise(res, () => {
                const group = owner.files[this._group]
                if (group instanceof Array) {
                    let index = group.indexOf(this);
                    group.splice(index,1); 
                } else {
                    delete owner.files[this._group];
                }
            });
        } catch (error) {
            owner.doCatch(error);
        }
    }
}