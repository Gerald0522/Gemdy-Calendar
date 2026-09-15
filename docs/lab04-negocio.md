# Laboratorio 4 Capa de negocios

## 1. Introducción

En este laboratorio se implementó la capa de negocios del proyecto
Gemdy Calendar, separando las responsabilidades de validación,
lógica de negocio y control de las solicitudes HTTP. Para la
implementación se trabajó principalmente con las entidades **Pendiente**
y **Curso**.

La solución utiliza clases FormRequest para las validaciones
declarativas, clases Service para las reglas de negocio, una excepción
personalizada para representar violaciones del dominio y transacciones
para las operaciones que escriben en más de una tabla.

## 2. Entidades principales

Las entidades principales seleccionadas fueron **Pendiente** y
**Curso**. Sobre ambas se implementaron operaciones CRUD y validaciones
de creación y actualización.

## 3. Validaciones mediante Form Requests

Las validaciones declarativas se separaron de los controladores mediante
las clases:

-   StorePendienteRequest
-   UpdatePendienteRequest
-   StoreCursoRequest
-   UpdateCursoRequest
-   StorePendienteConRecordatorioRequest

### 3.1 Pendiente

Se valida la existencia del usuario y del curso, título obligatorio y
longitud máxima, longitud de descripción, estados permitidos
(pendiente, en_progreso y completado), fecha válida y formato de
hora. Para las actualizaciones se permiten modificaciones parciales.

### 3.2 Curso

Se valida la existencia del usuario, nombre y código obligatorios,
unicidad del código para el usuario correspondiente, semestre
obligatorio y créditos dentro del rango permitido. En actualización se
mantiene la unicidad ignorando el registro actual.

Los mensajes de validación fueron definidos en español y asociados a
cada campo. Ante datos inválidos Laravel devuelve
422 Unprocessable Entity con los errores correspondientes.

## 4. Separación de la lógica de negocio

La lógica se extrajo a PendienteService y CursoService. Los
controladores PendienteController y CursoController reciben las
solicitudes, delegan en el servicio y devuelven la respuesta HTTP,
manteniendo separadas las responsabilidades.

## 5. Reglas de negocio

### Regla 1 - El curso debe pertenecer al usuario
PendienteService verifica que el usuario_id del curso coincida con
el usuario del pendiente. Si no coincide, se lanza
BusinessRuleException.

### Regla 2 - La fecha límite no puede ser anterior a la fecha actual

Antes de crear o actualizar un pendiente se verifica que su fecha límite
no sea anterior a la fecha actual. Si incumple la regla, la operación es
rechazada.

### Regla 3 - No eliminar cursos con pendientes activos

CursoService impide eliminar un curso que posea pendientes con estado
pendiente o en_progreso, evitando eliminar registros con
dependencias activas.

### Regla 4 - El recordatorio no puede quedar después de la fecha límite

Al crear un pendiente con recordatorio, PendienteService verifica que
la fecha del recordatorio no sea posterior a la fecha límite. Si lo es,
se genera BusinessRuleException.

## 6. Excepción personalizada

Se implementó App\Exceptions\BusinessRuleException para diferenciar
las violaciones de reglas de negocio de los errores de validación y
otros errores. La excepción queda preparada para su posterior traducción
a códigos HTTP.

## 7. Transacciones y rollback

La operación POST /api/pendientes/con-recordatorio crea un pendiente y
un recordatorio, por lo que se ejecuta dentro de DB::transaction().

Cuando todos los datos son válidos, ambas escrituras se confirman. Para
comprobar el rollback se realizó una solicitud con un recordatorio
posterior a la fecha límite. La regla falla después de iniciar la
operación y Laravel revierte la transacción completa. Posteriormente se
consultó el pendiente utilizado en la prueba y su cantidad fue `0`,
demostrando que no quedaron registros parciales.

## 8. CRUD

### 8.1 Pendiente

  Operación    Método      Endpoint
  ------------ ----------- ------------------------
  Listar       GET         `/api/pendientes`
  Crear        POST        `/api/pendientes`
  Consultar    GET         `/api/pendientes/{id}`
  Actualizar   PUT/PATCH   `/api/pendientes/{id}`
  Eliminar     DELETE      `/api/pendientes/{id}`

Las operaciones fueron verificadas desde Postman. Después de eliminar un
pendiente se realizó un GET con el mismo identificador y se obtuvo
404 Not Found.

### 8.2 Curso

  Operación    Método      Endpoint
  ------------ ----------- --------------------
  Listar       GET         `/api/cursos`
  Crear        POST        `/api/cursos`
  Consultar    GET         `/api/cursos/{id}`
  Actualizar   PUT/PATCH   `/api/cursos/{id}`
  Eliminar     DELETE      `/api/cursos/{id}`

También se verificó el CRUD de Curso: creación, consulta, actualización,
eliminación y una consulta final que devolvió 404 Not Found,
confirmando la eliminación.

## 9. Paginación, filtros y ordenamiento

El listado de pendientes utiliza paginación con un máximo de **50
registros por página**. Por ejemplo, GET /api/pendientes?por_pagina=5
devuelve cinco registros. Si se solicita por_pagina=100, el servicio
limita el resultado a 50.

Los filtros son combinables. Por ejemplo:

GET /api/pendientes?estado=pendiente&curso_id=1&por_pagina=5

También se permite ordenar por campos como titulo, fecha_limite,
estado y created_at, indicando dirección ascendente o descendente:

GET /api/pendientes?orden=titulo&direccion=asc&por_pagina=5

GET /api/pendientes?orden=fecha_limite&direccion=desc&por_pagina=5

## 10. Pruebas de la capa de negocio

Las pruebas se implementaron en tests/Unit/PendienteServiceTest.php y
tests/Unit/CursoServiceTest.php.

Se verifica que no se permita asignar un curso de otro usuario, utilizar
una fecha límite anterior a hoy, eliminar un curso con pendientes
activos ni programar un recordatorio después de la fecha límite.

La ejecución completa se realizó con:

``` bash
php artisan test
```

Resultado obtenido:

``` text
PASS  Tests\Unit\CursoServiceTest
✓ no permite eliminar curso con pendientes activos

PASS  Tests\Unit\PendienteServiceTest
✓ no permite asignar curso de otro usuario
✓ no permite fecha limite anterior a hoy
✓ no permite recordatorio despues de fecha limite

Tests: 6 passed (6 assertions)
```

Las otras dos pruebas corresponden a las pruebas de ejemplo incluidas en
el proyecto.

## 11. Evidencias

Se documentaron mediante capturas: CRUD de Pendiente y Curso,
paginación, filtros combinables, ordenamiento, máximo de página, datos
inválidos, violación de las cuatro reglas de negocio, transacción con
rollback y ejecución de las pruebas automatizadas.

## 12. Conclusión

El Laboratorio 4 permitió separar la validación declarativa, la lógica
de negocio y el manejo de solicitudes HTTP. Las clases FormRequest
concentran las validaciones y los servicios contienen las reglas del
dominio. Además, se implementaron CRUD, paginación, filtros,
ordenamiento, límite de página, excepción personalizada, transacciones
con rollback y pruebas automatizadas.
