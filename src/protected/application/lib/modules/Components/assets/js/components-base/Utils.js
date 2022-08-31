globalThis.__ = (key, componentName) => {
    const dict = Utils.getTexts(componentName);
    return dict(key);
}

globalThis.Utils = {
    getTexts(componentName) {
        const texts = $MAPAS.gettext?.[`component:${componentName}`] || {};
        return (key) => {
            return texts[key];
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

    parsePseudoQuery (pseudoQuery) {
        const newQuery = {};
        for(let k in pseudoQuery) {
            let val = pseudoQuery[k];
            let not = '';
            if(typeof val == 'string' && val.indexOf('!') === 0) {
                not = '!';
                val = val.substr(1);
            }

            if(k == '@verified') {
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
                    val = val.join(',');
                    if (val) {
                        newQuery[k] = `${not}IIN(${val})`;
                    }
                }
            }
        }
        return newQuery;
    }
}