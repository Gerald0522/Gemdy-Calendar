# Hito 1 – NestJS

## Implementación realizada

Para el estudio comparativo se implementó en NestJS la porción correspondiente a la entidad Pendiente, utilizando la misma base de datos SQLite del proyecto y tomando como referencia el contrato comparativo y la implementación de Laravel.

Se implementaron las siguientes funcionalidades:

- Autenticación mediante JWT.
- CRUD de Pendiente.
- Protección de endpoints.
- Validaciones mediante DTO.
- Reglas de negocio.
- Manejo de errores HTTP 401, 404, 409 y 422.
- Paginación con data, links y meta.
- Filtros por estado y curso.
- Ordenamiento de resultados.
- Consulta de pendientes próximos.


## Endpoints

| Método | Endpoint |

| POST | `/api/login` |
| GET | `/api/pendientes` |
| GET | `/api/pendientes/:id` |
| POST | `/api/pendientes` |
| PUT | `/api/pendientes/:id` |
| DELETE | `/api/pendientes/:id` |


## Criterios de comparación

### Tiempo de puesta en marcha

Este criterio corresponde a los minutos desde el proyecto vacío hasta obtener el primer endpoint respondiendo, sin embargo, este tiempo se registro mientras se implementan, asi que no puede darse un valor estimado. 

### Tamaño de la porción

Se contabilizó únicamente el código propio utilizado para la implementación, excluyendo dependencias y archivos predeterminados de NestJS.

- Archivos propios: 17
- Líneas de código propias: 747


### Esfuerzo de validación

La validación se realizó utilizando DTO, `class-validator` y `ValidationPipe`.

- Archivos principales involucrados: 3
- Líneas de los DTO: 66
- Configuración de `ValidationPipe`: 6 líneas
- Total medido: 72 líneas

Los errores de validación se devuelven mediante HTTP 422.


### Esfuerzo de autenticación

La autenticación utiliza JWT y bcrypt.

- Archivos: 5
- Líneas: 178
- `@nestjs/jwt`: versión 12.0.2
- `bcrypt`: versión 6.0.0

La implementación permite emitir y validar tokens. La revocación explícita del token no fue implementada en este Hito.


### Documentación automática

En este Hito no se configuró OpenAPI para NestJS y `@nestjs/swagger` no se encuentra instalado.

Esta funcionalidad se deja para un Hito posterior.

En Laravel sí se utilizó Scramble para generar la documentación automática
y el archivo `docs/openapi.yaml`.


### Pruebas

Las pruebas E2E se realizaron utilizando Jest y Supertest.

- Jest: 30.5.2
- Supertest: 7.2.2
- Líneas de pruebas: 100
- Pruebas equivalentes: 3
- Resultado: 3 de 3 aprobadas

Las pruebas realizadas fueron:

1. Listar los pendientes del usuario.
2. Crear un pendiente.
3. Validar los campos obligatorios al crear un pendiente.


### Rendimiento

La medición de peticiones por segundo y latencia p95 queda pendiente.

Esta prueba debe realizarse utilizando la misma máquina, base de datos y
escenario para Laravel, NestJS y ASP.NET Core para que la comparación sea válida.


### Huella de ejecución

La medición de memoria en reposo y tamaño del artefacto desplegable queda
pendiente y deberá realizarse bajo las mismas condiciones para los tres
frameworks.


### Seguridad por omisión

Se ejecuta en el Hito 3 por lo cual acá no se mide.

### Ecosistema y curva de aprendizaje

NestJS ofrece una estructura organizada mediante módulos, controladores,
servicios, DTO, guards, pipes y filtros.

Esta organización permite separar las responsabilidades del proyecto, aunque
al inicio requiere aprender varios componentes del framework.

Una de las principales dificultades fue adaptar NestJS para utilizar la misma
base de datos de Laravel y mantener el comportamiento definido en el contrato
comparativo.


## Resultado del Hito

La porción funcional de NestJS quedó implementada correctamente.

Las tres pruebas automatizadas equivalentes fueron aprobadas y se comprobó
el funcionamiento del CRUD, autenticación, validaciones, reglas de negocio,
paginación, filtros y manejo de errores.

Las mediciones de rendimiento y huella de ejecución quedan pendientes hasta
que puedan realizarse bajo las mismas condiciones para Laravel, NestJS y
ASP.NET Core.
