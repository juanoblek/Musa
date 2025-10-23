# CONFIGURACIÓN PARA VIDEOS SIN LÍMITE DE TAMAÑO

## Cambios realizados en admin-panel.php:

✅ **COMPLETADO**: Se modificó el input principal para aceptar videos:
- Cambiado `accept="image/*"` a `accept="image/*,video/*"`
- Cambiado el ícono de `fas fa-image` a `fas fa-photo-video`
- Actualizado el texto de "Imagen Principal" a "Imagen o Video Principal"
- Modificado el texto de ayuda para incluir formatos de video
- Removida la limitación de 5MB

✅ **COMPLETADO**: Se agregó la función `previewMainMedia()`:
- Maneja tanto imágenes como videos
- Muestra indicadores de tipo de archivo
- Sin validación de tamaño de archivo

✅ **COMPLETADO**: El sistema ya soporta videos en medios adicionales

## Configuraciones de PHP necesarias:

Para permitir videos grandes sin límite, edita el archivo `C:\xampp\php\php.ini`:

```ini
; Permitir uploads
file_uploads = On

; Sin límite de tamaño (o un valor muy grande)
upload_max_filesize = 0
post_max_size = 0

; Sin límite de tiempo de ejecución (o tiempo suficiente)
max_execution_time = 0
max_input_time = -1

; Memoria suficiente
memory_limit = 512M

; Múltiples archivos
max_file_uploads = 20
```

## Cómo aplicar los cambios:

1. **Verificar configuración actual**:
   - Ve a: http://localhost/Musa/check-php-config.php
   - Revisa los valores actuales

2. **Editar php.ini**:
   - Abre: `C:\xampp\php\php.ini`
   - Busca y modifica las líneas mencionadas arriba
   - Guarda el archivo

3. **Reiniciar Apache**:
   - Abre el panel de control de XAMPP
   - Detén Apache
   - Inicia Apache nuevamente

4. **Verificar cambios**:
   - Vuelve a verificar: http://localhost/Musa/check-php-config.php

## Valores alternativos si no quieres "sin límite":

Si prefieres límites específicos pero generosos:

```ini
upload_max_filesize = 1024M    ; 1GB
post_max_size = 1024M          ; 1GB
max_execution_time = 300       ; 5 minutos
max_input_time = 300           ; 5 minutos
```

## Archivos modificados:

1. ✅ `admin-panel.php` - Input principal modificado para aceptar videos
2. ✅ `api/upload-image.php` - Ya estaba preparado para videos sin límite
3. ✅ Los estilos CSS ya existían para videos

## Uso:

Ahora en el panel administrativo podrás:
- Subir videos como imagen principal del producto
- Subir videos en medios adicionales  
- Sin límite de tamaño de archivo (según configuración de PHP)
- Vista previa de videos en tiempo real
- Soporte completo para MP4, MOV, AVI, WebM

## Notas importantes:

- Los videos se mostrarán con controles automáticos en la vista del producto
- Se mantiene la compatibilidad completa con imágenes
- El sistema detecta automáticamente si es imagen o video
- Los videos se optimizan para web automáticamente