# Mestalla Entradas — WordPress local

Tema y complemento propios para la instalación local de WordPress en XAMPP.

## Ejecutar en local

1. Inicia Apache y MySQL en XAMPP.
2. Copia `wp-content/themes/mestalla-naranja` a `C:\xampp\htdocs\wordpress\wp-content\themes\`.
3. Copia `wp-content/plugins/mestalla-entradas` a `C:\xampp\htdocs\wordpress\wp-content\plugins\`.
4. En el escritorio de WordPress, activa **Mestalla Entradas** en Plugins y **Mestalla Naranja** en Apariencia → Temas.
5. La instalación configurada se abre en <http://localhost/wordpress/>.

Las páginas creadas para el sitio son Inicio, Entradas, Acceso y Mi cuenta. El complemento aporta el formulario de inicio de sesión y las solicitudes de reserva locales.

## GitHub y ejecución

GitHub guarda el historial del código. WordPress sigue ejecutándose en XAMPP; subir cambios al repositorio no los publica en Internet ni los copia automáticamente a XAMPP. Para actualizar la copia local, copia allí los cambios de las carpetas del tema y del complemento.

## Datos de demostración

Los partidos, fechas y precios son ejemplos. Las solicitudes se guardan en WordPress para demostración; este proyecto no procesa pagos ni emite entradas oficiales.

## Seguridad

Este repositorio contiene solo el tema y el complemento personalizados. No añadas `wp-config.php`, exportaciones `.sql`, contraseñas, claves, archivos `.env` ni copias completas de WordPress.
