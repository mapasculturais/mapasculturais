globalThis.useEntitiesCache = Pinia.defineStore('entitiesCache', {
    state: () => {
        return {
            default: {}
        }
    },

    actions: {
        store(entity, scope) {
            this[scope] = this[scope] || {};
            this[scope][entity.cacheId] = entity;
        },

        remove(entity, scope) {
            this[scope] = this[scope] || {};
            delete this[scope][entity.cacheId];
        },
        
        fetch(cacheId, scope) {
            this[scope] = this[scope] || {};
            return this[scope][cacheId];
        }
    }
});

globalThis.useEntitiesLists = Pinia.defineStore('entitiesLists', {
    state: () => {
        return {
            default: {}
        }
    },

    actions: {
        store(name, list, scope) {
            list.__name = name;
            this[scope] = this[scope] || {};
            this[scope][name] = list;
        },

        remove(name, scope) {
            this[scope] = this[scope] || {};
            delete this[scope][name];
        },
        
        fetch(name, scope) {
            this[scope] = this[scope] || {};
            return this[scope][name];
        },

        fetchAll(scope) {
            this[scope] = this[scope] || {};
            return this[scope];
        },

        fetchEntityLists(entity, scope) {
            this[scope] = this[scope] || {};
            this[scope].ENTITY_LISTS = this[scope].ENTITY_LISTS || {};

            const objectId = entity.__objectId;
            this[scope].ENTITY_LISTS[objectId] = this[scope].ENTITY_LISTS[objectId] || [];
            return this[scope].ENTITY_LISTS[objectId];
        }
    }
});

globalThis.apiInstances = {};

class API {
    constructor(objectType, scope, fetchOptions) {
        if (apiInstances[objectType]) {
            return apiInstances[objectType];
        } else {
            this.scope = scope;
            this.cache = useEntitiesCache();
            this.lists = useEntitiesLists();
            this.objectType = objectType;
            this.options = {
                cacheMode: 'force-cache', 
                ...fetchOptions
            };

            apiInstances[objectType] = this;
        }
    }

    getHeaders(data) {
        if (data instanceof FormData) {
            return {};
        } else {
            return {'Content-Type': 'application/json'};
        }
    }

    parseData(data) {
        if (data instanceof FormData) {
            return data;
        } else {
            return JSON.stringify(data);
        }
    }

    parseUrl(url) {
        let _url = url.toString();
        if(_url.indexOf('/') === 0 || _url.indexOf('http://') === 0 || _url.indexOf('https://') === 0) {
            return url;
        } else {
            return this.createUrl(url);
        }
    }

    async GET(url, data, init) {
        url = this.parseUrl(url);
        const requestInit = {
            cache: this.options.cacheMode,
            ...init
        }

        return fetch(url, requestInit);
    }

    async PUT(url, data) {
        url = this.parseUrl(url);
        return fetch(url, {
            method: 'PUT',
            headers: this.getHeaders(data),
            body: this.parseData(data)
        }).catch((e) => {
            return new Response(null, {status: 0, statusText: 'erro inesperado'});
        });
    }

    async PATCH(url, data) {
        url = this.parseUrl(url);
        return fetch(url, {
            method: 'PATCH',
            headers: this.getHeaders(data),
            body: this.parseData(data)
        }).catch((e) => {
            return new Response(null, {status: 0, statusText: 'erro inesperado'});
        });
    }

    async POST(url, data) {
        url = this.parseUrl(url);
        return fetch(url, {
            method: 'POST',
            headers: this.getHeaders(data),
            body: this.parseData(data)
        }).catch((e) => {
            return new Response(null, {status: 0, statusText: 'erro inesperado'});
        });
    }

    async DELETE(url, data) {
        url = this.parseUrl(url);
        return fetch(url, {
            method: 'DELETE',
            headers: this.getHeaders(data),
            body: this.parseData(data)
        }).catch((e) => {
            return new Response(null, {status: 0, statusText: 'erro inesperado'});
        });
    }

    async persistEntity(entity) {
        if (!entity.id) {
            let url = Utils.createUrl(this.objectType, 'index');
            return this.POST(url, entity.data())
            
        } else {
            return this.PATCH(entity.singleUrl, entity.data())
        }
    }

    async deleteEntity(entity) {
        if (entity.id) {
            return this.DELETE(entity.singleUrl);   
        }
    }

    async destroyEntity(entity) {
        if (entity.id) {
            return this.DELETE(entity.getUrl('destroy'));   
        }
    }

    async publishEntity(entity) {
        if (entity.id) {
            return this.POST(entity.getUrl('publish'));   
        }
    }

    async archiveEntity(entity) {
        if (entity.id) {
            return this.POST(entity.getUrl('archive'));   
        }
    }

    async findOne(id, select) {
        let url = this.createApiUrl('findOne', {id: `EQ(${id})`, '@select': select || '*'});
        return this.GET(url).then(response => response.json().then(obj => {
            let entity = this.getEntityInstance(id);
            return entity.populate(obj);
        }));
    }

    async find(query, list) {
        let url = this.createApiUrl('find', query);
        return this.GET(url).then(response => response.json().then(objs => {
            let result = list || [];
            result.metadata = JSON.parse(response.headers.get('API-Metadata'));
            
            objs.forEach(element => {
                let entity = this.getEntityInstance(element.id);
                entity.populate(element);
                result.push(entity);
                entity.$LISTS.push(result);
            });

            return result;
        }));
    }

    createUrl(route, query) {
        const url = Utils.createUrl(this.objectType, route, query); 
        return url;
    }

    createApiUrl(route, query) {
        const url = Utils.createUrl(this.objectType, `api/${route}`); 
        for (var key in query) {
            url.searchParams.append(key, query[key]);
        }

        return url;
    }

    createCacheId(objectId) {
        return this.objectType + ':' + objectId;
    }

    getEntityInstance(objectId) {
        const cacheId = this.createCacheId(objectId);
        let entity = this.cache.fetch(cacheId, this.scope);
        if (entity) {
            return entity;
        } else {
            entity = new Entity(this.objectType, objectId, this.scope); 
            this.cache.store(entity, this.scope);
            return entity;
        }
    }

    getEntityDescription(filter) {
        const description = $DESCRIPTIONS[this.objectType];
                
        let result = {};

        function filteredBy(f) {
            let filters = filter.split(',');
            return filters.indexOf(f) > -1;
        }

        for (var key in description) {
            if(key.substr(0,2) === '__') {
                continue;
            }

            let desc = description[key];
            let ok = true
            
            if (filter) {
                if (filteredBy('private') && desc.private !== true) {
                    ok = false;
                }

                if (filteredBy('public') && desc.private) {
                    ok = false
                }

                if (filteredBy('metadata') && !desc.isMetadata) {
                    ok = false
                } else if(filteredBy('!metadata') && desc.isMetadata) {
                    ok = false
                }

                if (filteredBy('relations') && !desc.isEntityRelation) {
                    ok = false
                } else if(filteredBy('!relations') && desc.isEntityRelation) {
                    ok = false
                }
            }
                
            if (ok) {
                key = desc['@select'] || key;

                if (desc.isEntityRelation && key[0] == '_'){
                    key = key.substr(1);
                }
                
                result[key] = desc;
            }
        }

        return result;
    }
}