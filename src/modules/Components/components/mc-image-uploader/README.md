# Componente `<mc-image-uploader>`
O componente `mc-image-uploader` é utilizado para carregar e recortar imagens, permitindo o upload para uma entidade específica.

### Eventos
- **cropped** :Emitido quando a imagem é recortada. O payload é o próprio componente.
- **uploaded** :Emitido quando o upload da imagem é concluído com sucesso. O payload é o próprio componente.

## Propriedades
- *Entity **entity*** (obrigatório): Entidade à qual a imagem será associada.
- *Group **string*** (obrigatório): Grupo de arquivos ao qual a imagem pertence.
- *Circular **boolean*** (padrão: false): Indica se o recorte da imagem deve ser circular.
- *AspectRatio **number***: Proporção de aspecto desejada para o recorte da imagem.
- *Width **number***: Largura desejada para o recorte da imagem.
- *Height **Number***: Altura desejada para o recorte da imagem.
- *UseDescription **boolean*** (padrão: false): Indica se deve ser exibido um campo para descrição da imagem.
- *DeleteFile **boolean*** (padrão: false): Indica se deve ser exibido o botão para deletar a imagem.

### Importando componente
```PHP
<?php 
$this->import('mc-image-uploader');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <mc-image-uploader :entity="entity" group="avatar" :circular="true" :aspectRatio="1" :width="300" :height="300" useDescription>
    <template #default="{ modal, blob, file, blobUrl, description, upload }">
        <label>
            <input type="file" ref="file" @change="loadImage($event, modal)" accept="image/*" style="display:none">
            <img :src="blobUrl" v-if="blobUrl" alt="Preview da imagem">
            <div v-else class="placeholder">Selecione uma imagem</div>
        </label>
    </template>
</mc-image-uploader>
```



