# Contrato comparativo - Gemdy Calendar

## 1. Objetivo

Este documento define el contrato común que se utilizará para implementar una misma porción funcional de Gemdy Calendar en:

- Laravel
- NestJS
- ASP.NET Core

El objetivo es utilizar la misma entidad, estructura de datos, endpoints, códigos de respuesta y datos de prueba en las tres implementaciones.

---

## 2. Entidad seleccionada

La entidad seleccionada para el contrato comparativo es:

**Pendiente**

Se eligió esta entidad porque representa una de las funcionalidades principales del sistema y posee una relación con la entidad Curso.

---

## 3. Campos

La entidad Pendiente contiene un máximo de ocho campos:

| Campo | Descripción |
|---|---|
| id | Identificador único del pendiente |
| usuario_id | Identificador del usuario propietario |
| curso_id | Identificador del curso asociado, puede ser nulo |
| titulo | Título del pendiente |
| descripcion | Descripción opcional |
| estado | Estado actual del pendiente |
| fecha_limite | Fecha límite opcional |
| hora_pendiente | Hora asociada al pendiente |

---

## 4. Relación seleccionada

La relación utilizada en el contrato comparativo es:

**Pendiente N:1 Curso**

Un Pendiente puede pertenecer a un Curso.

El campo:

`curso_id`

funciona como clave foránea.

La relación es opcional, por lo que `curso_id` puede contener un valor nulo.

---

## 5. Endpoints del contrato común

### 5.1 Autenticación

**Método**

`POST`

**Endpoint**

`/api/login`

**Objetivo**

Permitir la autenticación de un usuario mediante sus credenciales.

**Respuestas esperadas**

- `200 OK`: credenciales correctas.
- `401 Unauthorized`: credenciales incorrectas.
- `422 Unprocessable Entity`: datos requeridos inválidos o incompletos.

---

### 5.2 Listado paginado de pendientes

**Método**

`GET`

**Endpoint**

`/api/pendientes`

**Objetivo**

Obtener una lista paginada de pendientes.

Ejemplo:

`GET /api/pendientes?page=1`

**Respuestas esperadas**

- `200 OK`: listado obtenido correctamente.

---

### 5.3 Detalle de un pendiente

**Método**

`GET`

**Endpoint**

`/api/pendientes/{id}`

**Objetivo**

Obtener la información de un pendiente específico.

Ejemplo:

`GET /api/pendientes/1`

**Respuestas esperadas**

- `200 OK`: pendiente encontrado.
- `404 Not Found`: pendiente inexistente.

---

### 5.4 Crear pendiente

**Método**

`POST`

**Endpoint**

`/api/pendientes`

**Objetivo**

Registrar un nuevo pendiente.

Ejemplo de datos:

```json
{
    "usuario_id": 1,
    "curso_id": 1,
    "titulo": "Completar laboratorio",
    "descripcion": "Finalizar el laboratorio de Laravel",
    "estado": "pendiente",
    "fecha_limite": "2026-09-15",
    "hora_pendiente": "18:00:00"
}