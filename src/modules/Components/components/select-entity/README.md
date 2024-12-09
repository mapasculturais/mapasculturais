# Componente `<select-entity>`
O componente `select-entity` é utilizado para selecionar entidades específicas (agentes, espaços, eventos, projetos ou oportunidades) e permite a criação de novas entidades conforme necessário.

### Eventos
- **select** - Emitido quando uma entidade é selecionada pelo usuário. O evento é emitido com a entidade selecionada como parâmetro.
- **fetch** - Emitido quando a lista de entidades é atualizada com novos resultados. O evento é emitido com a lista de entidades recuperadas como parâmetro.

## Propriedades
- *type **String*** - Define o tipo de entidade que será selecionada (`agent`, `space`, `event`, `project`, `opportunity`).
- *Select **String*** - Campos selecionáveis da entidade (`id`,`name`,`files.avatar`).
- *Query **Object*** - Objeto de consulta para filtrar as entidades.
- *Permissions **String*** - Permissões de acesso às entidades.
- *Limit **Number*** - Limite de resultados a serem mostrados.
- *CreateNew **Boolean*** - Define se é possível criar uma nova entidade.
- *Scope **String*** - Escopo das entidades.
- *Openside **String*** - Lado de abertura do popover.
- *ButtonLabel **String*** - Texto do botão de ação principal.
- *ButtonClasses **String*** - Classes adicionais para o botão de ação principal.
- *Classes **String*** - Classes adicionais para o componente.

## Slots
- **button**: Slot para substituir o botão de ação principal do popover.
- **selected**: Slot para exibir conteúdo selecionado antes da lista de resultados.

### Importando componente
```PHP
<?php 
$this->import('select-entity');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<select-entity type="event"></select-entity>

<!-- utilizaçao com Slot para o Botão de Ação -->
 <select-entity type="space">
    <template #button="{ toggle }">
        <button @click="toggle()">{{ buttonText }}</button>
    </template>
</select-entity>

<!-- utilizaçao com Evento para Salvar a Entidade Selecionada -->
<select-entity type="agent" @select="saveAgent"></select-entity>
 ```