class Entity {
    constructor(objectType, id, scope) {
        this.id = id;
        this.__scope = scope;
        this.__objectType = objectType;
        this.__objectId = `${objectType}-${id}`;
        this.__validationErrors = {};

        this.API = new API(this.__objectType, this.__scope || 'default');

        this.__properties = this.API.getEntityDescription('!relations');
        this.__relations = this.API.getEntityDescription('relations');
        this.__skipDataProperties = ['createTimestamp', 'updateTimestamp'];
        
        this.__lists = [];
        this.__processing = false;    
    }

    populate(obj) {

        for (const prop of ['terms', 'files', 'metalists', 'seals', 'relatedAgents', 'agentRelations']) {
            if (obj[prop]) {
                this[prop] = obj[prop];
            }    
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

        if(this.terms) {
            result.terms = this.terms;
        }

        return result;
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

    get uploadUrl() {
        return this.API.createUrl('upload', [this.id]);
    }

    get createAgentRelationUrl() {
        return this.API.createUrl('createAgentRelation', [this.id]);
    }

    get cacheId() {
        return this.API.createCacheId(this.id);
    }

    removeFromLists(skipList) {
        skipList = skipList || [];
        this.__lists.forEach((list) => {
            if (skipList.indexOf(list.__name) >= 0) {
                return;
            }
            let index = list.indexOf(this);
            if (index >= 0){
                list.splice(index,1);
            }
        });
    }

    async save() {
        const messages = useMessages();
        this.__processing = 'salvando';
        return this.API.persistEntity(this)
            .then((response) => {
                this.__processing = false;
                const rJson = response.json();
                rJson.then(obj => {
                    if(!obj.error){
                        this.id = obj.id;
                    }
                })
                return rJson;
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
                return error;
            });
    }

    async delete(removeFromLists) {
        const messages = useMessages();

        this.__processing = 'excluindo';
        return this.API.deleteEntity(this)
            .then((response) => {
                this.status = -10;
                if(removeFromLists) {
                    this.removeFromLists();
                }
                this.__processing = false;
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
                this.removeFromLists();
                this.__processing = false;
                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });

    }

    async publish(removeFromLists) {
        const messages = useMessages();
        
        this.__processing = 'publicando';
        return this.API.publishEntity(this)
            .then((response) => {
                this.status = 1;
                if(removeFromLists) {
                    this.removeFromLists();
                }
                this.__processing = false;
                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
    }

    async archive(removeFromLists) {
        const messages = useMessages();

        this.__processing = 'arquivando';
        return this.API.archiveEntity(this)
            .then((response) => {
                this.status = -2;
                if(removeFromLists) {
                    this.removeFromLists();
                }
                this.__processing = false;
                return response.json();
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
    }

    async upload(file, group) {
        const data = new FormData();
        data.append(group, file);

        this.__processing = 'subindo arquivo';

        fetch(this.uploadUrl, {method: 'POST', body: data})
            .then(response => response.json().then(f => {
                let file;
                if(f[group] instanceof Array) {
                    file = f[group][0];
                    file.transformations = file.files;
                    delete file.files;
                    this.files[group].push(file);
                } else {
                    file = f[group];
                    file.transformations = file.files;
                    delete file.files;
                    this.files[group] = file;
                }

                return file;
            }))
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            });
    }

    async createAgentRelation(group, agent, hasControl, metadata) {
        this.__processing = true;
        
        return this.API.POST(this.createAgentRelationUrl, {group, agentId: agent.id, has_control: hasControl})
            .then(response => response.json().then(agentRelation => {
                delete agentRelation.owner;
                delete agentRelation.agentUserId;
                delete agentRelation.objectId;
                delete agentRelation.owner;
                delete agentRelation.ownerUserId;

                this.agentRelations[group] = this.agentRelations[group] || [];
                this.agentRelations[group].push(agentRelation);
                
                this.relatedAgents[group] = this.relatedAgents[group] || [];
                this.relatedAgents[group].push(agent);
                
                console.log(agentRelation);
                this.__processing = false;
            })
            .catch((error) => {
                this.__processing = false;
                console.log(error);
            }))
    }

    async addRelatedAgent(group, agentId, metadata) {
        console.log('teste');
        return this.createAgentRelation(group, agentId);
    }
}