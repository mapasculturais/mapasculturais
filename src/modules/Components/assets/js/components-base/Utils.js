globalThis.__ = (key, componentName) => {
    const dict = Utils.getTexts(componentName);
    const text = dict(key);

    if (!text) {
        console.error(`TRADUÇÃO FALTANDO "${key}" do componente "${componentName}`);
    }

    return text || key;
}

globalThis.Utils = {
    getTexts(componentName) {
        const texts = $MAPAS.gettext?.[`component:${componentName}`] || {};
        return (key) => {
            const text = texts[key];

            if (!text) {
                console.error(`TRADUÇÃO FALTANDO "${key}" do componente "${componentName}`);
            }
        
            return text || key;
        };
    },

    getObjectProperties (obj) {
        var keys = [];
        for (var key in obj) {
            keys.push(key);
        }
        return keys;
    },

    sortOjectProperties (obj) {
        if(obj instanceof Array) {
            return obj;
        }

        var newObj = {};

        this.getObjectProperties(obj).sort().forEach(function (e) {
            newObj[e] = obj[e];
        });

        return newObj;
    },

    isObjectEquals (obj1, obj2) {
        return JSON.stringify(this.sortOjectProperties(obj1)) === JSON.stringify(this.sortOjectProperties(obj2));
    },

    inArray (array, obj) {
        for (var i in array) {
            if (this.isObjectEquals(array[i], obj)) {
                return true;
            }
        }
        return false;
    },

    arraySearch (array, obj) {
        for (var i in array) {
            if (this.isObjectEquals(array[i], obj)) {
                return i;
            }
        }
        return false;
    },

    createUrl(controllerId, action_name, args) {
        const shortcuts = $MAPAS.routes.shortcuts;
        const actions = $MAPAS.routes.actions;
        const controllers = $MAPAS.routes.controllers;
        const api = action_name.indexOf('api/') === 0;
        if(api) {
            action_name = action_name.substr(4);
        }
        
        let route = '';
        
        action_name = action_name || $MAPAS.routes.default_action_name;
        
        if (args) {
            if(JSON.stringify(Object.keys(args)) == '["0"]') {
                args = [args[0]];
            }
            args = this.sortOjectProperties(args);
        }

        if (args && this.inArray(shortcuts, [controllerId, action_name, args])) {
            route = this.arraySearch(shortcuts, [controllerId, action_name, args]) + '/';
            args = null;
        } else if (this.inArray(shortcuts, [controllerId, action_name])) {
            route = this.arraySearch(shortcuts, [controllerId, action_name]) + '/';
        } else {
            if (this.inArray(controllers, controllerId)) {
                route = this.arraySearch(controllers, controllerId) + '/';
            } else {
                route = controllerId + '/';
            }

            if (action_name !== $MAPAS.routes.default_action_name) {
                if (this.inArray(actions, action_name)) {
                    route += this.arraySearch(actions, action_name) + '/';
                } else {
                    route += action_name + '/';
                }
            }
        }

        if (args) {
            for (var key in args) {
                var val = args[key];
                if (key == parseInt(key)) { // is integer
                    route += val + '/';
                } else {
                    route += key + ':' + val + '/';
                }
            }
        }

        if(api) {
            return new URL($MAPAS.baseURL + `api/${controllerId}/${action_name}`);
        } else {
            return new URL($MAPAS.baseURL + route);
        }
        
    },

    entityRawProcessor (entity){
        entity.__objectId = `${entity['@entityType']}:${entity.id}`;
        if (entity.location) {
            entity.location = {lat: entity.location.latitude, lng: entity.location.longitude};
        }
        return entity;
    },

    occurrenceRawProcessor (rawData, eventApi, spaceApi) {
        eventApi = eventApi || new API('event');
        spaceApi = spaceApi || new API('space');

        const data = rawData;
        const event = eventApi.getEntityInstance(rawData.event.id); 
        const space = spaceApi.getEntityInstance(rawData.space.id); 

        event.populate(rawData.event, true);
        space.populate(rawData.space, true);

        data.event = event;
        data.space = space;

        data.starts = new McDate(rawData.starts.date);
        data.ends = new McDate(rawData.ends.date);

        return data;
    },

    parsePseudoQuery (pseudoQuery) {
        const newQuery = {};
        for(let k in pseudoQuery) {
            let val = pseudoQuery[k];
            let not = '';
            if(typeof val == 'undefined') {
                continue;
            }
            if(typeof val == 'string' && val.indexOf('!') === 0) {
                not = '!';
                val = val.substr(1);
            } else if (typeof val == 'number') {
                val = String(val);
            }

            if(k == '@verified' || typeof val == 'boolean') {
                if (val) {
                    newQuery[k] = '1';
                }
            } else if(k == '@keyword') {
                val = val.replace(/ +/g, '%');
                newQuery[k] = `%${val}%`;

            } else if(val.indexOf('>= ') === 0) {
                val = val.substr(3);
                newQuery[k] = `${not}GTE(${val})`;

            } else if(val.indexOf('<= ') === 0) {
                val = val.substr(3);
                newQuery[k] = `${not}LTE(${val})`;

            } else if(val.indexOf('> ') === 0) {
                val = val.substr(2);
                newQuery[k] = `${not}GT(${val})`;

            } else if(val.indexOf('< ') === 0) {
                val = val.substr(2);
                newQuery[k] = `${not}LT(${val})`;

            } else if(val.indexOf('bet: ') === 0) {
                val = val.substr(2);
                newQuery[k] = `${not}BET(${val})`;

            } else if(val.indexOf('in: ') === 0) {
                val = val.substr(2);
                newQuery[k] = `${not}IIN(${val})`;

            } else if(val.indexOf('null:') === 0) {
                val = val.substr(2);
                newQuery[k] = `${not}NULL()`;

            } else if(k[0] == '@') {
                newQuery[k] = val;

            } else if(val) {
                if (typeof val == 'string') {
                    if (val) {
                        newQuery[k] = `${not}EQ(${val})`;
                    }
                } else if (val instanceof Array) {
                    const isNum = val.every(function(elem) {
                        return (!isNaN(parseFloat(elem)) && isFinite(elem));
                    });
                    val = val.join(',');
                    if (val) {
                        if (isNum) {
                            newQuery[k] = `${not}IN(${val})`;
                        } else {
                            newQuery[k] = `${not}IIN(${val})`;
                        }
                    }
                }
            }
        }
        return newQuery;
    },

    // string functions 
    ucfirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    pushEntityToList(entity, listName) {
        const lists = useEntitiesLists(); // obtem o storage de listas de entidades
        const listNames = {
            '0': `${entity.__objectType}:draft`,
            '1': `${entity.__objectType}:publish`,
            '-2': `${entity.__objectType}:archived`,
            '-10': `${entity.__objectType}:trash`,
        };

        listName = listName || listNames[`${entity.status}`];

        const list = lists.fetch(listName); // obtém a lista de agentes publicados
        
        if (list) {
            list.push(entity);  // adiciona a entidade na lista
        }
    },

    buildSocialMediaLink(entity, socialMedia){
        if(socialMedia == 'linkedin' ){
            return "https://" + socialMedia + ".com/in/" + entity[socialMedia];
        }
        if(socialMedia == 'spotify' ){
            return "https://open." + socialMedia + ".com/user/" + entity[socialMedia];
        }
        return "https://" + socialMedia + ".com/" + entity[socialMedia];
    },

    cookies: {
        get: function (name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
    
        set: function (key, value, options) {
            options = {...options};
    
            if (value == null) {
                options.expires = -1;
            }
    
            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
                options.expires = options.expires.toUTCString();
            } else {
                options.expires = 'Session';
            }
    
            value = String(value);
    
            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }
    }
}