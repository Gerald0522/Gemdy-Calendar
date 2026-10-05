# GEMDY Calendar — Guía de interfaz, mapa de navegación y bosquejos

Lab 07 — Guía de interfaz de usuario  
**Proyecto:** GEMDY Calendar  
**Archivo de entrega sugerido:** `docs/lab07-guia-ui.md`  



## ====== Sistema visual ======

### Paleta de colores

#### Colores base

| Fondo general | `#F6F8FC` | Fondo de páginas y áreas de trabajo |
| Superficies | `#FFFFFF` | Tarjetas, paneles, formularios y modales |
| Texto principal | `#172033` | Títulos, contenido y datos importantes |
| Texto secundario | `#667085` | Descripciones, fechas y metadatos |
| Bordes | `#E7EAF0` | Separadores, contornos y límites de componentes |

#### Azul de marca e interacción

| Azul GEMDY | `#4F7CFF` | Botón primario, enlaces, selección y foco |
| Azul hover | `#3B64E8` | Estado hover de acciones azules |
| Azul suave | `#EAF0FF` | Fondo del elemento activo y selección suave |

El azul GEMDY se reserva principalmente para acciones e interacciones. Los colores de categoría deben aparecer en eventos, indicadores, iconos, bordes o fondos pequeños, y se procurará que no ocupen grandes áreas de la interfaz.

#### Colores por categoría

| Categoría|Color base |Fondo suave |Texto / icono sobre fondo suave |
| Categoría                   | Color base | Fondo suave | Texto / icono sobre fondo suave |
|-----------------------------|------------|-------------|----------------------------------|
| Clase                       | `#4F7CFF` | `#EAF0FF`   | `#2F5BD9`                        |
| Tarea                       | `#8B7CF6` | `#F0EDFF`   | `#6A5AE0`                        |
| Examen                      | `#F28B82` | `#FFF0EF`   | `#C9483E`                        |
| Actividad                   | `#65C99A` | `#EAF9F2`   | `#2E8F62`                        |
| Estudio                     | `#F5C95B` | `#FFF8DF`   | `#9A7209`                        |
| Recordatorio / notificación | `#5BC7F2` | `#EAF9FE`   | `#1C86B0`                        |


### Tipografía y escala

| Elemento | Fuente | Tamaño |

Título principal de landing | Instrument Sans | 56–64 px | 700 |
Título principal dentro de la app (H1) | Instrument Sans | 28 px | 600 |
Título secundario (H2) | Instrument Sans | 20 px | 600 |
Texto de cuerpo | Instrument Sans | 15–16 px | 400 |
Etiquetas y metadatos | Instrument Sans | 13 px | 400–500 |
Slogan de la marca en Instrument Serif con cursiva y según el contexto regular o cursiva.

Instrument Sans se utiliza en toda la interfaz funcional. Instrument Serif en cursiva se reserva para el lema “Dale forma a tu tiempo” y, como máximo, uno o dos acentos de marca en la landing. Mantener una jerarquía tipográfica consistente.

### Espaciado

Utilizar una escala basada en múltiplos de 4 px para facilitar la consistencia:

| Token | Valor | Uso sugerido |
|---|---:|---|
| space-1 | 4 px | Separación mínima entre icono y texto |
| space-2 | 8 px | Elementos estrechamente relacionados |
| space-3 | 12 px | Separación en campos y grupos compactos |
| space-4 | 16 px | Padding interno habitual |
| space-6` | 24 px | Separación entre grupos de contenido |
| space-8 | 32 px | Separación entre secciones |
| space-12` | 48 px | Separación amplia entre bloques principales |

En la aplicación, mantener márgenes y separaciones regulares. Las tarjetas deben tener espacio suficiente para leer su contenido sin que la pantalla se vea saturada.

### Bordes, radios y sombras

- **Tarjetas:** radio de 16 px, borde de 1 px `#E7EAF0` y sombra suave `0 4px 20px rgba(23,32,51,0.06)`.
- **Campos de entrada:** radio de 12 px.
- **Botones:** forma de píldora.
- **Modales:** radio de 20 px y fondo superpuesto `rgba(23,32,51,0.4)`.
- **Contornos:** `#E7EAF0`.


