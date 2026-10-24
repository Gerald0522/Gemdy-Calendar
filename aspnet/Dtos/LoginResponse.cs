using System.Text.Json.Serialization;

namespace Gemdy_AspNet.Dtos;

public class LoginResponse
{
    [JsonPropertyName("message")]
    public string Message { get; set; } = null!;

    [JsonPropertyName("usuario")]
    public UsuarioLoginResponse Usuario { get; set; } = null!;

    [JsonPropertyName("token")]
    public string Token { get; set; } = null!;

    [JsonPropertyName("token_type")]
    public string TokenType { get; set; } = "Bearer";

    [JsonPropertyName("expires_at")]
    public DateTime ExpiresAt { get; set; }
}

public class UsuarioLoginResponse
{
    [JsonPropertyName("id")]
    public long Id { get; set; }

    [JsonPropertyName("nombre1")]
    public string Nombre1 { get; set; } = null!;

    [JsonPropertyName("nombre2")]
    public string? Nombre2 { get; set; }

    [JsonPropertyName("apellido1")]
    public string Apellido1 { get; set; } = null!;

    [JsonPropertyName("apellido2")]
    public string? Apellido2 { get; set; }

    [JsonPropertyName("correo")]
    public string Correo { get; set; } = null!;

    [JsonPropertyName("telefono")]
    public string? Telefono { get; set; }
}