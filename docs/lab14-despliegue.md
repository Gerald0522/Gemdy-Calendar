# Laboratorio 14 · Despliegue de Gemdy Calendar

- Web pública: https://gemdy-calendar.pages.dev/
- API pública: https://gemdy-calendar.onrender.com/api
- Estado de la API: https://gemdy-calendar.onrender.com/up

## Arquitectura

La web de Cloudflare Pages y la app Ionic para Android consumen la API Laravel mediante HTTPS y tokens Bearer de Sanctum. Render ejecuta la API en Docker y esta se conecta a PostgreSQL en Aiven mediante TLS. GitHub Actions prueba Laravel con SQLite en memoria, prueba Ionic, compila los clientes y construye la imagen. El trabajo de despliegue solo se ejecuta en un push a `main` cuando los dos trabajos anteriores terminan correctamente.

Aiven cerró el alta de instancias gratuitas de MySQL el 6 de noviembre de 2026. El proyecto usa PostgreSQL en el plan Free para mantener el despliegue sin costo. [Aviso oficial de Aiven](https://aiven.io/changelog/507f8b1b-dd8f-4841-ac4a-940ac492b688)

```mermaid
flowchart LR
    Web["Web · Cloudflare Pages"] -->|HTTPS| API["Laravel · Render"]
    Android["Ionic · APK firmado"] -->|HTTPS| API
    API -->|PostgreSQL con TLS| DB["PostgreSQL · Aiven Free"]
    CI["GitHub Actions"] -->|Deploy Hook| API
    GitHub["GitHub · main"] -->|Compilación de Pages| Web
```

## Entornos y despliegue

En desarrollo usar `.env.example` como base para `.env`. La web que sirve Laravel usa `/api` y la app conserva su URL local para el emulador.

1. En Aiven se creó el servicio PostgreSQL Free `pg-2c7ef480`. Descargar su certificado público a `certs/aiven-ca.pem` y añadir el certificado al repositorio.
2. En Render crear un Web Service Docker desde la raíz del repositorio, con puerto `8080`, verificación en `/up` y Auto-Deploy desactivado. El contenedor arranca como `www-data`, valida el entorno, ejecuta las migraciones y genera las cachés de Laravel.
3. En Cloudflare Pages conectar el repositorio GitHub y elegir `main` como rama de producción. Usar la raíz del repositorio, el comando `npm --prefix mobile ci && npm --prefix mobile run build:web`, el directorio de salida `dist-web` y las variables `NODE_VERSION=22` y `VITE_API_URL=https://gemdy-calendar.onrender.com/api`.
4. Registrar `RENDER_DEPLOY_HOOK` como secreto de GitHub Actions y `VITE_API_URL` como variable del repositorio. El trabajo `deploy` llama al hook de Render únicamente después de que pasen las pruebas y la compilación web.

| Variables de Render | Valores |
| --- | --- |
| `APP_ENV`, `APP_DEBUG`, `APP_URL`, `PORT` | `production`, `false`, URL HTTPS de Render, `8080` |
| `APP_KEY` | Generarla una vez con `php artisan key:generate --show` y guardarla en Render |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | `pgsql` y los datos de conexión de Aiven |
| `DB_SSLMODE`, `DB_SSLROOTCERT` | `verify-full`, `/var/www/html/certs/aiven-ca.pem` |
| `CORS_ALLOWED_ORIGINS` | `https://<proyecto>.pages.dev,https://localhost,capacitor://localhost` |
| `LOG_CHANNEL`, `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | `stderr`, `database`, `database`, `sync` |

## Artefactos

| Artefacto | Construcción | Tamaño |
| --- | --- | --- |
| Web, `dist-web/` | `npm --prefix mobile ci` y `npm --prefix mobile run build:web` con `VITE_API_URL` | 48 294 bytes en producción, 16 895 bytes en el ZIP de carga. |
| Imagen de la API | `docker build -t gemdy-api .` | 260 047 286 bytes en la compilación local inicial. CI registra el tamaño de cada nueva imagen. |
| Compilación de Ionic, `mobile/dist/` | `npm --prefix mobile run build` | 1 249 705 bytes con la API pública. |
| APK de distribución | Compilación y firma de Android | 3 540 348 bytes. Firma Android v2 y v3 verificada. |

El APK está disponible en [GitHub Releases](https://github.com/Gerald0522/Gemdy-Calendar/releases/tag/v1.0.0-lab14). SHA-256 del archivo: `0d9da01bd830561d19d44997ede6075006e697875db3dd5ae1a67d8f311b65ca`.

## APK firmado

Definir `VITE_API_URL` con la misma URL HTTPS que usa la web y ejecutar desde `mobile/`:

```sh
npm ci
npm run build
npx cap sync android
```

En Android Studio abrir `mobile/android` y usar **Build → Generate Signed Bundle / APK → APK → release**. Usar el almacén de claves del equipo o crearlo una sola vez y conservarlo fuera del repositorio. Verificar el APK con `apksigner verify --verbose <archivo.apk>`, medir el archivo y adjuntarlo a un GitHub Release.

Para esta compilación se usó Gradle 8.14.3. Gradle 9.7.1, definido en el wrapper del repositorio, elimina una API interna requerida por el Android Gradle Plugin 8.13.

## Verificación y evidencias

- **Pruebas locales:** 58 pruebas de Laravel y 18 de Ionic aprobadas. El contenedor ejecutó las 12 migraciones con SQLite, devolvió 200 en `/up` y 401 en `/api/pendientes` sin token. La imagen ejecuta como `www-data` y el arranque sin `APP_KEY` terminó con un error explícito.
- **Producción y TLS:** la portada web y `/up` respondieron 200 sobre HTTPS con validación del certificado. `/api/pendientes` sin token devolvió 401 y la solicitud de preflight desde el dominio de Pages devolvió 204 con el origen correcto.
- **Migraciones:** Render registró la creación de la tabla de migraciones, la ejecución de migraciones en PostgreSQL y el inicio exitoso del servicio. [Registro del despliegue inicial](https://dashboard.render.com/web/srv-dbvrsgnf3r2c73fui350/deploys/dep-dbvrsh7f3r2c73fui4u0)
- **Publicación inicial:** Render construyó desde `codex/lab14-despliegue`. Cloudflare Pages recibió el ZIP de la compilación de producción mediante carga directa. Sigue pendiente activar la publicación automática desde `main`.
- **Acción exitosa:** pendiente el enlace de la ejecución de `main` con los trabajos `api`, `web` y `deploy` en verde, más el estado del despliegue en Render.
- **Acción detenida:** la prueba deliberadamente fallida se ejecutó en una rama de evidencia. GitHub Actions registró `api` fallido, `web` exitoso y `deploy` omitido. [Ejecución de GitHub Actions](https://github.com/Gerald0522/Gemdy-Calendar/actions/runs/44669986165)
- **Secretos:** revisar que la web compilada solo contiene la URL pública de la API y que el repositorio y la imagen excluyen los archivos de entorno y de firma.
