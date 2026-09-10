# Laboratorio 3 - Modelo de datos Gemdy Calendar

## 1. Descripción del proyecto

Gemdy Calendar es una aplicación orientada a la organización académica de estudiantes.

El sistema permite administrar cursos, horarios, pendientes, etiquetas y recordatorios asociados a cada usuario.

El modelo de datos está compuesto por las siguientes entidades:

- Usuario
- Curso
- Horario
- Pendiente
- Etiqueta
- Recordatorio

Además, se utiliza la tabla intermedia `pendiente_etiqueta` para representar la relación muchos a muchos entre Pendiente y Etiqueta.

---

## 2. Entidades

### Usuario

Representa a una persona que utiliza la aplicación.

Sus datos principales permiten identificar al usuario y almacenar la información necesaria para acceder al sistema.

Un usuario puede tener múltiples cursos, horarios, pendientes y recordatorios.

Relaciones:

- Usuario 1:N Curso
- Usuario 1:N Horario
- Usuario 1:N Pendiente
- Usuario 1:N Recordatorio

La contraseña se encuentra protegida en el modelo Eloquent mediante el atributo `$hidden`, evitando que sea mostrada durante la serialización de los datos.

---

### Curso

Representa una asignatura registrada por un usuario.

Cada curso pertenece a un único usuario, mientras que un usuario puede registrar varios cursos.

Relaciones:

- Curso N:1 Usuario
- Curso 1:N Horario
- Curso 1:N Pendiente

El campo `usuario_id` es una clave foránea que relaciona el curso con el usuario propietario.

También se utiliza una restricción única entre `usuario_id` y `codigo`, evitando que un mismo usuario registre dos veces un curso con el mismo código.

---

### Horario

Representa el horario correspondiente a un curso.

Contiene información como:

- día de la semana
- hora de inicio
- hora de finalización
- salón

Cada horario pertenece a un usuario y a un curso.

Relaciones:

- Horario N:1 Usuario
- Horario N:1 Curso

---

### Pendiente

Representa una tarea, trabajo, examen o actividad que debe realizar el usuario.

Un pendiente pertenece obligatoriamente a un usuario, pero su asociación con un curso es opcional.

Esto permite registrar pendientes académicos relacionados con un curso, así como actividades generales.

Relaciones:

- Pendiente N:1 Usuario
- Pendiente N:1 Curso
- Pendiente N:M Etiqueta
- Pendiente 1:N Recordatorio

El campo `fecha_limite` utiliza un conversor de tipo `date` en el modelo Eloquent.

Además, el modelo contiene scopes reutilizables para realizar consultas por estado, curso y fecha límite.

---

### Etiqueta

Representa una categoría utilizada para clasificar pendientes.

Por ejemplo:

- Universidad
- Importante
- Personal
- Proyecto
- Examen

Una etiqueta puede estar asociada a múltiples pendientes, y un pendiente puede tener múltiples etiquetas.

Relación:

- Etiqueta N:M Pendiente

Esta relación se implementa mediante la tabla intermedia `pendiente_etiqueta`.

---

### Recordatorio

Representa una notificación asociada a un pendiente.

Cada recordatorio pertenece a un usuario y a un pendiente.

Relaciones:

- Recordatorio N:1 Usuario
- Recordatorio N:1 Pendiente

El campo `fecha_recordatorio` se convierte al tipo `date` mediante los casts del modelo Eloquent.

---

## 3. Relación muchos a muchos

La relación muchos a muchos se establece entre las entidades Pendiente y Etiqueta.

Un pendiente puede poseer varias etiquetas y una misma etiqueta puede utilizarse en varios pendientes.

La relación se implementa utilizando la tabla:

`pendiente_etiqueta`

Esta tabla contiene las claves foráneas:

- `pendiente_id`
- `etiqueta_id`

Además, contiene información adicional propia de la relación:

- `prioridad`
- `fecha_asignacion`

