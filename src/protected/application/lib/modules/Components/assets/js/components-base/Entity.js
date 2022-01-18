class Entity {
    constructor(objectType, id, scope) {
        this.id = id;
        this.__scope = scope;
        this.__objectType = objectType;
        this.__objectId = `${objectType}-${id}`;
        this.__validationErrors = {};
        this.__properties = this.API.getEntityDescription('!relations');
        this.__relations = this.API.getEntityDescription('relations');
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

            this.__validationErrors[prop] = [];
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

    get API () {
        return new API(this.__objectType, this.__scope || 'default');
    }

    get singleUrl() {
        return this.API.createUrl('single', [this.id]);
    }

    get destroyUrl() {
        return this.API.createUrl('destroy', [this.id]);
    }

    get publishUrl() {
        return this.API.createUrl('publish', [this.id]);
    }

    get archiveUrl() {
        return this.API.createUrl('archive', [this.id]);
    }

    get cacheId() {
        return this.API.createCacheId(this.id);
    }

    async save() {
        const messages = useMessages();

        this.__processing = 'salvando';
        return this.API.persistEntity(this)
            .then((response) => {
                this.__processing = false;
                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
                return error;
            });
    }

    async delete() {
        const messages = useMessages();

        this.__processing = 'excluindo';
        return this.API.deleteEntity(this)
            .then((response) => {
                this.__processing = false;
                this.__lists.forEach((list) => {
                    let index = list.indexOf(this);
                    if (index >= 0){
                        list.splice(index,1);
                    }
                });

                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
        
    }

    async destroy() {
        const messages = useMessages();

        this.__processing = 'excluindo definitivamente';
        return this.API.destroyEntity(this)
            .then((response) => {
                this.__processing = false;
                this.__lists.forEach((list) => {
                    let index = list.indexOf(this);
                    if (index >= 0){
                        list.splice(index,1);
                    }
                });

                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });

    }

    async publish() {
        const messages = useMessages();

        this.__processing = 'publicando';
        return this.API.publishEntity(this)
            .then((response) => {
                this.__processing = false;
                this.__lists.forEach((list) => {
                    let index = list.indexOf(this);
                    if (index >= 0){
                        list.splice(index,1);
                    }
                });

                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
    }

    async archive() {
        const messages = useMessages();

        this.__processing = 'arquivando';
        return this.API.archiveEntity(this)
            .then((response) => {
                this.__processing = false;
                this.__lists.forEach((list) => {
                    let index = list.indexOf(this);
                    if (index >= 0){
                        list.splice(index,1);
                    }
                });

                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
    }
}