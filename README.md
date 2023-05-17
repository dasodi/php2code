# php2code
Autor:          Dario Soto Diaz

Version:        1.0

Email:          dasodi@gmail.com

Fecha:          16-05-2023

Descripcion:    Pasa codigo de multiples archivos php y otros a un solo archivo de texto de codigo fuente.

1. Es necesario configurar el archivo ini.php con los parametros adecuados.

2. La aplicacion a codificar debe tener un archivo ini que tenga los parametros:

    $app_name       =   $ini_app['App']['name']
    
    $app_author     =   $ini_app['App']['autor']
    
    $app_version    =   $ini_app['App']['version']

3. El script se ejecuta en solitario desde un navegador local, y debe estar en el mismo servidor que la aplicación a codificar.

4. Se debe crear un archivo llamado ini.php en la misma carpeta del script php2code. Ejemplo:

; < ?php exit(); ?>

;php2code

;example: app conta

[App]

dir_app=C:\laragon\www\conta

dir_code=C:\_temp\code

ini_app_file=C:\laragon\www\conta\inc\ini.php

extensions=php-htm-js-css

no_dirs=.git,nbproject

no_files=.gitignore,.htaccess
