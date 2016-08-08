##Configuração dos selos no ambiente do mapas culturais.

Os selos certificadores do mapas culturais é uma forma de reconhecer determinadas entidades no sistema Mapas Culturais por um agente, que pode ser do tipo responsável e/ou coletivo.

Configuração
---------------

No arquivo de configuração do ambiente do Mapas Culturais dever existir uma diretiva que habilita a funcionalidade dos selos certificadores no sistema:
```
'app.enabled.seals'   => true,
```
Funcionalidades
---------------
### Gerenciamento
* Na primeira utilização da  funcionalidade dos selos, a criação e gerenciamento dos selos será feito somente pelos usuários 'admin' e 'superAdmin' do sistema;
* Após a atribuição de um selo a um agente, através da funcionalidade de permissões de agente para editar entidades, permitirá que seja possível atribuir um selo certificador recebido pelo Administrador ou Agente Responsável.

### Entidades
* Estará disponibilizado a atribuição para qualquer entidade existente no sistema Mapas Culturais (Agentes/Espaços/Projetos/Eventos);
* Definição de quais selos serão atribuídos aos agentes (Proponente/Responsável/Coletivo) que serão informados na inscrição de um edital quando a mesma for aprovada.

