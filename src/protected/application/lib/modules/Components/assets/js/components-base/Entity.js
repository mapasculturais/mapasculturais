class Entity {
    constructor(objectType, id) {
        this.id = id;
        this.__objectType = objectType;
        this.__api = new API(objectType);
        this.__properties = this.__api.getEntityDescription('!relations');
        this.__relations = this.__api.getEntityDescription('relations');
        this.__skipDataProperties = ['createTimestamp', 'updateTimestamp'];
        
        this.__lists = [];
        
        this.__processing = false;    
    }

    populate(obj) {
        if (obj.terms) {
            this.terms = obj.terms;
        }

        for (let prop in this.__properties) {
            let definition = this.__properties[prop];
            let val = obj[prop];

            if (definition.type == 'datetime' && val) {
                val = new Date(val.date);
            }

            this[prop] = val;
        }

        for (let prop in this.__relations) {
            this[prop] = obj[prop];
        }
        
        return this;
    }

    data() {
        const result = {};

        for (let prop in this.__properties) {
            if (this.__skipDataProperties.indexOf(prop) > -1) {
                continue;
            }

            let val = this[prop];

            if (prop == 'type' && typeof val == 'object') {
                val = val.id;
            }

            result[prop] = val;
        }

        return result;
    }

    get singleUrl() {
        return this.__api.createUrl('single', [this.id]);
    }

    get cacheId() {
        return this.__api.createCacheId(this.id);
    }

    async save() {
        const messages = useMessages();

        this.__processing = true;
        return this.__api.persistEntity(this)
            .then(() => {
                this.__processing = false;
                messages.alert('Modificações salvas')
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
                messages.error('Erro')
            });
    }

    async delete() {
        const messages = useMessages();

        this.__processing = true;
        return this.__api.deleteEntity(this)
            .then(() => {
                this.__processing = false;
                this.__lists.forEach((list) => {
                    let index = list.indexOf(this);
                    list.splice(index,1);
                });
                messages.alert('Objeto removido');
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
                messages.error('Erro ao remover objeto');
            });
        
    }

    async destroy() {

    }

    async publish() {

    }

    async archive() {

    }
}