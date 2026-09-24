app.component('entity-table', {
    template: $TEMPLATES['entity-table'],
    emits: ['clear-filters', 'remove-filter'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        const messages = useMessages();
        const text = Utils.getTexts('entity-table')
        const global = useGlobalState();
        return { messages, text, hasSlot, global };
    },

    props: {
        type: {
            type: String,
            required: true
        },
        controller: {
            type: String
        },
        limit: {
            type: Number,
            default: 50
        },
        order: {
            type: String,
            default: 'createTimestamp ASC'
        },
        query: {
            type: Object || String,
            default: {}
        },
        watchDebounce: {
            type: Number,
            default: 2000
        },
        headers: {
            type: Array,
            required: true
        },
        required: {
            type: [String, Array],
            default: ''
        },
        visible: {
            type: [String, Array],
            default: ''
        },
        endpoint: {
            type: String,
            default: 'find'
        },
        sortOptions: {
            type: Array,
            default: $MAPAS.config.entityTable.sortOptions
        },
        identifier: {
            type: String,
            required: true,
        },
        filtersDictComplement: {
            type: [Boolean, Object],
            default: false
        },
        select: String,
        showIndex: Boolean,
        allHeaders: Boolean,
        hideFilters: Boolean,
        hideAdvancedFilters: Boolean,
        hideSort: Boolean,
        hideActions: Boolean,
        hideHeader: Boolean,
        rawProcessor: Function,
        /**
         * Entidade dona do contexto da tabela (ex.: opportunity/phase).
         * Quem tem @control nela pode salvar o padrão para os demais usuários.
         * Listagens gerais (agent/space/…) não passam isso — só saasSuperAdmin.
         */
        columnsConfigEntity: {
            type: [Entity, Object],
            default: null,
        },
    },

    created() {
        const required = this.required instanceof Array ? this.required : this.required.split(",").filter(Boolean);
        const localColumnsConfig = this.getLocalColumnsConfig();
        const defaultVisible = this.visible instanceof Array ? this.visible : this.visible.split(",").filter(Boolean);
        const globalConfig = this.globalColumnsConfig;

        this.originalQuery = JSON.parse(JSON.stringify(this.query));
        for (let header of this.columns) {
            header.slug = this.parseSlug(header);
            header.required = required.includes(header.slug);
        }

        this.columns = this.resolveColumnOrder(this.columns, localColumnsConfig, globalConfig);
        const visibleSet = this.resolveVisibleColumns({
            required,
            defaultVisible,
            localColumnsConfig,
            globalConfig
        });

        for (let header of this.columns) {
            header.visible = this.allHeaders ? true : visibleSet.includes(header.slug) || required.includes(header.slug);
        }
    },

    mounted() {
        const searchInput = this.$refs.search;

        if (searchInput) {
            searchInput.addEventListener("input", OnInput, false);
        }

        function OnInput() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + "px";
        }
        this.resize();

        const self = this;
        window.addEventListener('resize', () => {
            self.resize();
        });

        setInterval(() => {
            self.resize();
        },500);
    },

    updated() {
        this.resize();
    },

    data() {
        let fromToStatus = $MAPAS.config.entityTable.fromToStatus
        const id = this.query['@opportunity'] ?? '';
        const sessionTitle = this.controller + ':' + this.endpoint + ':' + id + ':' + this.identifier;
        
        const seen = new Set();
        const columns = this.headers.filter(obj => {
            const key = obj.value || obj.slug;
            return seen.has(key) ? false : seen.add(key);
        })

        const getSeals = $MAPAS.config.entityTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        return {
            fromToStatus,
            apiController: this.controller || this.type,
            entitiesOrder: this.order,
            columns,
            searchText: '',
            timeout: null,
            left: 0,
            width: '100%', //no exemplo está iniciada em 0,
            columnsWidth: {},
            columnsLeft: {},
            columnsRight: {},
            ready: false,
            tableWidth: 'auto',
            headerHeight: 'auto',
            opportunityTypes: $DESCRIPTIONS.opportunity.type.options,
            projectTypes: $DESCRIPTIONS.project.type.options,
            spaceTypes: $DESCRIPTIONS.space.type.options,
            seals,
            sessionTitle,
            opportunityTypes: $DESCRIPTIONS.opportunity.type.options,
            projectTypes: $DESCRIPTIONS.project.type.options,
            spaceTypes: $DESCRIPTIONS.space.type.options,
            seals,
            dragColumnSlug: null,
            dropTargetSlug: null,
            dropPosition: null,
            columnsSearchText: '',
            globalColumnsConfigs: $MAPAS.config.entityTable.columnsConfig?.tables || {},
            _columnDragLastY: null,
            _columnDragRaf: null,
        }
    },

    beforeUnmount() {
        this.stopColumnDragAutoScroll();
    },

    watch: {
        columns: {
            handler(){
                if(this.$refs.contentTable) {
                    this.$refs.contentTable.style.width = 'auto';
                }
                this.headerHeight = 'auto';

                this.resize();
            },
            deep: true
        },
    },

    computed: {
        /**
         * Chave única do padrão da tabela.
         * Ex.: opportunity-findRegistrations-65-registrationsList
         * Listagens gerais: agent-find-agentTable
         */
        globalColumnsKey() {
            const scopeId = this.query?.['@opportunity']
                ?? this.columnsConfigEntity?.id
                ?? '';
            const parts = [
                this.controller || this.type || 'entity',
                this.endpoint || 'find',
                scopeId,
                this.identifier || 'default',
            ];

            return parts
                .map((part) => String(part ?? '').replace(/[^a-zA-Z0-9_-]/g, '-'))
                .filter((part) => part !== '')
                .join('-')
                .replace(/-+/g, '-');
        },

        /**
         * Fallback sem o id da entidade (padrão de sistema daquele tipo de tabela).
         * Ex.: opportunity-findRegistrations-registrationsList
         */
        globalColumnsFallbackKey() {
            const parts = [
                this.controller || this.type || 'entity',
                this.endpoint || 'find',
                this.identifier || 'default',
            ];

            return parts
                .map((part) => String(part ?? '').replace(/[^a-zA-Z0-9_-]/g, '-'))
                .filter((part) => part !== '')
                .join('-')
                .replace(/-+/g, '-');
        },

        canManageColumnsGlobal() {
            const entity = this.columnsConfigEntity;
            if (entity?.currentUserPermissions?.['@control']) {
                return true;
            }
            return !!$MAPAS.config.entityTable.canManageColumnsGlobal;
        },

        visibleColumns() {
            const columns = this.columns.filter((col) => col.visible);
            return columns;
        },

        allHeadersActive() {
            return this.visibleColumns.length == this.columns.length;
        },

        filteredColumns() {
            const search = (this.columnsSearchText || '').toLowerCase().trim();
            if (!search) {
                return this.columns;
            }

            return this.columns.filter(column => {
                const label = (column.text || '').toString().toLowerCase();
                const slug = (column.slug || '').toString().toLowerCase();
                return label.includes(search) || slug.includes(search);
            });
        },

        $description() {
            return $DESCRIPTIONS[this.type];
        },

        globalColumnsConfig() {
            const tables = this.globalColumnsConfigs || {};
            const key = this.globalColumnsKey;
            if (tables[key]) {
                return tables[key];
            }

            // Padrão de sistema (sem id da oportunidade/entidade)
            const fallbackKey = this.globalColumnsFallbackKey;
            if (fallbackKey && fallbackKey !== key && tables[fallbackKey]) {
                return tables[fallbackKey];
            }

            // Compatível com chaves antigas (ex.: opportunity.json / identifier)
            for (const legacy of [this.identifier, this.controller, this.type, this.apiController]) {
                if (legacy && tables[legacy]) {
                    return tables[legacy];
                }
            }

            return null;
        },

        advancedFilters() {
            let filters = {};

            if (this.$description) {
                for (let visibleColumn of this.visibleColumns) {
                    const description = this.$description[visibleColumn.slug];

                    if (description && description.hasOwnProperty('options')) {
                        let isDisabledPerson = description?.registrationFieldConfiguration?.config?.entityField === 'pessoaDeficiente';

                        if (visibleColumn.slug !== 'status' && Object.keys(description.options).length > 0 && !isDisabledPerson) {
                            filters[visibleColumn.slug] = {
                                label: description.label,
                                options: description.options, 
                            }
                        }
                    }
                }
            }

            return this.divideFiltersInColumns(filters);
        },

        hasFilters() {
            const query = JSON.parse(JSON.stringify(this.query));

            delete query['@limit'];
            delete query['@opportunity'];
            delete query['opportunity'];
            delete query['@order'];
            delete query['@select'];
            delete query['@page'];
            delete query['@permission'];
            delete query['@permissions'];
            delete query['action'];
            delete query['userId'];
            delete query['ip'];
            delete query['sessionId'];

            if (this.type == 'agent') {
                delete query['type']
            }

            return Object.keys(query).length > 0;
        },

        appliedFilters() {
            const query = JSON.parse(JSON.stringify(this.query));

            delete query['@limit'];
            delete query['@opportunity'];
            delete query['opportunity'];
            delete query['@order'];
            delete query['@select'];
            delete query['@page'];
            delete query['@permission'];
            delete query['@permissions'];
            delete query['action'];
            delete query['userId'];
            delete query['ip'];
            delete query['sessionId'];

            if (this.type == 'agent') {
                delete query['type']
            }

            let result = [];
            for (let key of Object.keys(query)) {
                if (this.getFilterLabels(key, query[key])) {
                    result = result.concat(this.getFilterLabels(key, query[key]));
                } 
            }

            result = result.filter((actualResult, index) => {
                const duplicityIndex = result.findIndex(compareResult => {
                    return JSON.stringify(compareResult) === JSON.stringify(actualResult);
                });
                return duplicityIndex === index;
            });

            return result;
        },

        spreadsheetQuery() {
            let spreadsheetQuery =  Object.assign({}, this.query);

            spreadsheetQuery['@select'] = this.buildSpreadsheetExportSelect();
            spreadsheetQuery['@order'] = this.entitiesOrder;

            spreadsheetQuery = Object.fromEntries(
                Object.entries(spreadsheetQuery).filter( ([key, value]) => value !== null && value !== undefined )
            );

            return spreadsheetQuery;
        },

        debouncedSearchText: {
            get () {
                return this.searchText;
            },
            set (value) {
                if (this.timeout) {
                    clearTimeout(this.timeout);
                }

                this.searchText = value;
                this.timeout = setTimeout(() => {
                    this.query['@keyword'] = this.searchText;
                    this.$refs.entities.entities.refresh();
                }, 500);
            },
        }
    },

    methods: {
        normalizeHeaders(headers) {
            const seen = new Set();
            return headers.filter(obj => {
                const key = obj.value || obj.slug;
                return seen.has(key) ? false : seen.add(key);
            });
        },

        /**
         * Fragmentos de @select na mesma ordem das colunas visíveis (global + local + arraste).
         * Em inscrições, normaliza owner?. e anexos para o formato esperado pelo export/job.
         */
        buildSpreadsheetExportSelect() {
            const seen = new Set();
            const fragments = [];

            const pushFragment = (token) => {
                const t = (token || '').toString().trim();
                if (!t || seen.has(t)) {
                    return;
                }
                seen.add(t);
                fragments.push(t);
            };

            for (const column of this.visibleColumns) {
                for (const part of this.columnToExportSelectFragments(column)) {
                    pushFragment(part);
                }
            }

            return fragments.join(',');
        },

        columnToExportSelectFragments(column) {
            if (Object.hasOwn(column, 'exportField')) {
                return column.exportField ? [String(column.exportField)] : [];
            }

            const raw = (column.value !== undefined && column.value !== null && String(column.value).trim() !== '')
                ? String(column.value).trim()
                : String(column.slug || '').trim();

            if (!raw) {
                return [];
            }

            if (this.type === 'registration') {
                if (raw === 'attachments') {
                    return ['files'];
                }
                if (raw === 'editable') {
                    return ['editableUntil', 'editSentTimestamp'];
                }
                const ownerOptional = raw.match(/^owner\?\.(.+)$/);
                if (ownerOptional) {
                    return [`owner.{${ownerOptional[1]}}`];
                }
            }

            return [raw];
        },

        getLocalColumnsConfig() {
            const value = localStorage.getItem(this.sessionTitle);
            if (!value) {
                return null;
            }

            try {
                const parsed = JSON.parse(value);
                if (parsed?.userDefined === true && Array.isArray(parsed.visible)) {
                    return {
                        visible: parsed.visible,
                        order: Array.isArray(parsed.order) ? parsed.order : [],
                    };
                }
                localStorage.removeItem(this.sessionTitle);
            } catch (error) {
                // formato legado não representa override explícito do usuário
                localStorage.removeItem(this.sessionTitle);
            }

            return null;
        },

        resolveColumnOrder(headers, localConfig, globalConfig) {
            const normalized = this.normalizeHeaders(headers).map(header => ({ ...header }));
            const currentSlugs = normalized.map(header => this.parseSlug(header));
            const orderedSlugs = [];
            const pushUnique = (slug) => {
                if (currentSlugs.includes(slug) && !orderedSlugs.includes(slug)) {
                    orderedSlugs.push(slug);
                }
            };

            (localConfig?.order || []).forEach(pushUnique);
            (globalConfig?.order || []).forEach(pushUnique);
            currentSlugs.forEach(pushUnique);

            // Avaliacao → Bonus → Pontuacao sempre contiguas na tabela de inscricoes
            if (this.type === 'registration') {
                const scoreGroup = ['consolidatedResult', 'appliedPointReward', 'score'];
                const present = scoreGroup.filter(slug => orderedSlugs.includes(slug));
                if (present.length > 1) {
                    const firstIndex = Math.min(...present.map(slug => orderedSlugs.indexOf(slug)));
                    const withoutGroup = orderedSlugs.filter(slug => !scoreGroup.includes(slug));
                    orderedSlugs.length = 0;
                    orderedSlugs.push(
                        ...withoutGroup.slice(0, firstIndex),
                        ...present,
                        ...withoutGroup.slice(firstIndex)
                    );
                }
            }

            const headerBySlug = {};
            normalized.forEach(header => {
                headerBySlug[this.parseSlug(header)] = header;
            });

            return orderedSlugs.map(slug => headerBySlug[slug]).filter(Boolean);
        },

        resolveVisibleColumns({ required, defaultVisible, localColumnsConfig, globalConfig }) {
            let visible = [];
            if (this.allHeaders) {
                visible = this.columns.map(column => column.slug);
            } else if (Array.isArray(localColumnsConfig?.visible) && localColumnsConfig.visible.length) {
                visible = [...localColumnsConfig.visible];

                // Colunas novas do default que o usuário nunca configurou (não estão
                // no order/visible salvos) — ex.: "score"/Pontuação reintroduzida.
                const known = new Set([
                    ...(localColumnsConfig.visible || []),
                    ...(localColumnsConfig.order || []),
                ]);
                defaultVisible.forEach(slug => {
                    if (slug && !known.has(slug) && !visible.includes(slug)) {
                        visible.push(slug);
                    }
                });
            } else if (Array.isArray(globalConfig?.visible) && globalConfig.visible.length) {
                visible = [...globalConfig.visible];

                const known = new Set([
                    ...(globalConfig.visible || []),
                    ...(globalConfig.order || []),
                    ...(globalConfig.known || []),
                ]);
                defaultVisible.forEach(slug => {
                    if (slug && !known.has(slug) && !visible.includes(slug)) {
                        visible.push(slug);
                    }
                });
            } else {
                visible = [...defaultVisible];
            }

            required.forEach(slug => {
                if (!visible.includes(slug)) {
                    visible.push(slug);
                }
            });

            return visible;
        },

        persistLocalColumnsConfig() {
            const data = {
                order: this.columns.map(column => column.slug),
                visible: this.visibleColumns.map(column => column.slug),
                userDefined: true,
                updatedAt: new Date().toISOString(),
            };
            localStorage.setItem(this.sessionTitle, JSON.stringify(data));
        },

        columnIndex(slug) {
            return this.columns.findIndex(column => column.slug === slug);
        },

        clearColumnDropIndicator() {
            this.dropTargetSlug = null;
            this.dropPosition = null;
        },

        startColumnDragAutoScroll() {
            this.stopColumnDragAutoScroll();

            const tick = () => {
                if (!this.dragColumnSlug) {
                    this._columnDragRaf = null;
                    return;
                }

                const list = this.$refs.columnsList;
                if (list && this._columnDragLastY != null) {
                    const rect = list.getBoundingClientRect();
                    const threshold = 48;
                    const maxSpeed = 22;
                    const y = this._columnDragLastY;

                    if (y < rect.top + threshold) {
                        const intensity = Math.min(1, (rect.top + threshold - y) / threshold);
                        list.scrollTop -= Math.ceil(maxSpeed * intensity);
                    } else if (y > rect.bottom - threshold) {
                        const intensity = Math.min(1, (y - (rect.bottom - threshold)) / threshold);
                        list.scrollTop += Math.ceil(maxSpeed * intensity);
                    }
                }

                this._columnDragRaf = requestAnimationFrame(tick);
            };

            this._columnDragRaf = requestAnimationFrame(tick);
        },

        stopColumnDragAutoScroll() {
            if (this._columnDragRaf) {
                cancelAnimationFrame(this._columnDragRaf);
                this._columnDragRaf = null;
            }
            this._columnDragLastY = null;
        },

        onColumnDragStart(event, slug) {
            if (!this.showIndex) {
                return;
            }
            this.dragColumnSlug = slug;
            this.clearColumnDropIndicator();
            this._columnDragLastY = event.clientY;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', slug);
            this.startColumnDragAutoScroll();
        },

        onColumnDragOver(event, targetSlug = null) {
            if (!this.showIndex || !this.dragColumnSlug) {
                return;
            }
            event.preventDefault();
            this._columnDragLastY = event.clientY;
            event.dataTransfer.dropEffect = 'move';

            if (!targetSlug || targetSlug === this.dragColumnSlug) {
                this.clearColumnDropIndicator();
                return;
            }

            const row = event.currentTarget;
            if (!row?.getBoundingClientRect) {
                return;
            }

            const rect = row.getBoundingClientRect();
            const position = event.clientY < rect.top + (rect.height / 2) ? 'before' : 'after';
            this.dropTargetSlug = targetSlug;
            this.dropPosition = position;
        },

        onColumnDrop(event, targetSlug) {
            if (!this.showIndex) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();

            const sourceSlug = this.dragColumnSlug || event.dataTransfer.getData('text/plain');
            const position = (this.dropTargetSlug === targetSlug && this.dropPosition)
                ? this.dropPosition
                : 'before';

            this.onColumnDragEnd();

            if (!sourceSlug || !targetSlug || sourceSlug === targetSlug) {
                return;
            }

            const sourceIndex = this.columnIndex(sourceSlug);
            let targetIndex = this.columnIndex(targetSlug);
            if (sourceIndex < 0 || targetIndex < 0) {
                return;
            }

            if (position === 'after') {
                targetIndex += 1;
            }

            const [sourceColumn] = this.columns.splice(sourceIndex, 1);
            if (sourceIndex < targetIndex) {
                targetIndex -= 1;
            }
            this.columns.splice(targetIndex, 0, sourceColumn);
            this.persistLocalColumnsConfig();
        },

        onColumnDragEnd() {
            this.dragColumnSlug = null;
            this.clearColumnDropIndicator();
            this.stopColumnDragAutoScroll();
        },

        async saveGlobalColumnsConfig() {
            if (!this.canManageColumnsGlobal) {
                return;
            }

            const apiController = this.controller || this.type;
            const api = new API(apiController);
            const entity = this.columnsConfigEntity;
            const payload = {
                tableKey: this.globalColumnsKey,
                entityType: entity?.__objectType || null,
                entityId: entity?.id || null,
                order: this.columns.map(column => column.slug),
                visible: this.visibleColumns.map(column => column.slug),
                required: this.columns.filter(column => column.required).map(column => column.slug),
                known: this.columns.map(column => column.slug),
            };

            const response = await api.POST('saveTableColumnsConfig', payload);
            if (!response.ok) {
                this.messages.error(this.text('erro ao salvar padrao global'));
                return;
            }

            const data = await response.json();
            this.globalColumnsConfigs[this.globalColumnsKey] = data.config || {};
            this.messages.success(this.text('padrao global salvo'));
        },

        keyword(entities) {
            entities.refresh()
        },
        getFilterLabels(prop, value) {
            const propLabels = {
                '@keyword': __('palavras-chave', 'entity-table'),
                '@date': __('data', 'entity-table'),
                '@pending': __('pendente', 'entity-table'),
                'idoso': __('pessoa idosa', 'entity-table'),
                'acessibilidade': __('acessibilidade', 'entity-table'),
            };

            if (prop === '@verified' && value === 1) {
                return null;
            }

            if (propLabels[prop] && (value != '' || prop === '@pending')) {
                return [{ prop, value: prop === '@pending' ? 'null' : value, label: propLabels[prop] }];
            }

            const typeMappings = {
                'opportunity': this.opportunityTypes,
                'project': this.projectTypes,
                'space': this.spaceTypes,
                '@seals': this.seals
            };

            if (typeMappings[prop] || (prop === 'type' && typeMappings[this.type])) {
                let values = this.getFilterValues(value);
                if (values) {
                    let typeKey = prop === 'type' ? this.type : prop;
                    let typeMap = typeMappings[typeKey];
                    return values.map(val => ({ prop, value: val, label: typeMap[val] || val }));
                }
            }

            let values = this.getFilterValues(value);
            if (values) {
                if (prop == 'status' || prop == '@pending' || prop == '@filterStatus' || prop == '@evaluationId') {
                    let _filtersDict = {
                        '0': __('rascunhos', 'entity-table'),
                        '1': __('publicadas', 'entity-table'),
                        '-10': __('lixeira', 'entity-table'),
                        '-1': __('arquivadas', 'entity-table'),
                    }

                    if(this.type == 'registration') {
                        _filtersDict = {
                            '0': __('rascunhos', 'entity-table'),
                            '1': __('pendentes', 'entity-table'),
                            '2': __('invalidas', 'entity-table'),
                            '3': __('nao selecionadas', 'entity-table'),
                            '8': __('suplentes', 'entity-table'),
                            '10': __('selecionadas', 'entity-table'),
                        }
                    }

                    if(this.type == 'payment') {
                        _filtersDict = {
                            '0': __('pendente', 'entity-table'),
                            '1': __('em processo', 'entity-table'),
                            '2': __('falha', 'entity-table'),
                            '3': __('exportado', 'entity-table'),
                            '8': __('disponivel', 'entity-table'),
                            '10': __('pago', 'entity-table'),
                        }
                    }

                    if (this.endpoint == 'findEvaluations') {
                        _filtersDict = {
                            'all': __('Todas', 'entity-table'),
                            'pending': __('Avaliações pendente', 'entity-table'),
                            '0': __('Avaliações iniciadas', 'entity-table'),
                            '1': __('Avaliações concluídas', 'entity-table'),
                            '2': __('Avaliações enviadas', 'entity-table'),
                        }
                    }

                    filtersDict = _filtersDict;
                    if(this.filtersDictComplement && this.filtersDictComplement.type == this.type || this.filtersDictComplement.type == this.endpoint) {
                        filtersDict = {
                            ..._filtersDict,
                            ...this.filtersDictComplement.dict
                        }
                    }

                    return values.map((value) => { 
                        return {prop, value, label: filtersDict[value]} 
                    });
                }

                const fieldDescription = this.$description[prop];
                if (fieldDescription?.field_type === 'select') {
                    return values.map(val => ({ prop, value: val, label: fieldDescription.options[val] || val }));
                } else {
                    return values.map(val => {
                        let label = typeof val === 'string' ? val.replace(/(\\)/g, '') : val;

                        if(this.filtersDictComplement && this.filtersDictComplement?.type == this.type) {
                            label = this.filtersDictComplement[label] || label;
                        }

                        return { prop, value: val, label };
                    });
                }
            }

            return null;
        },

        getFilterValues(value) {
            // Exemplos: 
            //      EQ(10), EQ(preto), IN(8, 10), IN(preto, pardo)
            let values = /(EQ|IN|IIN|GT|GTE|LT|LTE)\((.+)\)/.exec(value); 
            let exclude = ['GT','GTE','LT','LTE'];

            if (values) {
                const operator = values[1];
                const _values = values[2];

                if (exclude.includes(operator)) {
                    return null;
                }

                if (operator == '@pending') {
                    return 'null';
                }

                if (_values) {
                    if (operator == 'IN' || operator == 'IIN') {
                        let commaValues = _values.startsWith(',') ? _values.slice(1) : _values;
                        values = commaValues.replace(/([^\\]),/g, '$1%break%');
                    } else {
                        values = _values;
                    }

                    return values.split('%break%').filter(value => value.trim());
                } else {
                    return null;
                }
            } else {
                return null;
            }
        },

        parseSlug(header) {
            if (header.slug) {
                return header.slug;
            }
            return header.value;
        },

        getEntityData(obj, prop) {
            try {
                let val = eval(`obj.${prop}`);

                const description = this.$description[prop];

                if(description) {
                    if(description.options) {
                        if(description.options[val]) {
                            return description.options[val];
                        }
                    }
                    switch (description.type) {
                        case 'multiselect':
                        case 'array':
                            if (Array.isArray(val)) {
                                val = val.filter(item => item !== "null" && item !== "").join(', ');
                            } else {
                                val = null;
                            }
                            break;
                        case 'links':
                            var hasVal = val != null ?  (val !== '"null"' || val !== 'null' ? true : false) : false ;
                            if (hasVal && !Array.isArray(val)) {
                                
                                const parsed = val !== '' ? JSON.parse(val) : null ;
                                if (parsed && parsed !== 'null' && Array.isArray(parsed)) {
                                    val = parsed.map(item => `${item.title}: ${item.value},`).join('\n');
                                } else {
                                    val = null;
                                }
                            }
                            
                            if (hasVal &&  Array.isArray(val)) {
                                val = val.map(item => `${item.title}: ${item.value},`).join('\n');
                            }

                            val = null;
                            break;
                        case 'point':
                            val = val ? `${val.lat}, ${val.lng}` : null
                            break;
                        case 'addresses':
                            if(val === null || val === undefined || val === 'null' || val === '') {
                                val = null;
                            }
                            if (typeof val === 'string') {
                                try { val = JSON.parse(val); } catch { val = null; }
                            }
                            
                            if (Array.isArray(val)) {
                                
                                val = val.map(item =>
                                    `${item.nome || ''}: ${item.logradouro || ''}, ${item.numero || ''}, ${item.bairro || ''}, ${item.cidade || ''}, ${item.complemento || ''} - ${item.estado || ''}, ${item.cep || ''}`
                                ).join(',');
                            } else {
                                val = null;
                            }
                            break;
                        case 'location':
                            val = val?.address ? val.address : val?.endereco ? val.endereco : null;
                            break;
                        case 'boolean':
                            if (val === true || val === 'true' || val === 1 || val === '1') {
                                val = this.text('sim');
                            } else if (val === false || val === 'false' || val === 0 || val === '0') {
                                val = this.text('nao');
                            } else {
                                val = '';
                            }
                            break;
                        
                    }
                }

                if(prop == 'singleUrl' ) {
                    val = `<a href="${val}">${val}</a>`;
                }

                if(val && prop == 'seals[0]?.createTimestamp' ) {
                    let _val = new McDate(val.date);
                    val = _val.date('numeric year') + ' ' + _val.time('2-digit');
                }

                if(val instanceof McDate) {
                    if(description.type == 'datetime') {
                        val = val.date('numeric year') + ' ' + val.time('2-digit');
                    } else {
                        val = val.date('numeric year');
                    }
                }

                if (prop == 'type') {
                    val = val.name
                }

                if (prop == 'public') {
                    val = val ? this.text('sim') : this.text('nao')
                }

                if (prop == 'status') {
                    let type = this.type.charAt(0).toUpperCase() + this.type.slice(1);
                    val = this.fromToStatus[type]?.[val] || val;
                }

                return val;
            } catch (error) {
                console.error("erro ao carregar prop => ", prop);
            }
        },

        resetHeaders() {
            const visible = this.visible instanceof Array ? this.visible : this.visible.split(",");
            const required = this.required instanceof Array ? this.required : this.required.split(",");

            for (let header of this.columns) {
                if(visible.includes(header.slug) || required.includes(header.slug)) {
                    header.visible = true;
                } else {
                    header.visible = false;
                }
            }
        },

        toggleHeaders(event) {
            const field = event.target.value;
            let column = null;
            let changed = false;

            for (let header of this.columns) {
                if (header.slug == field) {
                    column = header;
                    if (header.required) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.messages.error(this.text('item obrigatório') + ' ' + header.text);
                    } else {
                        header.visible = !header.visible;
                        changed = true;
                    }
                }
            }

            if (column && !column.visible) {
                delete this.query[field];
            }

            if (changed) {
                this.persistLocalColumnsConfig();
            }
        },

        showAllHeaders() {
            if (!this.$refs.allHeaders.checked) {
                this.resetHeaders();
            } else {
                for (let header of this.columns) {
                    header.visible = true;
                }
            }
            this.persistLocalColumnsConfig();
        },

        clearFilters(entities) {
            this.searchText = '';
            for (let prop in this.query) {
                if (this.originalQuery[prop]) {
                    this.query[prop] = this.originalQuery[prop];
                } else {
                    delete this.query[prop];
                }
            }

            this.$emit('clear-filters', entities);
        },

        removeFilter(filter, entities) {
            const _values = /(EQ|IN|GT|GTE|LT|LTE)\(([^\)]+,?)+\)/.exec(this.query[filter.prop]);

            if (filter.prop == '@keyword') {
                delete this.query[filter.prop];
                this.searchText = '';
            } else if (filter.prop == '@pending'){ 
                delete this.query[filter.prop];
            } else {
                if (_values) {
                    let operator = _values[1];
                    let values = _values[2];

                    if (operator == 'IN') {
                        values = values.replace(/([^\\]),/g, '$1%break%');
                        values = values.split('%break%').filter(value => value.trim());

                        if (values.length > 1) {
                            for (let index of Object.keys(values)) {
                                if (values[index] == filter.value) {
                                    values.splice(index, 1);
                                }
                            }

                            this.query[filter.prop] = `${operator}(${values.toString()})`;
                        } else {
                            delete this.query[filter.prop];
                        }
                    } else {
                        delete this.query[filter.prop];
                    }
                }
            }

            entities.refresh();
            this.$emit('remove-filter', filter);
        },

        toggleAdvancedFilter(fieldName, option) {
            const currentValues = this.getFilterValues(this.query[fieldName] ?? '') || [];

            if (currentValues.includes(option)) {
                currentValues.splice(currentValues.indexOf(option), 1);    
            } else {
                currentValues.push(option);
            }

            if (currentValues.length > 0) {
                this.query[fieldName] = `IN(${currentValues.toString()})`;
            } else {
                delete this.query[fieldName];
            }

            this.$refs.entities.entities.refresh(this.watchDebounce);
        },

        advancedFilterChecked(fieldName, option) { 
            const currentValues = this.getFilterValues(this.query[fieldName] ?? '') || [];

            return currentValues.includes(option);
        },

        scroll(event) {
            const scrollLeft = event.target.scrollLeft; 
            const headerWrapper = this.$refs.headerWrapper; 
            const contentWrapper = this.$refs.contentWrapper;
            const scrollWrapper = this.$refs.scrollWrapper;
            headerWrapper.scrollLeft = scrollLeft; 
            contentWrapper.scrollLeft = scrollLeft;
            scrollWrapper.scrollLeft = scrollLeft;
        },

        setColumnWidth(slug) {
            const col = this.$refs['column-' + slug]?.[0] ?? this.$refs['column-' + slug] ?? null;
            if(col && !(col instanceof Array)) {
                const rect = col.getBoundingClientRect();
                this.columnsLeft[slug] = this.totalWidth + 'px';
                this.columnsWidth[slug] = rect.width + 'px';
                this.totalWidth += rect.width;
                this.columnsRight[slug] = (parseFloat(this.width) - this.totalWidth) + 'px';
                this._ready = true;
            }
        },

        setColumnRight(slug) {
            const col = this.$refs['column-' + slug]?.[0] ?? this.$refs['column-' + slug] ?? null;
            if(col) {
                this.columnsRight[slug] = this.totalWidth + 'px';
                this.totalWidth -= col.clientWidth;
                this._ready = true;
            }
        },

        calcResize() {
            globalThis.$table = this.$refs.contentTable;

            if (this.$refs.contentTable) {
                this.width = this.$refs.contentTable.clientWidth + 'px';
            }

            this.$nextTick(() => {
                this._ready = false;

                if (this.$refs.fakeHeaderTable && this.$refs.contentTable) {
                    const contentWidth = this.$refs.contentTable.offsetWidth + 'px';
                    
                    this.$refs.fakeHeaderTable.style.display = 'block'; 
                    this.$refs.fakeHeaderTable.style.width = contentWidth;
                }

                this.totalWidth = 0;
                this.setColumnWidth('-index');
                for(let column of this.visibleColumns) {
                    this.setColumnWidth(column.slug)
                }

                this.totalWidth = 0;
                for(let i = this.visibleColumns.length - 1; i >= 0; i--) {
                    const column = this.visibleColumns[i];
                    this.setColumnRight(column.slug);
                }

                if(this._ready && this.$refs.headerTable){
                    this.ready = this._ready;
                    this.headerHeight = this.$refs.headerTable.offsetHeight + 20;
                }
            });
        },

        resize() {
            const self = this;
            self.$nextTick(() => {
                if(self.$refs.contentTable) {
                    while(self.$refs.headerTable.offsetHeight > 175) {
                        self.$refs.contentTable.style.width = (self.$refs.contentTable.offsetWidth * 1.1) + 'px';
                    }
                }
                self.calcResize();
            });
        },

        getOffsetLeft(slug) {
            return this.columnsLeft[slug] ?? null;
        },

        headerStyle(column, header = false) {
            const width = header ? this.columnsWidth[column.slug] || '' : column.width || this.columnsWidth[column.slug] || '';
            const style = {
                width, 
                minHeight: this.headerHeight + 'px'
            };

            if(column.sticky) {
                style.left = this.columnsLeft[column.slug] ?? ''
            }

            if(column.stickyRight) {
                style.right = this.columnsRight[column.slug] ?? ''
            }

            return style;
        },

        optionValue(option, key) {
            if('string' == typeof key) {
                return key;
            } else {
                let _option = option.split(':');
                return _option[0];
            }
        },

        optionLabel(option) {
            let _option = option.split(':');
            return _option.length > 1 ? _option[1] : _option[0];
        },

        divideFiltersInColumns(campos, numGrupos = 4) {
            const keys = Object.keys(campos);
            const totalCampos = keys.length;
            const camposPorGrupo = Math.ceil(totalCampos / numGrupos);
            const grupos = {};

            for (let i = 0; i < numGrupos; i++) {
                const start = i * camposPorGrupo;
                const sliceKeys = keys.slice(start, start + camposPorGrupo);
                grupos[`grupo${i + 1}`] = {};
                sliceKeys.forEach(key => {
                grupos[`grupo${i + 1}`][key] = campos[key];
                });
            }

            return grupos;
        },
    }
});
