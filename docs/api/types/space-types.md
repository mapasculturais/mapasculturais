# Space Types

## Type Groups and IDs

### Espaços de Exibição de Filmes (10–19)

| ID | Name |
|----|------|
| 10 | Cine itinerante |
| 11 | Cineclube |
| 12 | Drive-in |
| 13 | Espaço Público |
| 14 | Sala de cinema |

### Bibliotecas (20–29)

| ID | Name |
|----|------|
| 20 | Biblioteca Pública |
| 21 | Biblioteca Privada |
| 22 | Biblioteca Comunitária |
| 23 | Biblioteca Escolar |
| 24 | Biblioteca Nacional |
| 25 | Biblioteca Universitária |
| 26 | Biblioteca Especializada |

### Teatros (30–39)

| ID | Name |
|----|------|
| 30 | Teatro Público |
| 31 | Teatro Privado |

### Centros Culturais (40–49)

| ID | Name |
|----|------|
| 40 | Centro Cultural Público |
| 41 | Centro Cultural Privado |

### Arquivos (50–59)

| ID | Name |
|----|------|
| 50 | Arquivo Público |
| 51 | Arquivo Privado |

### Museus (60–69)

| ID | Name |
|----|------|
| 60 | Museu Público |
| 61 | Museu Privado |

### Centros de Documentação (70–79)

| ID | Name |
|----|------|
| 70 | Centro de Documentação |

### Espaços Religiosos (80–89)

| ID | Name |
|----|------|
| 80 | Centro Espírita |
| 81 | Igreja |
| 82 | Mesquita |
| 83 | Sinagoga |
| 84 | Terreiro |
| 85 | Templo |

### Circos (90–99)

| ID | Name |
|----|------|
| 90 | Circo Itinerante |
| 91 | Circo Fixo |
| 92 | Circo Tradicional |
| 93 | Circo Moderno |
| 94 | Terreno (circense) |

### Demais Equipamentos (100–199)

| ID | Name |
|----|------|
| 100 | Arena |
| 101 | Auditório |
| 102 | Bar |
| 103 | Boate / Balada / Casa Noturna |
| 104 | Caixa d'Água |
| 105 | Casa de Cultura |
| 106 | Casa de Festas |
| 107 | Centro de Artesanato |
| 108 | Centro de Convenções |
| 109 | Centro de Música |
| 110 | Centro de Tradições Gaúchas |
| 111 | Centro Esportivo |
| 112 | Centro de Juventude |
| 113 | Centro de Memória |
| 114 | Corredor Cultural |
| 115 | Galeria |
| 116 | Ginásio |
| 117 | Hotel |
| 118 | Laboratório |
| 119 | Livraria |
| 120 | Lona Cultural |
| 121 | Maracanã |
| 122 | Mercado |
| 123 | Parque |
| 124 | Praça |
| 125 | Quadra |
| 126 | Residência |
| 127 | Restaurante |
| 128 | Sede / Galpão |
| 129 | Shoppings |
| 130 | Vila / Comunidade |

### Bens Culturais Materiais (200–299)

| ID | Name |
|----|------|
| 200 | Bem Cultural Material |
| 201 | Área Tombada |
| 202 | Edificação Tombada |
| 203 | Patrimônio Histórico |
| 204 | Sítio Arqueológico |

### Instituições de Ensino Regular (300–499)

| ID | Name |
|----|------|
| 300 | Creche |
| 301 | Educação Infantil |
| 302 | Ensino Fundamental |
| 303 | Ensino Médio |
| 304 | Ensino Superior |
| 305 | Escola Técnica |

### Temporário (500–600)

| ID | Name |
|----|------|
| 500 | Espaço Temporário |
| 501 | Feira / Exposição Temporária |
| 502 | Espaço Cênico Temporário |
| 600 | Espaço Não Edificado |

### Escolas Livres (800–899)

| ID | Name |
|----|------|
| 800 | Escola Livre de Música |
| 801 | Escola Livre de Teatro |
| 802 | Escola Livre de Dança |
| 803 | Escola Livre de Artes Visuais |
| 804 | Escola Livre de Audiovisual |
| 805 | Escola Livre de Circo |
| 806 | Escola Livre de Literatura |
| 807 | Escola Livre de Cultura Digital |
| 808 | Escola Livre de Gastronomia |
| 809 | Escola Livre de Fotografia |
| 810 | Escola Livre de Hip Hop |

## Metadata Fields

### Identification

| Field | Type | Description |
|-------|------|-------------|
| `cnpj` | `string` | CNPJ number |
| `razaoSocial` | `string` | Legal corporate name |

### Contact

| Field | Type | Description |
|-------|------|-------------|
| `emailPublico` | `string` | Public email |
| `emailPrivado` | `string` | Private email |
| `telefonePublico` | `string` | Public phone |
| `telefone1` | `string` | Primary phone |
| `telefone2` | `string` | Secondary phone |

### Capacity & Accessibility

| Field | Type | Description |
|-------|------|-------------|
| `capacidade` | `integer` | Maximum capacity |
| `acessibilidade` | `text` | General accessibility description |
| `acessibilidade_fisica` | `multiselect` | Physical accessibility features |

### Operational

| Field | Type | Description |
|-------|------|-------------|
| `horario` | `string` | Operating hours |
| `criterios` | `text` | Usage criteria / rules |

### Address

| Field | Type | Description |
|-------|------|-------------|
| `En_CEP` | `string` | Postal code |
| `En_Nome_Logradouro` | `string` | Street name |
| `En_Num` | `string` | Number |
| `En_Complemento` | `string` | Complement |
| `En_Bairro` | `string` | Neighborhood |
| `En_Municipio` | `string` | City |
| `En_Estado` | `string` | State (2-letter code) |
| `En_Pais` | `string` | Country |

### Web Presence

| Field | Type |
|-------|------|
| `site` | `string` |
| `facebook` | `string` |
| `twitter` | `string` |
| `instagram` | `string` |
| `linkedin` | `string` |
| `vimeo` | `string` |
| `spotify` | `string` |
| `youtube` | `string` |
| `pinterest` | `string` |
| `tiktok` | `string` |
| `fediverso` | `string` |

## Example

```json
{
  "type": 31,
  "name": "Teatro Municipal",
  "metadata": {
    "cnpj": "12.345.678/0001-90",
    "razaoSocial": "Fundação Teatro Municipal",
    "emailPublico": "contato@teatromunicipal.sp.gov.br",
    "telefonePublico": "(11) 3223-0000",
    "capacidade": 1500,
    "acessibilidade": "Rampas de acesso, elevador, banheiros adaptados",
    "acessibilidade_fisica": ["Rampa de acesso", "Elevador", "Banheiro acessível", "Cadeira de rodas"],
    "horario": "Terça a domingo, 14h às 22h",
    "En_CEP": "01000-000",
    "En_Nome_Logradouro": "Praça Ramos de Azevedo",
    "En_Num": "s/n",
    "En_Bairro": "República",
    "En_Municipio": "São Paulo",
    "En_Estado": "SP",
    "En_Pais": "Brasil",
    "site": "https://teatromunicipal.sp.gov.br",
    "instagram": "@teatromunicipal"
  }
}
```
