# Publicación en IONOS

1. Subir el contenido de esta carpeta por SFTP al directorio asignado a `crazy4arcade.com`.
2. Abrir `https://crazy4arcade.com/setup.php`.
3. Comprobar los datos de MariaDB, introducir la contraseña de la BD y crear la contraseña del administrador.
4. Cuando termine, borrar `setup.php` por SFTP.
5. Acceder a `https://crazy4arcade.com/admin/`.

La contraseña de la base de datos no se guarda en el repositorio. `app/config.php` se genera durante la instalación y debe permanecer protegido; el servidor no debe mostrar el contenido de archivos PHP.