Por esta razón, la tabla pivote no solamente conecta las entidades, sino que también almacena datos relacionados con la asignación de una etiqueta a un pendiente.

La combinación de `pendiente_id` y `etiqueta_id` funciona como clave primaria compuesta, evitando que una misma etiqueta sea asignada dos veces al mismo pendiente.

---

## 4. Políticas de borrado

Se utilizaron diferentes políticas de borrado dependiendo de la relación entre las entidades.

### Cascade

Se utiliza `cascadeOnDelete()` cuando un registro depende directamente de otro y no tendría sentido conservarlo después de eliminar el registro principal.

Ejemplos:

- Si se elimina un Usuario, se eliminan sus Cursos.
- Si se elimina un Usuario, se eliminan sus Horarios.
- Si se elimina un Usuario, se eliminan sus Pendientes.
- Si se elimina un Pendiente, se eliminan sus Recordatorios.
- Si se elimina un Pendiente o una Etiqueta, se eliminan las relaciones correspondientes de `pendiente_etiqueta`.

### Null on delete

En la relación entre Curso y Pendiente se utiliza `nullOnDelete()`.

El campo:

`curso_id`

de la tabla `pendientes` permite valores nulos.

Esto se decidió porque un pendiente puede continuar existiendo aunque el curso asociado sea eliminado.

De esta forma no se pierde la información del pendiente.

---

## 5. Restricciones e índices

El modelo incluye restricciones para mantener la integridad de los datos.

Entre ellas se encuentran:

- `correo` del Usuario es único.
- `usuario_id` y `codigo` forman una combinación única en Curso.
- `estado` de Pendiente posee un índice para facilitar consultas por estado.
- Se utilizan claves foráneas para mantener la integridad referencial.
- Se utilizan campos `nullable` cuando la información es opcional.

---

## 6. Modelos Eloquent

Los modelos implementan las relaciones correspondientes mediante los métodos de Eloquent:

- `hasMany()`
- `belongsTo()`
- `belongsToMany()`

También se utiliza `$fillable` para controlar la asignación masiva.

En el modelo Usuario se utiliza `$hidden` para evitar que el campo `contrasena` sea incluido en las respuestas serializadas.

Se utilizan casts en campos que requieren conversión de tipo, como fechas y valores enteros.

---

## 7. Factories y Seeders

Se implementaron factories para generar información de prueba para las entidades del sistema.

El `DatabaseSeeder` genera inicialmente 20 usuarios.

Para cada usuario se generan:

- 3 cursos
- 2 horarios por curso
- 3 pendientes por curso

También se generan etiquetas que posteriormente se asocian a los pendientes mediante la relación muchos a muchos.

Cada asociación almacena:

- una prioridad
- una fecha de asignación

Además, se genera un recordatorio para cada pendiente.

Esto permite mantener datos coherentes entre las diferentes entidades relacionadas.

---

## 8. Consultas implementadas

Se implementaron scopes reutilizables en el modelo Pendiente.

### Consulta por estado

Permite obtener pendientes según su estado.

Ejemplo:

`Pendiente::estado('pendiente')->get();`

### Consulta por curso

Permite obtener los pendientes asociados a un curso determinado.

Ejemplo:

`Pendiente::delCurso($cursoId)->get();`

### Pendientes próximos

Permite obtener pendientes con fecha límite y ordenarlos cronológicamente.

Ejemplo:

`Pendiente::proximos()->get();`

Los scopes también pueden combinarse.

Ejemplo:

`Pendiente::estado('pendiente')->proximos()->get();`

### Consulta de agregación

También se implementó una consulta para contar la cantidad de pendientes agrupados por estado.

Se utiliza:

- `COUNT(*)`
- `GROUP BY estado`

Ejemplo:

`Pendiente::selectRaw('estado, COUNT(*) as cantidad')->groupBy('estado')->get();`

Esta consulta permite conocer cuántos pendientes existen en cada estado.