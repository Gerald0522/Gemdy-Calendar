using Gemdy_AspNet.Data;
using Gemdy_AspNet.Dtos;
using Gemdy_AspNet.Models;
using Microsoft.EntityFrameworkCore;
using Gemdy_AspNet.Exceptions;
using System.Data;

namespace Gemdy_AspNet.Services;

public class PendienteService
{
    private readonly GemdyDbContext _context;

    public PendienteService(GemdyDbContext context)
    {
        _context = context;
    }

    public async Task<object> ListarAsync(
        long usuarioId,
        bool esAdmin,
        string? estado = null,
        long? cursoId = null,
        bool proximos = false,
        string? q = null,
        int page = 1,
        int perPage = 10)
    {
        var query = _context.Pendientes
            .AsNoTracking()
            .AsQueryable();

        if (!esAdmin)
        {
            query = query.Where(
                p => p.UsuarioId == usuarioId
            );
        }

        if (!string.IsNullOrWhiteSpace(estado))
        {
            query = query.Where(
                p => p.Estado == estado
            );
        }

        if (cursoId.HasValue)
        {
            query = query.Where(
                p => p.CursoId == cursoId.Value
            );
        }

        if (!string.IsNullOrWhiteSpace(q))
        {
            var termino = q.Trim();

            query = query.Where(p =>
                p.Titulo.Contains(termino) ||
                (
                    p.Descripcion != null &&
                    p.Descripcion.Contains(termino)
                )
            );
        }

        if (proximos)
        {
            var hoy = DateTime.Today;

            query = query.Where(
                p =>
                    p.FechaLimite != null &&
                    p.FechaLimite >= hoy
            );
        }

        page = Math.Max(page, 1);
        perPage = Math.Clamp(perPage, 1, 50);

        var total = await query.CountAsync();

        var pendientes = await query
            .OrderBy(p => p.FechaLimite)
            .Skip((page - 1) * perPage)
            .Take(perPage)
            .ToListAsync();

        var data = pendientes
            .Select(MapToResponse)
            .ToList();

        var lastPage = (int)Math.Ceiling(
            total / (double)perPage
        );

        return new
        {
            data,
            meta = new
            {
                current_page = page,
                per_page = perPage,
                total,
                last_page = lastPage
            }
        };
    }

    public async Task<PendienteResponse?> ObtenerAsync(
        long id,
        long usuarioId,
        bool esAdmin)
    {
        var pendiente = await _context.Pendientes
            .AsNoTracking()
            .FirstOrDefaultAsync(
                p => p.Id == id
            );

        if (pendiente is null)
        {
            return null;
        }

        if (
            !esAdmin &&
            pendiente.UsuarioId != usuarioId
        )
        {
            return null;
        }

        return MapToResponse(pendiente);
    }

    public async Task<PendienteResponse> CrearAsync(
        long usuarioAutenticadoId,
        bool esAdmin,
        CreatePendienteRequest request)
    {
        var usuarioExiste = await _context.Usuarios
            .AnyAsync(
                u => u.Id == request.UsuarioId
            );

        if (!usuarioExiste)
        {
            throw new RequestValidationException(
                "El usuario seleccionado no existe."
            );
        }

        var usuarioId = esAdmin
            ? request.UsuarioId
            : usuarioAutenticadoId;

        if (request.CursoId.HasValue)
        {
            var curso = await _context.Cursos
                .FirstOrDefaultAsync(
                    c => c.Id == request.CursoId.Value
                );

            if (curso is null)
            {
                throw new RequestValidationException(
                    "El curso seleccionado no existe."
                );
            }

            if (curso.UsuarioId != usuarioId)
            {
                throw new BusinessRuleException(
                    "El curso seleccionado no pertenece al usuario."
                );
            }
        }

        var estadosPermitidos = new[]
        {
            "pendiente",
            "en_progreso",
            "completado"
        };

        if (!estadosPermitidos.Contains(request.Estado))
        {
            throw new RequestValidationException(
                "El estado debe ser pendiente, en_progreso o completado."
            );
        }

        if (
            request.FechaLimite.HasValue &&
            request.FechaLimite.Value <
            DateOnly.FromDateTime(DateTime.Today)
        )
        {
            throw new BusinessRuleException(
                "La fecha límite no puede ser anterior a hoy."
            );
        }

        var pendiente = new Pendiente
        {
            UsuarioId = usuarioId,
            CursoId = request.CursoId,
            Titulo = request.Titulo,
            Descripcion = request.Descripcion,
            Estado = request.Estado,

            FechaLimite = request.FechaLimite.HasValue
                ? request.FechaLimite.Value.ToDateTime(
                    TimeOnly.MinValue
                )
                : null,

            HoraPendiente = request.HoraPendiente,

            CreatedAt = DateTime.UtcNow,
            UpdatedAt = DateTime.UtcNow
        };

        _context.Pendientes.Add(pendiente);

        await _context.SaveChangesAsync();

        return MapToResponse(pendiente);
    }

