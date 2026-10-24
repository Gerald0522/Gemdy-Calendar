using System.ComponentModel.DataAnnotations;
using System.Text.Json.Serialization;

namespace Gemdy_AspNet.Dtos;

public class CreatePendienteRequest
{
    [JsonPropertyName("usuario_id")]
    [Required(ErrorMessage = "El usuario es obligatorio.")]
    public long UsuarioId { get; set; }

    [JsonPropertyName("curso_id")]
    public long? CursoId { get; set; }

    [JsonPropertyName("titulo")]
    [Required(ErrorMessage = "El título es obligatorio.")]
    [StringLength(
        150,
        ErrorMessage = "El título no puede superar los 150 caracteres."
    )]
    public string Titulo { get; set; } = null!;

    [JsonPropertyName("descripcion")]
    [StringLength(
        1000,
        ErrorMessage = "La descripción no puede superar los 1000 caracteres."
    )]
    public string? Descripcion { get; set; }

    [JsonPropertyName("estado")]
    [Required(ErrorMessage = "El estado es obligatorio.")]
    public string Estado { get; set; } = null!;

    [JsonPropertyName("fecha_limite")]
    public DateOnly? FechaLimite { get; set; }

    [JsonPropertyName("hora_pendiente")]
    public TimeOnly? HoraPendiente { get; set; }
}