# ASP.NET Core 8 — Hito 2
## Gemdy Calendar

> Documentación técnica de la implementación comparativa en ASP.NET Core 8.
> Esta carpeta es documental y no modifica el funcionamiento de Laravel, NestJS o ASP.NET.

## 1. Alcance

La implementación ASP.NET reproduce la vertical comparativa de `Pendiente` utilizando la misma base SQLite del proyecto principal para ejecución normal, sin ejecutar migraciones de Entity Framework sobre la base de Laravel.

Reglas de aislamiento aplicadas:

- ASP.NET no ejecuta migraciones de EF sobre `database/database.sqlite`.
- No se usa `EnsureCreated()` ni `EnsureDeleted()` contra la base principal.
- ASP.NET no escribe tokens en `personal_access_tokens`; utiliza JWT propio.
- Las pruebas automatizadas usan SQLite en memoria.
- No se modificó Laravel para satisfacer necesidades de ASP.NET.
- La validación de dependencias previa al `DELETE` se realiza sin modificar las migraciones ni las reglas de cascada de Laravel.

## 2. Endpoints implementados

| Método | Endpoint | Resultado principal |
|---|---|---|
| POST | `/api/login` | `200` + token JWT |
| GET | `/api/pendientes` | `200` paginado |
| GET | `/api/pendientes/{id}` | `200 / 403 / 404` |
| POST | `/api/pendientes` | `201` + `Location` |
| PUT | `/api/pendientes/{id}` | `200 / 403 / 404 / 422 / 409` |
| DELETE | `/api/pendientes/{id}` | `204 / 403 / 404 / 409` |

El listado admite:

- `page`
- `por_pagina`
- `q`
- `estado`
- `curso_id`
- `proximos`

## 3. Autenticación y autorización

Se utiliza JWT Bearer con:

- validación de issuer;
- validación de audience;
- validación de expiración;
- clave simétrica;
- `ClockSkew = 0`;
- claims de usuario y rol;
- `[Authorize]` en los endpoints protegidos;
- control de ownership para diferenciar `403` y `404`.

La verificación de contraseña es compatible con los hashes bcrypt existentes de Laravel.

### Casos comprobados

| Caso | Resultado |
|---|---:|
| Login correcto | `200 OK` |
| Credenciales incorrectas | `401 Unauthorized` |
| Datos de login inválidos | `422 Unprocessable Entity` |
| Endpoint protegido sin token | `401 Unauthorized` |
| Recurso de otro usuario | `403 Forbidden` |
| Recurso inexistente | `404 Not Found` |

### Revocación

La implementación emite y valida JWT con expiración, pero **no implementa revocación explícita de tokens**. No existe blacklist/denylist, versionado de token ni endpoint de revocación. Esta limitación se declara expresamente para el criterio de esfuerzo de autenticación.

## 4. Validación y manejo de errores

Se configuró `InvalidModelStateResponseFactory` para devolver `422 Unprocessable Entity` en validaciones automáticas del modelo.

Además, se implementaron excepciones específicas:

- `RequestValidationException` → `422`
- `BusinessRuleException` → `409`

Casos verificados:

| Caso | Código |
|---|---:|
| Login con formato inválido | `422` |
| POST con estado inválido | `422` |
| PUT con estado inválido | `422` |
| Usuario o curso inexistente | `422` |
| Fecha límite anterior a hoy | `409` |
| Curso de otro usuario | `409` |
| DELETE de pendiente con dependencias | `409` |

## 5. Paginación y búsqueda

Ejemplo validado:

```http
GET /api/pendientes?page=1&por_pagina=5
```

Respuesta observada:

```json
"meta": {
  "current_page": 1,
  "per_page": 5,
  "total": 11,
  "last_page": 3
}
```

También se validó búsqueda con `q` sobre título y descripción:

```http
GET /api/pendientes?q=Prueba&page=1&por_pagina=5
```

La búsqueda y la paginación funcionan de manera conjunta.

> El parámetro de entrada expuesto por la API es `por_pagina`. El objeto de metadatos de respuesta conserva el campo `per_page`, por lo que una petición con `por_pagina=5` devuelve `"per_page": 5` en `meta`.

## 6. Creación y header Location

La creación válida responde:

```text
201 Created
```

e incluye el header `Location`.

Ejemplo observado durante la prueba:

```text
Location: /api/pendientes/186
```

## 7. DELETE y dependencias

El contrato comparativo exige rechazar con `409 Conflict` la eliminación de un `Pendiente` cuando existen dependencias asociadas.

