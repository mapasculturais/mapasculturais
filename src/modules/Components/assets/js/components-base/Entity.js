class Entity {
    constructor(objectType, id, scope) {
        this.__objectType = objectType;
        this.id = id;
        this.__scope = (scope || 'default');
        this.__validationErrors = {};
        
        this.__messagesEnabled = true;
        this.__processing = false;

        this.__originalValues = {};

        // as traduções estão no arquivo texts.php do componente <entity>
        this.text = Utils.getTexts('mc-entity');
    }

    populate(obj, preserveValues = true) {
        const __properties = this.$PROPERTIES;
        const __relations = this.$RELATIONS;
        const defaultProperties = [
            'terms', 'seals', , 'currentUserPermissions', 
            'relatedAgents', 'agentRelations',
            'relatedSpaces', 'spaceRelations',
        ];
        
        this.populateId(obj);

        for (const prop of defaultProperties) {
            if (this[prop] && !obj[prop]) {
                continue;
            }
            let _default = prop == 'terms' ? [] : {};
            this[prop] = obj[prop] || _default;
        }

        for (let prop in __properties) {
            if(prop == this.$PK) {
                continue;
            }
            let definition = __properties[prop];
            let val = obj[prop];

            if(val === undefined && preserveValues) {
                val = this[prop];
            }

            if(prop === 'status' && preserveValues && this[prop] <= 0 && obj[prop] > 0) {
                this[prop] = obj[prop];
            }

            if ((definition.type == 'datetime' || definition.type == 'date' ) && val && !(val instanceof McDate)) {
                if (typeof val == 'string') {
                    val = new McDate(val);
                } else {
                    val = new McDate(val.date);
                }
            }

            if (definition.type === 'checklist' && !val) {
                val = [];
            }

            if (prop == 'location' && val) {
                if(val?.latitude && val?.longitude) {
                    val = {lat: parseFloat(val?.latitude), lng: parseFloat(val?.longitude)};
                }
                val.lat = val.lat ?? 0;
                val.lng = val.lng ?? 0;
            }

            if(prop == 'type' && (typeof val == 'number')) {
                val = {
                    id: val, 
                    name: __properties['type']?.options?.[val]
                };
            }
            if(JSON.stringify(this[prop]) != JSON.stringify(val)) {
                if(!preserveValues || this[prop] == undefined) {
                    this[prop] = val;
                }
            }
        }

        for (let key in __relations) {
            let prop = obj[key];
            let value;
            if (prop instanceof Array) {
                for (let i in prop) {
                    value = value || [];
                    value[i] = this.parseRelation(prop[i], key);
                }
            } else {
                value = this.parseRelation(prop, key);
            }

            if (value) {
                this[key] = value;
            }
        }

        this.populateFiles(obj.files);
        this.populateMetalists(obj.metalists);

        this.cleanErrors();
        
        this.__originalValues = this.data();
        return this;
    }

    parseRelation(prop, key) {
        const type = prop?.['@entityType'] || this.$RELATIONS[key]?.targetEntity?.toLocaleLowerCase();
        const id = typeof prop == 'number' ? prop : prop?.id 

        if (type && id) {
            const propAPI = new API(type, this.__scope);
            const instance = propAPI.getEntityInstance(id);
            if(typeof prop != 'number') {
                instance.populate(prop, true);
            }
            return instance;
        } else {
            return prop;
        }
    }

    populateId(obj) {
        this.id = obj[this.$PK];
    }

    populateFiles(files) {        
        this.files = this.files || {};
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
        this.metalists = this.metalists || {};
        for (let groupName in metalists) {
            const group = metalists[groupName];
            this.metalists[groupName] = group.map((data) => new EntityMetalist(this, groupName, data));
        }
    }

    cleanErrors() {
        this.__validationErrors = {};
    }

    catchErrors(res, data) {
        const message = data.data.message;
        
        if (res.status >= 500 && res.status <= 599) {
            this.sendMessage(message || this.text('erro inesperado'), 'error');
        } else if(res.status == 400) {
            if (data.error) {
                this.__validationErrors = data.data;
                this.sendMessage(message || this.text('erro de validacao'), 'error');
            }
        } else if(res.status == 403) {
            this.sendMessage(message || this.text('permissao negada'), 'error');
        }
    }

    data(onlyModifiedFields) {
        const skipProperties = ['id', 'createTimestamp', 'updateTimestamp', 'lastLoginTimestamp'];
        const skipRelations = ['user', 'subsite'];

        const result = {};

        const $PROPERTIES = this.$PROPERTIES;

        for (let prop in $PROPERTIES) {
            if (skipProperties.indexOf(prop) > -1) {
                continue;
            }

            const definition = $PROPERTIES[prop];
            
            let val = this[prop];

            if(val instanceof McDate) {
                if (definition.type == 'date') {
                    val = val.sql('date');
                } else if (definition.type == 'time') {
                    val = val.time();
                } else if (definition.type == 'datetime') {
                    val = val.sql('date') + ' ' + val.time();
                }
            }
            
            if (val && (typeof val == 'object')) {
                if (prop == 'type') {
                    val = val.id;
                } else {
                    result[prop] = JSON.parse(JSON.stringify(val));
                }
            } else {
                result[prop] = val;
            }
        }

        for (let prop in this.$RELATIONS) {
            const relation = this.$RELATIONS[prop];

            if (skipRelations.indexOf(prop) > -1 || !relation.isOwningSide) {
                continue;
            }

            // para a criação de oportunidades
            if(prop == 'ownerEntity' && this[prop]) {
                result[prop] = this[prop]?.id;
                result['objectType'] = this[prop]?.__objectType;
            } else if(prop == 'parent' && this[prop]) {
                if (this[prop]?.id != this.id) {
                    result[prop] = this[prop]?.id;
                }
            } else {
                result[prop] = this[prop]?.id;
            }
        }

        if(this.terms) {
            result.terms = JSON.parse(JSON.stringify(this.terms));
        }

        if(onlyModifiedFields) {
            for(let key in result) {
                if(!result[key] && !this.__originalValues[key]) {
                    delete result[key];
                }

                if(JSON.stringify(result[key]) == JSON.stringify(this.__originalValues[key])){
                    delete result[key];
                }
            }
        } 

        for(let key in result) {
            if(typeof result[key] == 'object' && !result[key] instanceof Entity) {
                if(result[key] instanceof Array) {
                    result[key] = [...result[key]];
                } else {
                    result[key] = {...result[key]};
                }
            }
        }
        return result;

    }

    get API () {
        return new API(this.__objectType, this.__scope || 'default');
    }

    get $PROPERTIES() {
        return this.API.getEntityDescription('!relations');
    }

    get $PK() {
        const __properties = this.$PROPERTIES;
        let pk;
        for (let prop in __properties) {
            if(__properties[prop].isPK) {
                pk = prop;
                break;
            }
        }

        return pk;
    }

    get id() {
        let pk = this.$PK;
        if (pk == 'id') {
            pk = '_id';
        } 
        return this[pk];
    }

    set id (value) {
        let pk = this.$PK;
        if (pk == 'id') {
            pk = '_id';
        } 
        this[pk] = value;
    }

    get __objectId() {
        return `${this.__scope}-${this.__objectType}-${this.id}`;
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

    sendMessage(message, type) {
        type = type || 'success';
        if(this.__messagesEnabled) {
            const messages = useMessages();
            messages[type](message);
        }
    }

    disableMessages() {
        this.__messagesEnabled = false;
    }

    enableMessages() {
        this.__messagesEnabled = true;
    }

    getUrl(action, params) {
        if (params) {
            params = {0: this.id, ...params};
        } else {
            params = [this.id];
        }
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
            result = Promise.resolve(data);
        } else {
            this.catchErrors(res, data);
            data.status = res.status;
            result = Promise.reject(data);
        }

        this.__processing = false;
        return result;
    }

    async POST(action, {callback, data, processingMessage}) {
        this.__processing = processingMessage || this.text('processando');
        const res = await this.API.POST(this.getUrl(action), data);
        callback = callback || (() => {});

        try {
            return this.doPromise(res, callback);
        } catch(error) {
            return this.doCatch(error);
        }
    }

    async doCatch(error) {
        this.__processing = false;
        this.sendMessage(this.text('erro inesperado'), 'error');
        return Promise.reject({error: true, status:0, data: this.text('erro inesperado'), exception: error});
    }

    async save(delay = 300, preserveValues = true) {
        this.__processing = this.text('salvando');
        if(!this.id) {
            preserveValues = false;
        }
        
        clearTimeout(this.__saveTimeout);

        this.resolvers = this.resolvers || [];
        this.rejecters = this.rejecters || [];

        return new Promise((resolve, reject) => {
            this.resolvers.push(resolve);
            this.rejecters.push(reject);

            this.__saveTimeout = setTimeout(async () => {
                try {
                    const data = this.data(true);
                    if(JSON.stringify(data) == '{}') {
                        const response = this.data();
                        for(let resolve of this.resolvers) {
                            resolve(response);
                        }

                        this.__processing = false;
                        return;
                    }

                    const res = await this.API.persistEntity(this);                    
                    this.doPromise(res, (entity) => {
                        if (this.id) {
                            this.sendMessage(this.text('modificacoes salvas'));
                        } else {
                            this.sendMessage(this.text('entidade salva'));
                        }
                        this.populate(entity, preserveValues);

                    }).then((response) => {
                        for(let resolve of this.resolvers) {
                            resolve(response);
                        }
                        this.cleanErrors();
                    }).catch((error) => {
                        for(let reject of this.rejecters) {
                            reject(error);
                        }
                    })
        
                } catch (error) {
                    this.doCatch(error).then((response) => {
                        for(let reject of this.rejecters) {
                            reject(response);
                        }                        
                    });
                }

            }, delay);
        });
    }

    async delete(removeFromLists) {
        this.__processing = this.text('excluindo');

        try {
            const res = await this.API.deleteEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade removida'));
                
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

        try {
            const res = await this.API.undeleteEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade recuperada'));
                this.populate(entity);
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }       
    }

    async destroy() {
        this.__processing = this.text('excluindo definitivamente');

        try {
            const res = await this.API.destroyEntity(this);
            return this.doPromise(res, () => {
                this.sendMessage(this.text('entidade removida definitivamente'));
                this.removeFromLists()
            });
        } catch (error) {
            return this.doCatch(error)
        }
    }

    async publish(removeFromLists) {
        this.__processing = this.text('publicando');
        try {
            // se há modificações não salvas, primeiro salva as alterações, só depois publica a entidade
            if(Object.keys(this.data(true)).length > 0) {
                await this.save();
            }
            const res = await this.API.publishEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade publicada'));
                this.populate(entity);
                if(removeFromLists) {
                    this.removeFromLists();
                }
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async duplicate(removeFromLists) {
        this.__processing = this.text('duplicando');

        try {
            const res = await this.API.duplicateEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade duplicada'));
                this.populate(entity);
                
                window.open('/minhas-oportunidades/#draft', '_blank').focus();
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    async archive(removeFromLists) {
        this.__processing = this.text('arquivando');

        try {
            const res = await this.API.archiveEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade arquivada'));
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

        try {
            const res = await this.API.unpublishEntity(this);
            return this.doPromise(res, (entity) => {
                this.sendMessage(this.text('entidade foi tornada rascunho'));
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
            return this.doCatch(error);
        }
    }

    async createMetalist(group, {title, description, value} ) {
        this.__processing = this.text('criando');
        try{
            const res = await this.API.POST(this.getUrl('metalist'), {group, title, description, value});

            this.metalists[group] = this.metalists[group] || [];

            this.doPromise(res, (data) => {
                const _data = {
                    id: data.id,
                    title, description, value
                };
                const metalist = new EntityMetalist(this, group, _data);
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
                
                agentRelation.agent = agent;

                this.agentRelations[group] = this.agentRelations[group] || [];
                this.agentRelations[group].push(agentRelation);
                
                this.relatedAgents[group] = this.relatedAgents[group] || [];
                this.relatedAgents[group].push(agent);
            
            }).then(response => {
                const messages = useMessages();

                if (response.status == -5) {
                    messages.success(this.text('solicitação enviada com sucesso'));
                }
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
        const global = useGlobalState();

        this.__processing = this.text('alterando propriedade da entidade');
        ownerId = ownerId || global.auth.user?.profile?.id;

        if (!ownerId) {
            return Promise.reject('ownerId indefinido');
        }

        try {
            const res = await this.API.POST(this.getUrl('changeOwner'), {ownerId});
            this.doPromise(res, (data) => {

                console.log('NÃO IMPLEMENTADO', data);

                /* let index;
                index = this.seals.indexOf(seal);
                this.seals.splice(index,1); */
            });
        } catch (error) {
            return this.doCatch(error);
        }
    }

    getHumanReadable(prop) {
        const propDefinitions = this.$PROPERTIES[prop];

        if(!propDefinitions?.options) {
            return this[prop]
        }else {
            return propDefinitions.options[this[prop]];
        }
    }
}