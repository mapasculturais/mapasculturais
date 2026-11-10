# UPGRADING

Notas de atualização entre versões do MapasCulturais.

## Filtros territoriais unificados nas buscas

- A variável de ambiente `EVENTS_FILTER_STATES_AND_CITIES` deixou de ter efeito. O filtro de Estado/Município agora é unificado e sempre visível nas cinco buscas públicas (agentes, espaços, eventos, projetos e oportunidades). Instalações que usavam a variável não precisam mudar nada: o filtro de eventos continua aparecendo.
- Novas variáveis `SEARCH_FILTER_STATES` e `SEARCH_FILTER_CITIES` (nomes exibidos ao usuário, separados por vírgula) recortam os resultados das buscas por território: com `SEARCH_FILTER_STATES`, o filtro de Estado lista apenas os estados configurados, o de Município lista só cidades desses estados e os resultados já vêm restritos a eles; com as duas, os dois filtros somem e os resultados mostram só as cidades da segunda que pertençam aos estados da primeira. `SEARCH_FILTER_CITIES` só tem efeito combinada com `SEARCH_FILTER_STATES`.
- O recorte organiza o que as páginas de busca exibem; a API pública (`/api/{entidade}/find`) permanece sem restrição.