Para cumplirlo sin alterar Laravel, `PendienteService` consulta antes de eliminar si el identificador aparece en:

- `recordatorios.pendiente_id`;
- `pendiente_etiqueta.pendiente_id`.

Si existe al menos una dependencia se lanza `BusinessRuleException` y se responde:

```json
{
  "message": "No se puede eliminar el pendiente porque tiene dependencias asociadas."
}
```

No se modificaron migraciones de Laravel ni se ejecutaron migraciones de Entity Framework sobre la base compartida.

### Casos comprobados

| Caso | Resultado |
|---|---:|
| Pendiente propio con dependencias (`id = 10`) | `409 Conflict` |
| Pendiente propio sin dependencias (`id = 182`) | `204 No Content` |
| Repetir DELETE del pendiente `182` | `404 Not Found` |
| Recurso ajeno | `403 Forbidden` |

Por tanto, el comportamiento `409 / 204 / 404` requerido para la eliminación quedó verificado.

## 8. Swagger / OpenAPI

Swagger quedó disponible con autenticación Bearer JWT.

Se documentaron los endpoints:

- `POST /api/login`
- `GET /api/pendientes`
- `POST /api/pendientes`
- `GET /api/pendientes/{id}`
- `PUT /api/pendientes/{id}`
- `DELETE /api/pendientes/{id}`

Se aplicó compatibilidad de salida OpenAPI `3.0.3` para evitar un problema de renderizado observado con Swagger UI y `3.0.4`.

La autenticación desde Swagger se verificó ejecutando un endpoint protegido con JWT y obteniendo `200 OK`.

## 9. Pruebas automatizadas

Proyecto:

```text
Gemdy-AspNet.Tests
```

Las pruebas usan `WebApplicationFactory<Program>` y SQLite en memoria, evitando modificar la base principal de Laravel.

Pruebas equivalentes implementadas:

1. Login válido → `200` + token.
2. `GET /api/pendientes` autenticado → `200`.
3. `POST /api/pendientes` válido → `201` + `Location`.

Comando final de verificación:

```powershell
dotnet test .\Gemdy-AspNet.Tests\Gemdy-AspNet.Tests.csproj
```

Resultado final observado el 24 de octubre de 2026:

Correctas: 3
Con error: 0
Omitido: 0
Total: 3
Duración: 448 ms

## 10. Métricas ASP.NET

### 10.1 Tamaño y código

| Métrica | Resultado |
|---|---:|
| Archivos relevantes | 21 |
| Líneas | 1297 |
| Tamaño de `publish` | 33.09 MB |
| RSS en reposo | 246.57 MB |

### 10.2 Rendimiento

Escenario:

- endpoint: `GET /api/pendientes?page=1&por_pagina=5`;
- autenticación: JWT Bearer;
- 10 solicitudes de calentamiento;
- 100 solicitudes medidas;
- ejecución secuencial;
- mismo script previsto para la comparación de frameworks.

Resultados:

| Métrica | Resultado |
|---|---:|
| Solicitudes | 100 |
| Exitosas | 100 |
| Fallidas | 0 |
| Tiempo total | 6.01 s |
| Req/s | 16.65 |
| Latencia promedio | 59.96 ms |
| p95 | 90.17 ms |
| Mínima | 24.65 ms |
| Máxima | 99.34 ms |

> importante destacar esta medición es secuencial y sirve como escenario reproducible de comparación interna. No debe interpretarse como capacidad máxima de concurrencia del framework.

## 11. Esfuerzo de implementación medido

Para los criterios de validación, autenticación, documentación automática y pruebas se contabilizaron los archivos y bloques de código directamente relacionados con cada responsabilidad.

En archivos compartidos se contabilizaron únicamente los rangos correspondientes al área evaluada. Los conteos incluyen comentarios y líneas vacías dentro de los rangos seleccionados. **Los resultados por área no deben sumarse como un total de LOC del proyecto**, porque algunos archivos participan en más de una responsabilidad.

