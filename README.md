Notas
=====


Reestructuración del Framework usando las mejores prácticas.

* Se quitó `micromotor.php`, se integró a `globals.php`
* Forzando uso de `status_code()` con `suppress_response_codes`
* Bandera debug se controla desde la credencial
* Se quito la ejecución permanente de `validation.php`
* Se cambiaron `if` por `OR`
* Reestructuración de `getValueFrom` para aceptar callbacks y se agregaron shortcuts


Revisar
--------

* en `doJSONResponse` revisar si es necesario `Allow-Origin`

TODO
-----

* index.php:70


Errores
--------
* 500, "Config file does not exists!"
  El archivo de configuración no existe
* 500, "Configuration not defined!"
  Existe el archivo pero no la variable de configuración
* 503
  Servicio apagado desde config
* 401
  Credencial inválida o no establecida en el request
* 400, "Bad Request", "Invalid resource!"
  Recurso no definido o no establecido en el request
* 400, "Bad Request", "Invalid resource action!"
  Acción en recurso inválida