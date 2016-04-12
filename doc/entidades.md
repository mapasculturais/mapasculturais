Entidades dos Mapas Culturais
=============================
La base de datos está compuesta, básicamente, por cuatro entidades: Agentes, Espacios, Eventos y Proyectos

Las cuatro entidades poseen en comúm:
-------------------------------------
* Entidad Verificada o no por el administrador del sistema
* Nombre para mostrar
* Imagen de la cubierta
* Imagen de Avatar
* Agentes relacionados
* Descripción breve
* Descripción larga
* Etiquetas
* página web
* Videos
* Galería de imágenes
* Lista de archivos para descarga
* Links (lista)
* Facebook
* Tweeter
* Google+
* Publicado por (Agente responsable por la entidad)


Los agentes y los espacios tienen en común:
-----------------------------------
* Email privado
* Email público
* Teléfono público
* Teléfonos Privados 1 y 2
* Localización geográfica
* Dirección
* Calendario de Eventos
* Áreas de Actuación



Los agentes tienen exclusivamente:
-------------------------------
* Nombre completo
* Tipo
    * Colectivo
    * individual
* CI / RUT
* Fecha de Nacimiento / Fundación
* Género
* Raza / Color

Los Espacios tienen exclusivamente
-----------------------------------
* Tipo	
* Estado de la publicación:
    * Publicación restringida - requiere autorización para crear eventos
    * Publicación libre - cualquier persona puede crear eventos
* Accesibilidad
* Capacidad
* Horario de funcionamiento
* Criterios de uso del Espacio
* El subespacio o espacio padre al que pertenecen


Los Eventos tienen exclusivamente:
-----------------------------------
* Tipos de Eventos
* Subtítulo
* Inscripciones
* Clasificación etaria
* Número de teléfono para obtener información
* Accesibilidad:
    * Traducción al lenguaje de señas
    * Audio Descripción
* Proyecto al que pertenece
* Eventos: un evento puede tener varias instancias, cada instancia tiene:
    * Espacio relacionado
    * Reglas de repetición de fechas; Horarios, Duración
    * Precio


Los Proyectos tienen exclusivamente:
--------------------------------
* Tipo
* Inscripciones:
    * En línea - activado o desactivado
    * Fecha de Inicio, Fin, Hora de finalización
    * Introducción de texto
    * Reglamento
    * Categorías
    * Agentes:
        * Responsable
        * Institución Responsable
        * Colectivo
        * El número máximo de entradas por agente responsable
        * Los archivos adjuntos:
            * Obligatorio / Opcional
            * Con o sin archivo de plantilla (forma)
    * Estado:
        * No válida - en violación de la reglamentación (por ejemplo, documentación incorrecta.).
        * Pendiente - aún no evaluado.
        * No seleccionada - evaluado, pero no seleccionado.
        * Suplente - evaluado, pero esperando vacante.
        * Seleccionado - evaluado y seleccionado.
        * Borrador - utilice esta opción para permitir la edición y volver a presentar una solicitud. Al seleccionar esta opción, la inscripción ya no se mostrará en esta tabla.