| Área | Archivos involucrados | Líneas contabilizadas | Dependencias / herramientas | Alcance |
|---|---:|---:|---|---|
| Validación | 6 | **271** | DataAnnotations, ModelState, excepciones y middleware propios | 3 DTO de entrada (88), `Program.cs` 55–77 (23), validaciones de creación/actualización en `PendienteService.cs` (118) y manejo `409/422` en `ExceptionMiddleware.cs` (42). |
| Autenticación | 3 | **201** | `Microsoft.AspNetCore.Authentication.JwtBearer`, `Microsoft.IdentityModel.Tokens`, `System.IdentityModel.Tokens.Jwt`, `BCrypt.Net-Next` | `AuthControllers.cs` (49), `AuthService.cs` (120), `Program.cs` 24–53 (30) y 190–191 (2). Emisión y validación implementadas; revocación explícita no implementada. |
| OpenAPI | 1 | **102** | `Swashbuckle.AspNetCore` | `Program.cs` 79–117 (39) y 121–183 (63), incluyendo Bearer, Swagger UI y compatibilidad OpenAPI 3.0.3. |
| Pruebas | 2 | **305** | xUnit, `Microsoft.AspNetCore.Mvc.Testing`, `WebApplicationFactory`, EF Core SQLite | `AuthTests.cs` (148) y `CustomWebApplicationFactory.cs` (157), incluyendo SQLite en memoria y seed. |

### 11.1 Detalle del conteo de validación

| Bloque | Líneas |
|---|---:|
| DTOs de entrada | 88 |
| `Program.cs` — respuesta automática `422` | 23 |
| `PendienteService.cs` — validaciones de creación/actualización | 118 |
| `ExceptionMiddleware.cs` — respuestas `409/422` | 42 |
| **Total** | **271** |

### 11.2 Detalle del conteo de autenticación

| Bloque | Líneas |
|---|---:|
| `AuthControllers.cs` | 49 |
| `AuthService.cs` | 120 |
| `Program.cs` — configuración JWT | 30 |
| `Program.cs` — activación Authentication/Authorization | 2 |
| **Total** | **201** |

### 11.3 Tiempo de puesta en marcha

No se registró de forma exacta el tiempo desde un proyecto vacío hasta el primer endpoint funcional. Se reporta como:

```text
No registrado durante el desarrollo.
```

No se presenta una estimación retrospectiva como si fuera una medición objetiva.

## 12. Matriz de cumplimiento

| Requisito | Estado |
|---|---|
| Login válido `200` + token | Listo |
| Login incorrecto `401` | Listo |
| Login inválido `422` | Listo |
| GET listado `200` | Listo |
| GET sin token `401` | Listo |
| Paginación | Listo |
| Búsqueda `q` | Listo |
| GET propio `200` | Listo |
| GET ajeno `403` | Listo |
| GET inexistente `404` | Listo |
| POST válido `201` | Listo |
| Header `Location` | Listo |
| POST inválido `422` | Listo |
| Regla de negocio `409` | Listo |
| PUT válido `200` | Listo |
| PUT inválido `422` | Listo |
| DELETE válido `204` | Listo |
| DELETE inexistente `404` | Listo |
| DELETE con dependencias `409` | Listo |
| JWT Bearer | Listo |
| Swagger/OpenAPI | Listo |
| Swagger + JWT | Listo |
| 3 pruebas automatizadas | Listo |
| Tests aislados de Laravel | Listo |
| Métricas técnicas | Listo |
| Esfuerzo cuantificado por área | Listo |

## 13. Consideraciones de seguridad

Para la implementación comparativa:

- JWT tiene expiración;
- se validan issuer y audience;
- se usa firma simétrica;
- se aplica autorización a los endpoints protegidos;
- se verifica ownership de recursos;
- los tests no usan la base principal;
- la eliminación comprueba dependencias antes de borrar.

Limitaciones y endurecimiento posterior:

- la revocación explícita de JWT no está implementada;
- la clave JWT debe trasladarse a variables de entorno o secretos de desarrollo/producción y no permanecer como secreto real versionado.

## 14. Nota para la comparación final

Las métricas de Laravel y NestJS deben levantarse con el mismo criterio:

- misma máquina;
- misma base de datos y escenario comparable;
- mismo endpoint equivalente;
- mismo número de solicitudes;
- mismo calentamiento;
- mismos datos;
- mismo script de benchmark;
- mismas reglas de conteo de LOC/archivos;
- misma definición de RSS y tamaño de artefacto.

Esto permite comparar Laravel, NestJS y ASP.NET Core sin mezclar metodologías diferentes.

## 15. Evidencia visual

La evidencia visual completa se mantiene en el PDF de entrega de ASP.NET para evitar llenar el repositorio con capturas que no son necesarias para la ejecución del proyecto.

Entre las evidencias finales se incluyen:

- autenticación JWT;
- respuestas `401`, `403`, `404`, `409` y `422`;
- creación `201` con header `Location`;
- paginación y búsqueda;
- Swagger/OpenAPI;
- benchmark;
- `DELETE` con dependencias → `409`;
- `DELETE` sin dependencias → `204`;
- repetición del `DELETE` → `404`;
- ejecución final de las tres pruebas automatizadas.
