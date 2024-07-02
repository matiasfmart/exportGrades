# Export Grades Module for Moodle

## Descripción Corta
Este módulo para Moodle permite exportar calificaciones a Google Drive y manejar frecuencias de exportación según la configuración del usuario.

## Descripción Detallada
El plugin `Export Grades` ha sido diseñado para mejorar la funcionalidad de Moodle mediante la automatización del proceso de exportación de calificaciones. Este plugin no solo facilita la exportación automática de notas hacia Google Drive y el almacenamiento local, sino que también proporciona una interfaz altamente personalizable. 

### Características Principales:
- **Personalización de Frecuencia de Exportación**: Los usuarios pueden configurar la frecuencia de exportación general, con la posibilidad de aplicar configuraciones específicas por curso que pueden sobrescribir las configuraciones globales.
- **Filtrado Avanzado de Notas**: El plugin permite a los usuarios seleccionar cómo quieren exportar las notas. Las opciones incluyen:
  - Exportación de todas las notas.
  - Exportación solo de notas finales.
  - Exportación de notas específicas de las sedes Belgrano y Yatay, facilitando así la gestión administrativa y académica en contextos educativos diversificados.
- **Interfaz Intuitiva**: Diseñada para facilitar al máximo la experiencia del usuario, permitiendo una configuración rápida y eficaz sin necesidad de intervención técnica profunda.

### Beneficios:
- **Automatización y Eficiencia**: Ahorra tiempo y reduce errores mediante la automatización del proceso de exportación de notas.
- **Flexibilidad**: Adapta el proceso de exportación a las necesidades específicas de diferentes cursos o departamentos dentro de la misma institución educativa.
- **Accesibilidad Mejorada**: Facilita el acceso y la gestión de las calificaciones a través de Google Drive, permitiendo un manejo más fluido y accesible desde cualquier lugar.

Este plugin es ideal para instituciones educativas que buscan optimizar y automatizar la gestión de calificaciones a través de Moodle.


## Requisitos Previos

Para usar este módulo, asegúrate de tener instalado lo siguiente en tu sistema:
- PHP 7.2 o superior
- [Composer](https://getcomposer.org/), el cual es utilizado para manejar las dependencias del proyecto.

## Instalación

### 1. Opción: Instalación via archivo ZIP subido
1. Inicia sesión en tu sitio de Moodle como administrador y ve a **Administración del sitio > Plugins > Instalar plugins**.
2. Sube el archivo ZIP con el código del plugin. Solo se te solicitarán detalles adicionales si el tipo de tu plugin no se detecta automáticamente.
3. Revisa el informe de validación del plugin y finaliza la instalación.

### 2. Opción: Instalación manual
El plugin también puede ser instalado colocando los contenidos de este directorio en:
{tu_directorio_moodle}/mod/exportgrades

Después, inicia sesión en tu sitio de Moodle como administrador y ve a **Administración del sitio > Notificaciones** para completar la instalación.

### 3. Instalar Dependencias

cd exportgrades
composer install

### 4. Instalar las bibliotecas requeridas

El módulo requiere ciertas bibliotecas de PHP y Google API Client. Instala estas dependencias ejecutando:

composer require google/apiclient:^2.0

### 5. Configuración en Moodle

Después de instalar las dependencias, inicia sesión en tu instancia de Moodle como administrador. Navega a Administración del sitio > Notificaciones para permitir que Moodle reconozca y complete la instalación del módulo exportgrades.

### 6. Configuración en Google Cloud

Para utilizar la funcionalidad de exportación a Google Drive, necesitas tener una cuenta de Google Cloud. Sigue estos pasos para configurarla:

-Crea un proyecto en Google Cloud.
-Habilita la API de Google Drive para tu proyecto.
-Configura las credenciales para acceder a la API, generando un cliente y un token de acceso.
-Guarda el archivo client_secret.json en el directorio del módulo y asegúrate de que el archivo token.json esté accesible para que el módulo pueda autenticarse correctamente.

### 7. Uso

Una vez instalado y configurado, puedes utilizar el módulo desde tus cursos para programar y automatizar la exportación de calificaciones a Google Drive según los criterios seleccionados.

## Contribuir

Si deseas contribuir al desarrollo de este módulo, considera hacer un fork del repositorio y enviar tus Pull Requests. Toda contribución es bienvenida!

## Soporte

Si encuentras algún problema durante la instalación o el uso del módulo, por favor, abre un issue en el repositorio de GitHub del proyecto.


### Notas adicionales:

- Asegúrate de reemplazar `https://url_to_repository/exportgrades.git` con la URL real de tu repositorio si es público o con instrucciones adecuadas si el código se distribuye de otra forma.
- Este README proporciona una guía completa para instalar y configurar el módulo, asegurando que los usuarios tengan toda la información necesaria.


## Link al manual de usuario:
[Manual de usuario](https://docs.google.com/presentation/d/1P-mRu9dRpNx_FAxFdanJVbIcqP5B90mG3QT9sB81_7k/edit#slide=id.g211fb023b56_0_0)

## Link a la ppt con una descripción breve del proyecto:
[Plugin - Exportación csv a Google Drive](https://docs.google.com/document/d/1hNy-oxBLFoaJDLR_yAS5lPKgHlj9VM0Cph4CeFVApW8/edit)
