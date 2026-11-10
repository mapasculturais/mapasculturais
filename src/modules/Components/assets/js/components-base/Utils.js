globalThis.__ = (key, componentName, replacements) => {
    const dict = Utils.getTexts(componentName);
    return dict(key, replacements);
}

globalThis.Utils = {
    _uidCounter: 0,

    uid() {
        return `uid-${++this._uidCounter}-${Date.now().toString(36)}`;
    },

    getTexts(componentName) {
        const texts = $MAPAS.gettext?.[`component:${componentName}`] || {};
        return (key, replacements) => {
            const text = texts[key];

            if (!text) {
                console.error(`TRADUÇÃO FALTANDO "${key}" do componente "${componentName}`);
            }
        
            let result = text || key;

            if (replacements) {
                for (let key in replacements) {
                    result = result.replaceAll('{' + key + '}' , replacements[key]);
                }
            }

            return result;
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

    isEvaluationFieldVisible(fields, key) {
        const value = fields?.[key];
        return value === true || value === 'true';
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

    /**
     * Recorte territorial da instalação (ADR 0019): merge forçado das chaves
     * `En_Estado`/`En_Municipio` na query JÁ PARSEADA por parsePseudoQuery —
     * nunca como valor default da pseudoQuery, para que o "Limpar todos os
     * filtros" (que zera as chaves) não destrua a restrição: ela é reaplicada
     * a cada fetch, aqui.
     *
     * Lê `$MAPAS.config.searchTerritorialFilters` (publicado em
     * search/init.php): ausente ou sem valores forçados → query intacta
     * (retrocompatível com instalações sem as variáveis).
     *
     * `entityType`: 'agent'|'space'|'project'|'opportunity' filtram a própria
     * entidade (chaves `En_*`); 'event' filtra pelo endereço do espaço da
     * ocorrência (chaves `space:En_*`, resolvidas pela correlação manual dos
     * controllers de evento/espaço).
     *
     * Se o usuário já escolheu valores para a chave, aplica a INTERSEÇÃO com o
     * recorte (a UI já pré-filtra o dataset; a interseção é defesa contra
     * manipulação). Interseção vazia → resultado vazio garantido (`id=IN(-1)`).
     * Os valores são (re)escritos na forma parseada `IIN(...)` porque o ponto
     * de aplicação é pós-parsePseudoQuery (ADR 0019, decisão 1 e 3).
     */
    applyTerritorialRestrictions(query, entityType) {
        const filters = $MAPAS.config?.searchTerritorialFilters;
        const statesForced = filters?.statesForced || [];
        const citiesForced = filters?.citiesForced || [];

        if (!statesForced.length && !citiesForced.length) {
            return query;
        }

        const prefix = entityType === 'event' ? 'space:' : '';
        const stateKey = `${prefix}En_Estado`;
        const cityKey = `${prefix}En_Municipio`;

        let emptyResult = false;

        if (statesForced.length) {
            const states = this.intersectTerritorialValues(query[stateKey], statesForced);
            if (states.length) {
                query[stateKey] = `IIN(${states.join(',')})`;
            } else {
                emptyResult = true;
            }
        }

        if (!emptyResult && citiesForced.length) {
            const cities = this.intersectTerritorialValues(query[cityKey], citiesForced);
            if (cities.length) {
                query[cityKey] = `IIN(${cities.join(',')})`;
            } else {
                emptyResult = true;
            }
        }

        if (emptyResult) {
            // escolha inteiramente fora do recorte: nenhum resultado pode abrir
            delete query[stateKey];
            delete query[cityKey];
            query['id'] = 'IN(-1)';
        }

        return query;
    },

    /**
     * Interseção entre a escolha do usuário e os valores forçados da instalação.
     * `parsedValue` vem na forma produzida por parsePseudoQuery (`IIN(a,b)`,
     * `IN(a,b)`, `EQ(a)`, com prefixo `!` para negação); sem escolha do usuário
     * retorna o recorte completo. Comparação case/accent-insensitive espelhando
     * o operador IIN da DSL ApiQuery (ADR 0004); o retorno usa a forma canônica
     * dos valores do recorte. Limitação herdada do parsePseudoQuery: vírgula é
     * o separador de itens (nomes de UF/município do dataset BR não contêm vírgula).
     */
    intersectTerritorialValues(parsedValue, forcedValues) {
        const normalize = (value) => String(value).trim().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        const canonical = new Map();
        forcedValues.forEach((forced) => canonical.set(normalize(forced), String(forced).trim()));

        if (parsedValue === undefined || parsedValue === null || parsedValue === '') {
            return [...canonical.values()];
        }

        let raw = String(parsedValue).trim();
        let negated = false;
        if (raw[0] === '!') {
            negated = true;
            raw = raw.slice(1);
        }

        // extrai os itens do operador que o parsePseudoQuery emitiu para a chave
        const matched = raw.match(/^(?:IIN|IN|EQ)\((.*)\)$/);
        const values = (matched ? matched[1] : raw)
            .split(',')
            .map((value) => value.trim())
            .filter((value) => value !== '');

        if (!values.length) {
            return [...canonical.values()];
        }

        if (negated) {
            // escolha negada: interseção = recorte MENOS os valores negados
            values.forEach((value) => canonical.delete(normalize(value)));
            return [...canonical.values()];
        }

        const intersection = [];
        values.forEach((value) => {
            const canonicalValue = canonical.get(normalize(value));
            if (canonicalValue !== undefined && !intersection.includes(canonicalValue)) {
                intersection.push(canonicalValue);
            }
        });
        return intersection;
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
            const value = entity[socialMedia];
            if (!value) {
                return '';
            }
            // Suporta formato "type:id" ou apenas "id" (assume user)
            const parts = value.split(':');
            if (parts.length === 2) {
                const [type, id] = parts;
                return `https://open.spotify.com/${type}/${id}`;
            }
            // Assume user se não tiver tipo
            return `https://open.spotify.com/user/${value}`;
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