# Taxonomies

Taxonomies classify entities by shared vocabulary terms. Each taxonomy is defined by a `slug` and applies to specific entity types.

## Overview

| Taxonomy | Slug | Entity Types | Term Count |
|----------|------|-------------|------------|
| Tags | `tag` | Agent, Space, Event, Project, Opportunity | Free-form |
| Areas of Activity | `area` | Agent, Space, Opportunity | ~380 |
| Languages | `linguagem` | Agent, Space, Event, Opportunity | 18 |
| Professional Roles | `funcao` | Agent | ~365 |
| Ethnicities | `etnia` | Agent | ~245 |

---

## tag

Free-form user-defined tags. Available on all entity types.

**Endpoint:** `GET /api/taxonomy/find?slug=tag`

```json
{
  "slug": "tag",
  "term": "cultura-negritude"
}
```

---

## area

Areas of cultural activity. Used to classify Spaces, Agents, and Opportunities.

**Entity types:** Space, Agent, Opportunity

### Selected Terms

| Term |
|------|
| Arquitetura e Urbanismo |
| Artes Cênicas |
| Artes Circenses |
| Artes Visuais |
| Audiovisual |
| Cinema |
| Comunicação |
| Cultura Acessível |
| Cultura Afro-brasileira |
| Cultura Digital |
| Cultura Indígena |
| Cultura Popular |
| Dança |
| Design |
| Documentação e Memória |
| Educação |
| Editoração |
| Esportes |
| Estudos e Pesquisas |
| Festividades e Celebrações |
| Fotografia |
| Gastronomia |
| Grafite e Pixação |
| Hip Hop |
| História |
| Humanidades |
| Literatura |
| Meio Ambiente |
| Música |
| Moda |
| Museus e Acervos |
| Patrimônio Cultural |
| Patrimônio Histórico |
| Patrimônio Imaterial |
| Patrimônio Material |
| Produção Cultural |
| Produção Fonográfica |
| Produção Literária |
| Produção Visual |
| Radio e TV |
| Rádio e TV |
| Religiosidade |
| Saúde |
| Teatro |
| Turismo Cultural |

> Full taxonomy contains ~380 terms. Use `GET /api/taxonomy/find?slug=area` to retrieve all.

---

## linguagem

Cultural languages / artistic disciplines.

**Entity types:** Agent, Space, Event, Opportunity

### All Terms

| Term |
|------|
| Artes Circenses |
| Artes Visuais |
| Audiovisual |
| Cultura Digital |
| Dança |
| Fotografia |
| Gastronomia |
| Grafite |
| Hip Hop |
| Interdisciplinar |
| Literatura |
| Música |
| Moda |
| Patrimônio |
| Teatro |
| Turismo Cultural |

---

## funcao

Professional roles and functions within the cultural sector.

**Entity types:** Agent

### Selected Terms

| Term |
|------|
| Ação Cultural |
| Administrador |
| Antropólogo |
| Arquivista |
| Arte-educador |
| Artista Gráfico |
| Artista Plástico |
| Assistente de Produção |
| Assistente de Direção |
| Assistente de Câmera |
| Ator / Atriz |
| Bibliotecário |
| Câmera |
| Cantor / Compositor |
| Cartazista |
| Cenógrafo |
| Comissário de Exposição |
| Conservador / Restaurador |
| Contrarregra |
| Coreógrafo |
| Curador |
| Crítico Cultural |
| Dançarino / Bailarino |
| Desenhista |
| Diretor de Arte |
| Diretor de Fotografia |
| Diretor de Produção |
| Diretor de Som |
| Editor |
| Figurinista |
| Filósofo |
| Fonógrafo |
| Fotógrafo |
| Fraseólogo |
| Gesseiro |
| Historiador |
| Iluminador |
| Instrumentista |
| Intérprete de Libras |
| Jornalista |
| Maquiador |
| Mestre de Obra |
| Museólogo |
| Músico |
| Narrador |
| Palhaço |
| Produtor Cultural |
| Programador Visual |
| Radialista |
| Realizador |
| Regente |
| Roteirista |
| Sociólogo |
| Sonoplasta |
| Tradutor / Intérprete |
| Turismólogo |

> Full taxonomy contains ~365 terms. Use `GET /api/taxonomy/find?slug=funcao` to retrieve all.

---

## etnia

Indigenous ethnicities and traditional communities.

**Entity types:** Agent

### Selected Terms

| Term |
|------|
| Acroá |
| Aikaná |
| Akuntsu |
| Aldeia Indígena |
| Aparai |
| Apinajé |
| Apiaká |
| Aranã |
| Arara |
| Aruá |
| Assurini |
| Awá |
| Bakairí |
| Banawá |
| Barasana |
| Baniwa |
| Bororo |
| Caeté |
| Canela |
| Carajá |
| Caripuna |
| Fulni-ô |
| Guarani |
| Guarani Kaiowá |
| Guarani Mbyá |
| Guarani Nhandeva |
| Ianomâmi |
| Juruna |
| Kaiabi |
| Kalapalo |
| Kamaiurá |
| Karajá |
| Kayapó |
| Kaxinawá |
| Krikati |
| Kuikuro |
| Macuxi |
| Marubo |
| Maxakalí |
| Munduruku |
| Mura |
| Ofaié |
| Pankararu |
| Pataxó |
| Paumari |
| Pirahã |
| Potiguara |
| Rikbaktsá |
| Sateré-Mawé |
| Suruí |
| Tembé |
| Terena |
| Ticuna |
| Tukano |
| Tupinambá |
| Tupiniquim |
| Wai-Wai |
| Waimiri-Atroari |
| Wapichana |
| Xakriabá |
| Xavante |
| Xerente |
| Yawalapiti |
| Yanomami |
| Zo'é |

> Full taxonomy contains ~245 terms. Use `GET /api/taxonomy/find?slug=etnia` to retrieve all.

---

## API Usage

### Retrieve all terms for a taxonomy

```
GET /api/taxonomy/find?slug=area
```

### Filter by entity type

```
GET /api/taxonomy/find?slug=linguagem&entityType=MapasCulturais\Entities\Event
```

### Search terms

```
GET /api/taxonomy/find?slug=funcao&@keyword=músico
```

### Apply taxonomy to an entity

```json
{
  "name": "Meu Projeto",
  "terms": {
    "linguagem": ["Música", "Dança"],
    "area": ["Artes Cênicas", "Educação"],
    "tag": ["festival-2026", "cultura-negritude"]
  }
}
```
