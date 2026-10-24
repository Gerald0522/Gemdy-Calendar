using System.Security.Claims;
using Gemdy_AspNet.Dtos;
using Gemdy_AspNet.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace Gemdy_AspNet.Controllers;

[ApiController]
[Route("api/pendientes")]
[Authorize]
public class PendientesController : ControllerBase
{
    private readonly PendienteService _service;

    public PendientesController(PendienteService service)
    {
        _service = service;
    }

    [HttpGet]
    public async Task<IActionResult> Index(
        [FromQuery] string? estado,
        [FromQuery(Name = "curso_id")] long? cursoId,
        [FromQuery] bool proximos = false,
        [FromQuery] string? q = null,
        [FromQuery] int page = 1,
        [FromQuery(Name = "por_pagina")] int perPage = 10)
    {
        var usuarioId = ObtenerUsuarioId();
        var esAdmin = User.IsInRole("admin");

        var pendientes = await _service.ListarAsync(
            usuarioId,
            esAdmin,
            estado,
            cursoId,
            proximos,
            q,
            page,
            perPage
        );

        return Ok(pendientes);
    }

    [HttpGet("{id:long}")]
    public async Task<IActionResult> Show(long id)
    {
        var usuarioId = ObtenerUsuarioId();
        var esAdmin = User.IsInRole("admin");

        var pendiente = await _service.ObtenerAsync(
            id,
            usuarioId,
            esAdmin
        );

        if (pendiente is null)
        {
            var existe = await _service.ExisteAsync(id);

            if (existe)
            {
                return StatusCode(403, new
                {
                    message =
                        "No tiene permisos para acceder a este recurso."
                });
            }

            return NotFound(new
            {
                message = "Recurso no encontrado."
            });
        }

        return Ok(pendiente);
    }

    [HttpPost]
    public async Task<IActionResult> Store(
        [FromBody] CreatePendienteRequest request)
    {
        var usuarioId = ObtenerUsuarioId();
        var esAdmin = User.IsInRole("admin");

        var pendiente = await _service.CrearAsync(
            usuarioId,
            esAdmin,
            request
        );

        return Created(
            $"/api/pendientes/{pendiente.Id}",
            pendiente
        );
    }

    [HttpPut("{id:long}")]
    public async Task<IActionResult> Update(
        long id,
        [FromBody] UpdatePendienteRequest request)
    {
        var usuarioId = ObtenerUsuarioId();
        var esAdmin = User.IsInRole("admin");

        var pendiente = await _service.ActualizarAsync(
            id,
            usuarioId,
            esAdmin,
            request
        );

        if (pendiente is null)
        {
            var existe = await _service.ExisteAsync(id);

            if (existe)
            {
                return StatusCode(403, new
                {
                    message =
                        "No tiene permisos para acceder a este recurso."
                });
            }

            return NotFound(new
            {
                message = "Recurso no encontrado."
            });
        }

        return Ok(pendiente);
    }

    [HttpDelete("{id:long}")]
    public async Task<IActionResult> Destroy(long id)
    {
        var usuarioId = ObtenerUsuarioId();
        var esAdmin = User.IsInRole("admin");

        var eliminado = await _service.EliminarAsync(
            id,
            usuarioId,
            esAdmin
        );

        if (!eliminado)
        {
            var existe = await _service.ExisteAsync(id);

            if (existe)
            {
                return StatusCode(403, new
                {
                    message =
                        "No tiene permisos para acceder a este recurso."
                });
            }

            return NotFound(new
            {
                message = "Recurso no encontrado."
            });
        }

        return NoContent();
    }

    private long ObtenerUsuarioId()
    {
        var sub = User.FindFirstValue(
            ClaimTypes.NameIdentifier
        );

        if (sub is null)
        {
            sub = User.FindFirstValue("sub");
        }

        if (!long.TryParse(sub, out var usuarioId))
        {
            throw new UnauthorizedAccessException();
        }

        return usuarioId;
    }
}