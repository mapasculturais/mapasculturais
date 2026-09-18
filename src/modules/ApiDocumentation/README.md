# ApiDocumentation

Publica a documentação navegável da API pública em `/api/documentation`, usando Swagger UI.

## Rotas

| rota | resposta |
|---|---|
| `GET /api/documentation` | página do Swagger UI |
| `GET /api/documentation/openapi` | a especificação, em YAML |

Ambas são anônimas, como o restante de `/api`.

A página é uma view comum do projeto (`views/documentation/index.php`), renderizada com `partial()` para sair sem o layout do tema — o Swagger UI monta a própria interface e não convive com o cabeçalho, o rodapé e o bundle Vue.

## Especificação

`openapi.yaml` é estático e mantido à mão. Ao mudar a API pública — endpoint, parâmetro, comportamento de filtro — atualize esse arquivo no mesmo commit.

Duas decisões de conteúdo, tomadas para não expor a topologia da instalação:

- o único `servers` é a URL relativa `/`, então "Try it out" sempre bate no host que serviu a página, e nenhum domínio de produção fica listado;
- `/api/subsite/find` **não é documentada**, embora responda. Quem precisa do id de um site pede `subsite` no `@select` de uma consulta feita naquele host.

O guia em prosa e a collection Postman correspondentes estão fora deste módulo, em `tasks/api-publica/documentacao/`.

## Swagger UI

`assets/swagger-ui/` é cópia de [`swagger-ui-dist`](https://www.npmjs.com/package/swagger-ui-dist) **5.32.15**, Apache-2.0 (`assets/swagger-ui/LICENSE`). Só os dois arquivos usados pela página, com a referência ao sourcemap removida do CSS para não gerar 404.

Para atualizar:

```bash
V=<nova-versão>
D=src/modules/ApiDocumentation/assets/swagger-ui
for f in swagger-ui.css swagger-ui-bundle.js LICENSE; do
  curl -sL -o "$D/$f" "https://cdn.jsdelivr.net/npm/swagger-ui-dist@$V/$f"
done
sed -i '' 's|/\*# sourceMappingURL=swagger-ui.css.map\*/||' "$D/swagger-ui.css"
```

Depois atualize a versão citada acima.
