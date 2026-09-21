# Laboratorio 5 - Diseño de API REST

## Gemdy Calendar

En este laboratorio se trabajó en la API REST del proyecto Gemdy Calendar.

Se mejoraron las rutas, las respuestas de la API, el manejo de errores y la documentación de los endpoints.

## Recursos utilizados

Los principales recursos de la API son:

- Cursos
- Pendientes
- Recordatorios

Las rutas utilizan nombres de recursos y los métodos HTTP correspondientes.

Ejemplos:

GET /api/cursos

POST /api/cursos

GET /api/pendientes

POST /api/pendientes

DELETE /api/cursos/{curso}

## Recordatorios

Los recordatorios pertenecen a un pendiente.

Por esta razón se utilizan las siguientes rutas:

GET /api/pendientes/{pendiente}/recordatorios

POST /api/pendientes/{pendiente}/recordatorios

Esto permite indicar que el recordatorio está relacionado con un pendiente.

## Pendientes próximos

Para consultar los pendientes próximos se utiliza:

GET /api/pendientes?proximos=1

Esto permite filtrar los pendientes sin crear otra ruta para el mismo recurso.

## API Resources

Se utilizaron API Resources de Laravel para controlar los datos que devuelve la API.

Se crearon los siguientes:

- CursoResource
- PendienteResource
- RecordatorioResource
- ResumenEstadoResource

De esta manera no se devuelven directamente los modelos de la base de datos.

## Códigos HTTP

La API utiliza diferentes códigos de respuesta dependiendo del resultado.

200: La solicitud fue realizada correctamente.

201: El recurso fue creado correctamente.

204: El recurso fue eliminado correctamente.

404: El recurso solicitado no existe.

409: Existe un conflicto con una regla de negocio.

422: Los datos enviados no son válidos.

## Manejo de errores

Los errores se manejan de forma centralizada.

Por ejemplo:

Si un recurso no existe se devuelve 404.

Si se incumple una regla de negocio se devuelve 409.

Si los datos enviados no son válidos se devuelve 422.

Esto permite que las respuestas de error tengan un formato similar.

## Paginación

Los listados de cursos y pendientes utilizan paginación.

Por ejemplo:

GET /api/cursos

GET /api/pendientes

Las respuestas muestran los registros y también información sobre las páginas disponibles.

## Documentación OpenAPI

Se utilizó Scramble para generar la documentación de la API.

La documentación se puede consultar en:

http://127.0.0.1:8000/docs/api

También se exportó el archivo:

docs/openapi.yaml

## Postman

Se creó una colección de Postman para probar la API.

La colección contiene 15 solicitudes con casos correctos y casos de error.

Se probaron operaciones como:

- Listar cursos
- Crear cursos
- Buscar cursos
- Listar pendientes
- Crear pendientes
- Consultar pendientes próximos
- Consultar el resumen de estados
- Listar recordatorios
- Crear recordatorios
- Probar errores de validación
- Probar reglas de negocio

También se creó un environment con las variables base_url y token.

La variable base_url contiene:

http://127.0.0.1:8000/api

El token se dejó vacío porque la autenticación se trabajará posteriormente.

## Propuesta de microservicio

Como propuesta de microservicio se seleccionó el módulo de recordatorios.

Los recordatorios tienen una función específica dentro del sistema, por lo que en el futuro podrían manejarse de forma separada.

El microservicio de recordatorios podría encargarse de:

- Listar recordatorios
- Crear recordatorios
- Consultar un recordatorio
- Actualizar un recordatorio
- Eliminar un recordatorio
- Consultar recordatorios de un pendiente

Algunas rutas que podría utilizar son:

GET /recordatorios

POST /recordatorios

GET /recordatorios/{id}

PUT /recordatorios/{id}

DELETE /recordatorios/{id}

GET /recordatorios?pendiente_id={id}

## Justificación

Se seleccionó Recordatorios porque cumple una función específica dentro de Gemdy Calendar.

Separarlo permitiría que en el futuro esta parte del sistema pueda crecer sin tener que modificar directamente toda la aplicación.

Por ahora los recordatorios continúan formando parte del proyecto Laravel. La separación solamente se presenta como una propuesta para un futuro microservicio.

## Conclusión

En este laboratorio se mejoró la API REST de Gemdy Calendar utilizando rutas REST, API Resources, códigos HTTP, manejo de errores, paginación y documentación OpenAPI.

También se creó una colección de Postman para probar los endpoints y se propuso Recordatorios como un posible microservicio.