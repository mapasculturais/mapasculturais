<script type="text/html" id="agent-search-result-template">
    <div class="search-agent clearfix">
        <img class="search-agent-thumb" src='{{thumbnail}}' alt="{{name}}" />
        <h1>{{name}}</h1>
        <div class="objeto-meta">
            <div>
                <span class="label"><?php \MapasCulturais\i::_e("área de atuação:");?></span>
                {{areas}}
            </div>
            <div><span class="label"><?php \MapasCulturais\i::_e("tipo:");?></span>{{type.name}}</div>
        </div>
    </div>
</script>

<script type="text/html" id="seal-result-template">
    <div class="search-seal clearfix">
        <img class="search-agent-thumb" src='{{thumbnail}}' alt="{{name}}" />
        <h1>{{name}}</h1>
    </div>
</script>
