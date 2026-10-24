using System.ComponentModel.DataAnnotations;
using System.Text.Json.Serialization;

namespace Gemdy_AspNet.Dtos;

public class UpdatePendienteRequest
{
    [JsonPropertyName("usuario_id")]
    public long? UsuarioId { get; set; }

    [JsonPropertyName("curso_id")]
    public long? CursoId { get; set; }

    [JsonPropertyName("titulo")]
    [StringLength(
        150,
        ErrorMessage = "El título no puede superar los 150 caracteres."
    )]
    public string? Titulo { get; set; }

    [JsonPropertyName("descripcion")]
    [StringLength(
        1000,
        ErrorMessage = "La descripción no puede superar los 1000 caracteres."
    )]
    public string? Descripcion { get; set; }

    [JsonPropertyName("estado")]
    public string? Estado { get; set; }

    [JsonPropertyName("fecha_limite")]
    public DateOnly? FechaLimite { get; set; }

    [JsonPropertyName("hora_pendiente")]
    public TimeOnly? HoraPendiente { get; set; }
}