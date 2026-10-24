using System.Text.Json.Serialization;

namespace Gemdy_AspNet.Dtos;

public class PendienteResponse
{
    [JsonPropertyName("id")]
    public long Id { get; set; }

    [JsonPropertyName("usuario_id")]
    public long UsuarioId { get; set; }

    [JsonPropertyName("curso_id")]
    public long? CursoId { get; set; }

    [JsonPropertyName("titulo")]
    public string Titulo { get; set; } = null!;

    [JsonPropertyName("descripcion")]
    public string? Descripcion { get; set; }

    [JsonPropertyName("estado")]
    public string Estado { get; set; } = null!;

    [JsonPropertyName("fecha_limite")]
    public string? FechaLimite { get; set; }

    [JsonPropertyName("hora_pendiente")]
    public string? HoraPendiente { get; set; }
}