    public async Task<PendienteResponse?> ActualizarAsync(
        long id,
        long usuarioId,
        bool esAdmin,
        UpdatePendienteRequest request)
    {
        var pendiente = await _context.Pendientes
            .FirstOrDefaultAsync(
                p => p.Id == id
            );

        if (pendiente is null)
        {
            return null;
        }

        if (
            !esAdmin &&
            pendiente.UsuarioId != usuarioId
        )
        {
            return null;
        }

        if (request.CursoId.HasValue)
        {
            var curso = await _context.Cursos
                .FirstOrDefaultAsync(
                    c => c.Id == request.CursoId.Value
                );

            if (curso is null)
            {
                throw new RequestValidationException(
                    "El curso seleccionado no existe."
                );
            }

            if (
                curso.UsuarioId !=
                pendiente.UsuarioId
            )
            {
                throw new BusinessRuleException(
                    "El curso seleccionado no pertenece al usuario."
                );
            }
        }

        if (!string.IsNullOrWhiteSpace(request.Estado))
        {
            var estadosPermitidos = new[]
            {
                "pendiente",
                "en_progreso",
                "completado"
            };

            if (
                !estadosPermitidos.Contains(
                    request.Estado
                )
            )
            {
                throw new RequestValidationException(
                    "El estado debe ser pendiente, en_progreso o completado."
                );
            }

            pendiente.Estado = request.Estado;
        }

        if (request.Titulo is not null)
        {
            pendiente.Titulo = request.Titulo;
        }

        if (request.Descripcion is not null)
        {
            pendiente.Descripcion =
                request.Descripcion;
        }

        if (request.CursoId.HasValue)
        {
            pendiente.CursoId =
                request.CursoId;
        }

        if (request.FechaLimite.HasValue)
        {
            if (
                request.FechaLimite.Value <
                DateOnly.FromDateTime(DateTime.Today)
            )
            {
                throw new BusinessRuleException(
                    "La fecha límite no puede ser anterior a hoy."
                );
            }

            pendiente.FechaLimite =
                request.FechaLimite.Value.ToDateTime(
                    TimeOnly.MinValue
                );
        }

        if (request.HoraPendiente.HasValue)
        {
            pendiente.HoraPendiente =
                request.HoraPendiente.Value;
        }

        pendiente.UpdatedAt = DateTime.UtcNow;

        await _context.SaveChangesAsync();

        return MapToResponse(pendiente);
    }
private async Task<bool> TieneDependenciasAsync(
    long pendienteId)
{
    var connection =
        _context.Database.GetDbConnection();

    var cerrarConexion =
        connection.State != ConnectionState.Open;

    if (cerrarConexion)
    {
        await connection.OpenAsync();
    }

    try
    {
        await using var command =
            connection.CreateCommand();

        command.CommandText = """
            SELECT CASE
                WHEN EXISTS (
                    SELECT 1
                    FROM recordatorios
                    WHERE pendiente_id = $id
                )
                OR EXISTS (
                    SELECT 1
                    FROM pendiente_etiqueta
                    WHERE pendiente_id = $id
                )
                THEN 1
                ELSE 0
            END;
            """;

        var parameter =
            command.CreateParameter();

        parameter.ParameterName = "$id";
        parameter.Value = pendienteId;

        command.Parameters.Add(parameter);

        var result =
            await command.ExecuteScalarAsync();

        return Convert.ToInt32(result) == 1;
    }
    finally
    {
        if (cerrarConexion)
        {
            await connection.CloseAsync();
        }
    }
}
   public async Task<bool> EliminarAsync(
    long id,
    long usuarioId,
    bool esAdmin)
{
    var pendiente = await _context.Pendientes
        .FirstOrDefaultAsync(
            p => p.Id == id
        );

    if (pendiente is null)
    {
        return false;
    }

    if (
        !esAdmin &&
        pendiente.UsuarioId != usuarioId
    )
    {
        return false;
    }

    var tieneDependencias =
        await TieneDependenciasAsync(id);

    if (tieneDependencias)
    {
        throw new BusinessRuleException(
            "No se puede eliminar el pendiente porque tiene dependencias asociadas."
        );
    }

    _context.Pendientes.Remove(pendiente);

    await _context.SaveChangesAsync();

    return true;
}

    public async Task<bool> ExisteAsync(long id)
    {
        return await _context.Pendientes
            .AsNoTracking()
            .AnyAsync(p => p.Id == id);
    }

    private static PendienteResponse MapToResponse(
        Pendiente pendiente)
    {
        return new PendienteResponse
        {
            Id = pendiente.Id,
            UsuarioId = pendiente.UsuarioId,
            CursoId = pendiente.CursoId,
            Titulo = pendiente.Titulo,
            Descripcion = pendiente.Descripcion,
            Estado = pendiente.Estado,

            FechaLimite = pendiente.FechaLimite?
                .ToString("yyyy-MM-dd"),

            HoraPendiente = pendiente.HoraPendiente?
                .ToString("HH:mm:ss")
        };
    }
}