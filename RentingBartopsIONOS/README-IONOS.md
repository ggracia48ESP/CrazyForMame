# Publicación en IONOS

1. Sube el contenido de esta carpeta por SFTP al directorio asignado a crazy4arcade.com.
2. Abre https://crazy4arcade.com/setup.php.
3. Introduce los datos de MariaDB, la cuenta SMTP de IONOS y la contraseña del administrador.
4. Cuando termine, borra setup.php por SFTP.
5. Accede a https://crazy4arcade.com/admin/.

## Correo SMTP

La web utiliza el SMTP autenticado de IONOS:

- Servidor: smtp.ionos.es
- Puerto: 587
- Seguridad: STARTTLS
- Usuario: la dirección de correo completa de IONOS
- Remitente: una dirección de correo autorizada en el dominio

El instalador guarda estos datos en app/config.php. Si la instalación ya existe, añade el bloque mail siguiendo app/config.example.php mediante SFTP. No publiques ni compartas app/config.php.

Cada solicitud envía un aviso a thorzanocade@gmail.com y una confirmación al cliente. Si SMTP falla, la solicitud queda guardada y la pantalla muestra el fallo.

La contraseña de la base de datos no se guarda en el repositorio. app/config.php se genera durante la instalación y debe permanecer protegido; el servidor no debe mostrar el contenido de archivos PHP.
