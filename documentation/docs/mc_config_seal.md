# Selos no ambiente do mapas culturais.

Os selos certificadores do mapas culturais é uma forma de reconhecer determinadas entidades no sistema Mapas Culturais por um agente, que pode ser do tipo responsável e/ou coletivo.

## Ativando a Funcionalidade

No arquivo de configuração `config.php` deve existir uma diretiva que habilita a funcionalidade dos selos certificadores no sistema:
```
'app.enabled.seals'   => true,
```

## Primeira utilização

Na primeira vez que o arquivo deploy.sh é executado, são executadas duas tarefas

- A Criação de um Selo padrão, que assume-se como "Selo Verificado"
- Todas as Entidades que eram marcadas como Verificadas, recebem o Selo que foi criado

O campo de pesquisa "Resultados Verificados" pesquisa agora todos os resultados que tenham esse selo que foi criado.


## Selos Verificados

Os selos que são considerados "Verificados" são ativados no arquivo `config.php` no parâmetro `app.verifiedSealsIds`, que recebe os IDs dos selos Verificados. O valor desse parâmetro deve ser um array, podendo assim mais de um selo ser considerado como verificado.

Por padrão, o único considerado verificado é o Selo "1", onde a configuração é assim:

```
'app.verifiedSealsIds' => [1],
```

Para habilitar mais selos como verificados, o basta colocar mais número. Esse é um exemplo onde os Selos verificados são os de IDs 5, 8 e 13:
```
'app.verifiedSealsIds' => [5, 8, 13],
```

## Atribuição de Selos

É possível atribuir Selos para as quatro Entidades do Mapas: Agentes, Espaços, Eventos e Projetos.

Dentro dos Projetos com Inscrições Abertas (Editais), existe uma área chamada "Selos Certificadores". Nessa parte é possível atribuir o Selos automaticamente para Agentes inscritos que forem aprovados. A atribuição dos Selos aos Agentes ocorre no momento da publicação do resultado.
