#!/bin/sh

if [ "${APP_ENV:-}" = production ]; then
    for variable in APP_KEY APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD CORS_ALLOWED_ORIGINS; do
        if [ -z "$(printenv "$variable")" ]; then
            echo "Falta la variable de producción $variable." >&2
            exit 1
        fi
    done

    if [ "$APP_DEBUG" != false ]; then
        echo "Producción requiere APP_DEBUG=false." >&2
        exit 1
    fi

    case "$DB_CONNECTION" in
        pgsql)
            if [ "${DB_SSLMODE:-}" != verify-full ] || [ ! -r "${DB_SSLROOTCERT:-}" ]; then
                echo "PostgreSQL requiere DB_SSLMODE=verify-full y un certificado DB_SSLROOTCERT legible." >&2
                exit 1
            fi
            ;;
        mysql)
            if [ ! -r "${MYSQL_ATTR_SSL_CA:-}" ]; then
                echo "MySQL requiere un certificado MYSQL_ATTR_SSL_CA legible." >&2
                exit 1
            fi
            ;;
        *)
            echo "Producción requiere DB_CONNECTION=pgsql o mysql." >&2
            exit 1
            ;;
    esac

    case "$CORS_ALLOWED_ORIGINS" in
        *\**)
            echo "CORS_ALLOWED_ORIGINS debe contener orígenes explícitos." >&2
            exit 1
            ;;
    esac
fi