## Componentes de interfaz

### Botones

| Tipo | Apariencia | Uso |
|---|---|---|
| Primario | Fondo `#4F7CFF`, texto blanco, forma de píldora | Acción principal, como guardar o crear |
| Hover primario | Fondo `#3B64E8` | Puntero sobre el botón |
| Secundario | Superficie blanca o suave, borde discreto | Acción alternativa |
| Terciario / enlace | Texto azul, sin contenedor prominente | Acciones auxiliares, como “Ver todas” |
| Deshabilitado | Contraste visual reducido y sin interacción | Acción que no se puede ejecutar todavía |

### Contraste y accesibilidad
Los colores de la interfaz fueron seleccionados procurando una lectura clara entre texto y fondo. Se utiliza como referencia una relación de contraste mínima de 4.5:1 para texto normal. Además, las categorías no se identifican únicamente mediante colores, sino también mediante texto e iconos.

### Campos de entrada

- Cada campo tiene una etiqueta visible.
- Los ejemplos o placeholders son ayudas, no reemplazan la etiqueta.
- En foco: borde azul GEMDY y anillo suave `#EAF0FF`.
- En error: mostrar un mensaje junto al campo que explique cómo corregirlo.
- En deshabilitado: indicar visualmente que no está disponible e impedir la edición.
- Validar los datos antes de confirmar el registro y conservar los valores cuando sea seguro hacerlo tras un error.

### Tarjetas de resumen

Las tarjetas muestran una información principal, un título corto y, si hace falta, una descripción secundaria. En Inicio pueden presentar:

- Próxima clase.
- Tareas pendientes.
- Próximo examen.
- Actividades del día.

Usar superficies blancas, radio de 16 px, borde suave y un icono o acento de categoría. Evitar que todas las tarjetas compitan por la atención con colores intensos.


### Prioridad y estados de tarea

Mostrar la prioridad mediante puntos y la palabra Alta, Media o Baja. 

### Navegación lateral y barra superior

**Barra lateral de escritorio:**
- Logo y lema.
- Navegación: Inicio, Calendario, Generar horario y Tareas.
- Separador.
- Configuración, Perfil y Cerrar sesión.
- La sección activa usa fondo `#EAF0FF`, texto e icono azules y una barra indicadora de 3 px.

**Barra superior:**
- Buscador con el texto “Buscar evento, tarea o actividad…”.
- Campana de notificaciones con panel asociado.
- Avatar con nombre y menú desplegable.



## Estados de los componentes

La interfaz debe especificar los estados siguientes para los componentes pertinentes:

| Estado | Comportamiento visual y funcional |
|---|---|
| Normal | Apariencia base del componente |
| Hover | Cambio sutil de fondo, borde o elevación en elementos interactivos |
| Foco | Contorno visible, preferiblemente azul, para navegación por teclado |
| Cargando | Skeletons o indicador de progreso; evitar aparentar que los datos ya están disponibles |
| Vacío | Mensaje que explique que no hay contenido y sugiera una acción posible |
| Éxito | Confirmación breve de que la acción se completó |
| Error | Mensaje específico y accionable, cercano al elemento afectado |
| Deshabilitado | Apariencia diferenciada y acción bloqueada |

**Textos de referencia:**

- Estado vacío de tareas: “No tienes tareas pendientes.” / “Agrega una nueva tarea para comenzar.”
- Éxito: “Tarea registrada correctamente.”
- Error de formulario: “Revisa este campo e inténtalo de nuevo.”


### Descripción de los flujos

1. Una persona visitante puede entrar por la landing e iniciar sesión o crear una cuenta.
2. Después de autenticarse, llega a Inicio, que funciona como resumen y punto de acceso a los módulos.
3. Desde Inicio puede ir al Calendario, Generar horario o Tareas.
4. En Calendario puede consultar eventos y abrir el detalle para editar o eliminar.
5. En Generar horario puede definir y guardar sus bloques de horario.
6. En Tareas puede registrar, consultar, editar o eliminar tareas.
7. Perfil y Configuración se acceden desde la navegación secundaria o el menú de usuario.
8. Las notificaciones informan sobre recordatorios y actividades relevantes.




