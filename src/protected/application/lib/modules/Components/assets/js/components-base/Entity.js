class Entity {
    constructor(objectType, id, scope) {
        this.id = id;
        this.__scope = scope;
        this.__objectType = objectType;
        this.__objectId = `${objectType}-${id}`;
        this.__validationErrors = {};
        
        this.__skipDataProperties = ['id', 'createTimestamp', 'updateTimestamp', 'lastLoginTimestamp'];

        this.__processing = false;

        // as traduções estão no arquivo texts.php do componente <entity>
        this.text = Utils.getTexts('entity');

    }

    populate(obj, preserveValues = true) {
        const __properties = this.$PROPERTIES;
        const __relations = this.$RELATIONS;
        const defaultProperties = ['terms', 'seals', 'relatedAgents', 'agentRelations', 'currentUserPermissions'];
        
        for (const prop of defaultProperties) {
            if (this[prop] && !obj[prop]) {
                continue;
            }
            let _default = prop == 'terms' ? [] : {};
            this[prop] = obj[prop] || _default;
        }

        for (let prop in __properties) {
            let definition = __properties[prop];
            let val = obj[prop];

            if(val === undefined && preserveValues) {
                val = this[prop];
            }

            if (definition.type == 'datetime' && val && !(val instanceof McDate)) {
                val = new McDate(val.date);
            }

            if (prop == 'location') {
                val = {lat: parseFloat(val?.latitude), lng: parseFloat(val?.longitude)};
            }

            this[prop] = val;
        }

        for (let key in __relations) {
            let prop = obj[key];
            if (prop instanceof Array) {
                for (let i in prop) {
                    prop[i] = this.parseRelation(prop[i], key);
                }
            } 
            this[key] = this.parseRelation(prop, key);
        }

        this.populateFiles(obj.files);
        this.populateMetalists(obj.metalists);

        this.cleanErrors();
        
        return this;
    }

    parseRelation(prop, key) {
        const type = prop?.['@entityType'] || $DESCRIPTIONS?.[this.__objectType]?.[key]?.targetEntity.toLocaleLowerCase();
        if (type && prop?.id) {
            const propAPI = new API(type, this.__scope);
            const instance = propAPI.getEntityInstance(prop.id);
            instance.populate(prop, true);
            return instance;
        } else {
            return prop;
        }
    }

    populateFiles(files) {
        if (this.files) {
            return;
        }
        this.files = {};
        for (let groupName in files) {
            const group = files[groupName];
            if (group instanceof Array) {
                this.files[groupName] = this.files[groupName] || [];
                this.files[groupName] = group.map((data) => new EntityFile(this, groupName, data));
            } else {
                this.files[groupName] = new EntityFile(this, groupName, group);
            }
        }
    }

    populateMetalists(metalists) {
        this.metalists = {};
        for (let groupName in metalists) {
            const group = metalists[groupName];
            this.metalists[groupName] = group.map((data) => new EntityMetalist(this, groupName, data));
        }
    }

    cleanErrors() {
        this.__validationErrors = {};
    }

    catchErrors(res, data) {
        const messages = useMessages();
        if (res.status >= 500 && res.status <= 599) {
            messages.error(this.text('erro inesperado'));
        } else if(res.status == 400) {
            if (data.error) {
                this.__validationErrors = data.data;
                messages.error(this.text('erro de validacao'));
            }
        } else if(res.status == 403) {
            messages.error(this.text('permissao negada'));
        }
    }

    data() {
        const result = {};

        for (let prop in this.$PROPERTIES) {
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

    get API () {
        return new API(this.__objectType, this.__scope || 'default');
    }

    get $PROPERTIES() {
        return this.API.getEntityDescription('!relations');
    }

    get $RELATIONS() {
        const result = this.API.getEntityDescription('relations');
        if (this.__objectType == 'opportunity') {
            result.ownerEntity = {
                isEntityRelation: true,
                isMetadata: false,
                isOwningSide: true,
                label: "",
                targetEntity: null,
            };
        }
        return result;
    }

    get $LISTS() {
        const lists = useEntitiesLists();
        return lists.fetchEntityLists(this);
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

    getUrl(action, params) {
        params = {0: this.id, ...params};
        return this.API.createUrl(action, params);
    }

    removeFromLists(skipList) {
        skipList = skipList || [];
        this.$LISTS.forEach((list) => {
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

    async save(preserveValues = true) {
        this.__processing = this.text('salvando');

        const messages = useMessages();

        try {
            const res = await this.API.persistEntity(this);
            return this.doPromise(res, (entity) => {

                if (this.id) {
                    messages.success(this.text('modificacoes salvas'));
                } else {
                    messages.success(this.text('entidade salva'));
                }
                this.populate(entity, preserveValues)
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
            return this.doPromise(res, (entity) => {
                messages.success(this.text('entidade removida'));
                
                if(removeFromLists) {
                    this.removeFromLists();
                }

                this.populate(entity);
            });

        } catch (error) {
            return this.doCatch(error)
        }        
    }

    async undelete(removeFromLists) {
        this.__processing = this.text('recuperando');

        const messages = useMessages();

        try {
            const res = await this.API.undeleteEntity(this);
            return this.doPromise(res, (entity) => {
                messages.success(this.text('entidade recuperada'));
                
                if(removeFromLists) {
                    this.removeFromLists();
                }

                this.populate(entity);
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
            return this.doPromise(res, (entity) => {
                messages.success(this.text('entidade publicada'));
                this.populate(entity);
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
            return this.doPromise(res, (entity) => {
                messages.success(this.text('entidade arquivada'));
                this.populate(entity);
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async unpublish(removeFromLists) {
        this.__processing = this.text('tornando rascunho');

        const messages = useMessages();

        try {
            const res = await this.API.unpublishEntity(this);
            return this.doPromise(res, (entity) => {
                messages.success(this.text('entidade foi tornada rascunho'));
                this.populate(entity);
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async upload(file, {group, description}) {
        this.__processing = this.text('subindo arquivo');

        const data = new FormData();
        data.append(group, file);
        if (description) {
            data.append(`description[${group}]`, description);
        }
        try{
            const res = await fetch(this.getUrl('upload'), {method: 'POST', body: data});
            return this.doPromise(res, (f) => {
                let file;
                if(f[group] instanceof Array) {
                    file = new EntityFile(this, group, f[group][0]);
                    this.files[group] = this.files[group] || [];
                    this.files[group].push(file);
                } else {
                    file = new EntityFile(this, group, f[group]);
                    this.files[group] = file;
                }
                return file;
            });
        } catch (error) {
            this.doCatch(error);
        }
    }

    async createMetalist(group, {title, description, value} ) {
        this.__processing = this.text('criando');
        try{
            const res = await this.API.POST(this.getUrl('metalist'), {group, title, description, value});

            this.metalists[group] = this.metalists[group] || [];

            this.doPromise(res, (data) => {
                const metalist = new EntityMetalist(this, group, {title, description, value});
                this.metalists[group] = this.metalists[group] || [];
                this.metalists[group].push(metalist);
            });
        } catch (error) {
            this.doCatch(error);
        }
    }

    async createAgentRelation(group, agent, hasControl, metadata) {
        try{
            const res = await this.API.POST(this.getUrl('createAgentRelation'), {group, agentId: agent.id, has_control: hasControl});

            this.doPromise(res, (agentRelation) => {
                delete agentRelation.owner;
                delete agentRelation.agentUserId;
                delete agentRelation.objectId;
                delete agentRelation.owner;
                delete agentRelation.ownerUserId;

                this.agentRelations[group] = this.agentRelations[group] || [];
                this.agentRelations[group].push(agentRelation);
                
                this.relatedAgents[group] = this.relatedAgents[group] || [];
                this.relatedAgents[group].push(agent);
            
            });
        } catch (error) {
            this.doCatch(error);
        }
    }

    async addRelatedAgent(group, agent, metadata) {
        this.__processing = this.text('adicionando agente relacionado');

        return this.createAgentRelation(group, agent);
    }

    async addAdmin(agent) {
        this.__processing = this.text('adicionando administrador');

        return this.createAgentRelation('group-admin', agent, true);
    }

    async removeAgentRelation(group, agent) {
        this.__processing = this.text('removendo agente relacionado');

        try {
            const res = await this.API.POST(this.getUrl('removeAgentRelation'), {group, agentId: agent.id});
            this.doPromise(res, (data) => {
                let index;
                
                index = this.agentRelations[group].indexOf(agent);
                this.agentRelations[group].splice(index,1);
                
                index = this.relatedAgents[group].indexOf(agent);
                this.relatedAgents[group].splice(index,1);
            
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }


    async removeAgentRelationGroup(group) {
        this.__processing = this.text('removendo grupo de agentes relacionados');

        try {
            const res = await this.API.POST(this.getUrl('removeAgentRelationGroup'), {group});
            this.doPromise(res, (data) => {
                delete this.agentRelations[group];
                delete this.relatedAgents[group];
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }


    async renameAgentRelationGroup(oldName, newName) {
        this.__processing = this.text('renomeando grupo de agentes relacionados');

        try {
            const res = await this.API.POST(this.getUrl('renameAgentRelationGroup'), {oldName, newName});
            this.doPromise(res, (data) => {
                this.agentRelations[newName] = this.agentRelations[oldName];
                this.relatedAgents[newName] = this.relatedAgents[oldName];
                delete this.agentRelations[oldName];
                delete this.relatedAgents[oldName];
               
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async createSealRelation(seal) 
    {   
        this.__processing = this.text('relacionando selo à entidade');

        try{
            const res = await this.API.POST(this.getUrl('createSealRelation'), {sealId: seal.id});

            this.doPromise(res, (r) => {

                const seal = {
                    sealId: r.seal.id,
                    sealRelationId: r.id,
                    singleUrl: Utils.createUrl('sealRelation', 'single', [r.id]),
                    name: r.seal.name,
                    createTimestamp: r.createTimestamp,
                    files: r.seal.files,
                };          
                
                this.seals = this.seals || [];
                this.seals.push(seal);
            });
        } catch (error) {
            this.doCatch(error);
        }
    }

    async removeSealRelation(seal) 
    {
        this.__processing = this.text('removendo selo da entidade');

        try {
            const res = await this.API.POST(this.getUrl('removeSealRelation'), {sealId: seal.sealId});
            this.doPromise(res, (data) => {
                let index;
                
                index = this.seals.indexOf(seal);
                this.seals.splice(index,1);
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async changeOwner(ownerId) {
        this.__processing = this.text('alterando propriedade da entidade');
        ownerId = ownerId || $MAPAS.userProfile.id;

        if (!ownerId) {
            return Promise.reject('ownerId indefinido');
        }

        try {
            const res = await this.API.POST(this.getUrl('changeOwner'), {ownerId});
            this.doPromise(res, (data) => {

                console.log(data);

                /* let index;
                index = this.seals.indexOf(seal);
                this.seals.splice(index,1); */
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }
}