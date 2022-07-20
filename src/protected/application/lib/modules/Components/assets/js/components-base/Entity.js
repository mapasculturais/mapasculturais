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

        // as traduções estão no arquivo texts.php do componente <entity>
        this.text = Utils.getTexts('entity');
    }

    populate(obj) {
        const defaultProperties = ['terms', 'files', 'metalists', 'seals', 'relatedAgents', 'agentRelations', 'currentUserPermissions'];

        for (const prop of defaultProperties) {
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
        }

        for (let prop in this.__relations) {
            this[prop] = obj[prop];
        }

        this.cleanErrors();
        
        return this;
    }

    cleanErrors() {
        for (let prop in this.__properties) {
            this.__validationErrors[prop] = [];
        }
    }

    catchErrors(res, data) {
        const messages = useMessages();
        if (res.status >= 500 && res.status <= 599) {
            messages.error(this.text('erro inesperado'));
        } else if(res.status == 400) {
            if (data.error) {
                this.__validationErrors = {...this.__validationErrors, ...data.data};
                messages.error(this.text('erro de validacao'));
            }
        } else if(res.status == 403) {
            messages.error(this.text('permissao negada'));
        }
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
        return this.getUrl('single');
    }

    get editUrl() {
        return this.getUrl('edit');
    }

    get cacheId() {
        return this.API.createCacheId(this.id);
    }

    getUrl(action) {
        return this.API.createUrl(action, [this.id]);
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

    async doPromise(res, cb) {
        let data = await res.json();
        let result;

        if (res.ok) { // status 20x
            data = cb(data) || data;
            this.cleanErrors();
            result = Promise.resolve(data);
        } else {
            this.catchErrors(res, data);
            data.status = res.status;
            result = Promise.reject(data);
        }

        this.__processing = false;
        return result;
    }

    async doCatch(error) {
        const messages = useMessages();

        this.__processing = false;
        messages.error(this.text('erro inesperado'));
        return Promise.reject({error: true, status:0, data: this.text('erro inesperado'), exception: error});
    }

    async save() {
        this.__processing = this.text('salvando');

        const messages = useMessages();

        try {
            const res = await this.API.persistEntity(this);
            return this.doPromise(res, (data) => {
                if (this.id) {
                    messages.success(this.text('modificacoes salvas'));
                } else {
                    messages.success(this.text('entidade salva'));
                }
    
                this.id = data.id;
            });

        } catch (error) {
            return this.doCatch(error)
        }
    }

    async delete(removeFromLists) {
        this.__processing = this.text('excluindo');

        const messages = useMessages();

        try {
            const res = await this.API.deleteEntity(this);
            return this.doPromise(res, () => {
                messages.success(this.text('entidade removida'));
                
                if(removeFromLists) {
                    this.removeFromLists();
                }

                this.status = -10;
            });

        } catch (error) {
            return this.doCatch(error)
        }        
    }

    async destroy() {
        this.__processing = this.text('excluindo definitivamente');

        const messages = useMessages();

        try {
            const res = await this.API.destroyEntity(this);
            return this.doPromise(res, () => {
                messages.success(this.text('entidade removida definitivamente'));
                this.removeFromLists()
            });
        } catch (error) {
            return this.doCatch(error)
        }
    }

    async publish(removeFromLists) {
        this.__processing = this.text('publicando');

        const messages = useMessages();

        try {
            const res = await this.API.publishEntity(this);
            return this.doPromise(res, () => {
                messages.success(this.text('entidade publicada'));
                this.status = 1;
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async archive(removeFromLists) {
        this.__processing = this.text('arquivando');

        const messages = useMessages();

        try {
            const res = await this.API.archiveEntity(this);
            return this.doPromise(res, () => {
                messages.success(this.text('entidade arquivada'));
                this.status = -2;
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async upload(file, group) {
        this.__processing = this.text('subindo arquivo');

        const data = new FormData();
        data.append(group, file);

        try{
            const res = await fetch(this.getUrl('upload'), {method: 'POST', body: data});
            return this.doPromise(res, (f) => {
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
            });
        } catch (error) {
            this.doCatch(error);
        }
    }

    async createAgentRelation(group, agent, hasControl, metadata) {
        return this.API.POST(this.getUrl('createAgentRelation'), {group, agentId: agent.id, has_control: hasControl})
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

    async addRelatedAgent(group, agent, metadata) {
        this.__processing = this.text('adicionando agente relacionado');

        return this.createAgentRelation(group, agent);
    }

    async removeAgentRelation(group, agent) {
        this.__processing = this.text('removendo agente relacionado');

        try {
            const res = this.API.POST(this.getUrl('removeAgentRelation'), {group, agentId: agent.id});
            res.then(() => {
                let index;
                
                index = this.agentRelations[group].indexOf(agent);
                this.agentRelations[group].splice(index,1);
                
                index = this.relatedAgents[group].indexOf(agent);
                this.relatedAgents[group].splice(index,1);

                this.__processing = false;
            
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }


    async removeAgentRelationGroup(group) {
        this.__processing = this.text('removendo grupo de agentes relacionados');

        try {
            const res = this.API.POST(this.getUrl('removeAgentRelationGroup'), {group});
            res.then(() => {
                delete this.agentRelations[group];
                delete this.relatedAgents[group];
                
                this.__processing = false;
            
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }


    async renameAgentRelationGroup(oldName, newName) {
        this.__processing = this.text('renomeando grupo de agentes relacionados');

        try {
            const res = this.API.POST(this.getUrl('renameAgentRelationGroup'), {oldName, newName});
            res.then(() => {
                this.agentRelations[newName] = this.agentRelations[oldName];
                this.relatedAgents[newName] = this.relatedAgents[oldName];
                delete this.agentRelations[oldName];
                delete this.relatedAgents[oldName];
                
                this.__processing = false;
            
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }
}