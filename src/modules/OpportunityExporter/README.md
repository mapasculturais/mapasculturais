# Versionamento do Módulo de Exportação/Importação

## Esquema de Versionamento

O módulo utiliza versionamento semântico no formato `MAJOR.MINOR.PATCH`:

- **MAJOR**: Incrementado quando há quebra de compatibilidade (arquivos exportados deixam de ser importáveis)
- **MINOR**: Incrementado quando novas funcionalidades são adicionadas, mantendo compatibilidade retroativa
- **PATCH**: Incrementado para correções que não afetam funcionalidades

## Regras de Compatibilidade

### Compatibilidade Garantida
- **Importador X.Y.Z** é sempre compatível com arquivos do **Exportador X.\*.***
- **Importador X.Y.Z** importa todas as funcionalidades disponíveis até a versão **X.Y** do exportador

### Limitações de Funcionalidade
- **Importador X.Y1** não importa funcionalidades implementadas no **Exportador X.Y2** (onde Y2 > Y1)
- **Importador X.Y2** importa arquivos do **Exportador X.Y1** (onde Y2 > Y1), mas sem as novas funcionalidades de Y2

### Quebra de Compatibilidade
- A versão **MAJOR** é incrementada quando o importador requer dados que não estão presentes em versões anteriores do exportador
- Arquivos exportados com versão **MAJOR** diferente não são compatíveis

## Exemplos Práticos

- Exportador 2.3.1 → Importador 2.2.0: **Compatível** (funcionalidades da 2.3 não importadas)
- Exportador 2.2.0 → Importador 2.3.1: **Compatível** (sem funcionalidades da 2.3)
- Exportador 3.0.0 → Importador 2.5.0: **Incompatível** (MAJOR diferente)
- Exportador 2.1.0 → Importador 2.1.5: **Totalmente compatível**