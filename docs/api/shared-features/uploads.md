# File Uploads (ControllerUploads)

Upload files to entities that use the `EntityFiles` trait (Agent, Space, Event, Project, Opportunity, Seal).

## Upload File

```
POST /{entity}/upload/{id}
```

Uploads files using `multipart/form-data`. The file input field name determines the **group**.

### File Groups

| Group | Description | Unique |
|-------|-------------|--------|
| `avatar` | Profile/main image | Yes |
| `gallery` | Image gallery | No |
| `downloads` | Downloadable files | No |
| `header` | Header/banner image | Yes |

Groups marked as **Unique** will replace the existing file. Non-unique groups accept multiple files.

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| File field | file | The file to upload. Field name = group name |
| `description[group]` | string | Optional description for the uploaded file |

### Upload Avatar (Unique)

```bash
curl -X POST https://mapas.cultura.gov.br/agent/123/upload \
  -H "Authorization: Bearer TOKEN" \
  -F "avatar=@/path/to/photo.jpg"
```

Response:

```json
{
  "avatar": {
    "id": 456,
    "name": "photo.jpg",
    "url": "https://mapas.cultura.gov.br/files/photo.jpg",
    "description": null,
    "group": "avatar"
  }
}
```

### Upload Gallery Image with Description

```bash
curl -X POST https://mapas.cultura.gov.br/space/456/upload \
  -H "Authorization: Bearer TOKEN" \
  -F "gallery=@/path/to/image.jpg" \
  -F "description[gallery]=Foto do evento"
```

### Upload Downloadable File

```bash
curl -X POST https://mapas.cultura.gov.br/project/789/upload \
  -H "Authorization: Bearer TOKEN" \
  -F "downloads=@/path/to/document.pdf" \
  -F "description[downloads]=Regulamento do projeto"
```

Response (multiple files):

```json
{
  "downloads": [
    {
      "id": 789,
      "name": "document.pdf",
      "url": "https://mapas.cultura.gov.br/files/document.pdf",
      "description": "Regulamento do projeto",
      "group": "downloads"
    }
  ]
}
```

### Error Response

```json
[{"error": "The uploaded file is not a valid image."}]
```

### Querying Files

```bash
# Get entity files
GET /api/agent/find?id=EQ(123)&@select=id,name,files

# Get specific group
GET /api/agent/find?id=EQ(123)&@select=id,name,files.avatar
```
