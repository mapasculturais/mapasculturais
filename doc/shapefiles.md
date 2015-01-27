Padronização dos SHAPEFILEs para uso na plataforma Mapas Culturais
==================================================================
Os SHAPEFILEs devem, preferencialmente:

- Estar no sistema de coordenadas WGS84 (EPSG 4326), que é o sistem de coordenadas usado pelo Sistema de Posicionamento Global (GPS) e pelos Mapas Culturais. É possível converter de praticamente qualquer outro sistema de coordenadas para o WGS84usando o Quantum GIS;

- Consistir de um SHAPEFILE bidimensional por grupo de dados (bairros, distritos, regiões, municípios...), não múltiplos arquivos. É possível combinar múltiplos SHAPEFILEs em um usando o plugin MMQGIS do Quantum GIS e é possível converter de polyline e outros formatos vetoriais para o padrão de polígonos de duas dimensões usando o Quantum GIS;

- Conter legendas codificadas em UTF-8. Uma legenda por polígono;
