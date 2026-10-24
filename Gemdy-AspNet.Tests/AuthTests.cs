using System.Net;
using System.Net.Http.Json;
using System.Text.Json;

namespace Gemdy_AspNet.Tests;

public class AuthTests
    : IClassFixture<CustomWebApplicationFactory>
{
    private readonly HttpClient _client;

    public AuthTests(
        CustomWebApplicationFactory factory)
    {
        _client = factory.CreateClient();
    }

    [Fact]
    public async Task LoginValido_Retorna200YToken()
    {
        var request = new
        {
            correo = "test@gemdy.local",
            contrasena = "12345678"
        };

        var response = await _client.PostAsJsonAsync(
            "/api/login",
            request
        );

        Assert.Equal(
            HttpStatusCode.OK,
            response.StatusCode
        );

        var json = await response.Content
            .ReadFromJsonAsync<JsonElement>();

        Assert.True(
            json.TryGetProperty(
                "token",
                out var token
            )
        );

        Assert.False(
            string.IsNullOrWhiteSpace(
                token.GetString()
            )
        );
    }

    [Fact]
    public async Task GetPendientes_ConTokenValido_Retorna200()
    {
        var loginRequest = new
        {
            correo = "test@gemdy.local",
            contrasena = "12345678"
        };

        var loginResponse = await _client.PostAsJsonAsync(
            "/api/login",
            loginRequest
        );

        var loginJson = await loginResponse.Content
            .ReadFromJsonAsync<JsonElement>();

        var token = loginJson
            .GetProperty("token")
            .GetString();

        _client.DefaultRequestHeaders.Authorization =
            new System.Net.Http.Headers.AuthenticationHeaderValue(
                "Bearer",
                token
            );

        var response = await _client.GetAsync(
            "/api/pendientes?page=1&por_pagina=5"
        );

        Assert.Equal(
            HttpStatusCode.OK,
            response.StatusCode
        );
    }

    [Fact]
    public async Task CrearPendiente_Valido_Retorna201YLocation()
    {
        var loginRequest = new
        {
            correo = "test@gemdy.local",
            contrasena = "12345678"
        };

        var loginResponse = await _client.PostAsJsonAsync(
            "/api/login",
            loginRequest
        );

        var loginJson = await loginResponse.Content
            .ReadFromJsonAsync<JsonElement>();

        var token = loginJson
            .GetProperty("token")
            .GetString();

        _client.DefaultRequestHeaders.Authorization =
            new System.Net.Http.Headers.AuthenticationHeaderValue(
                "Bearer",
                token
            );

        var request = new
        {
            usuario_id = 1,
            curso_id = 1,
            titulo = "Pendiente creado desde test",
            descripcion = "Prueba automatizada de creación",
            estado = "pendiente",
            fecha_limite = DateTime.Today
                .AddDays(7)
                .ToString("yyyy-MM-dd"),
            hora_pendiente = "18:00:00"
        };

        var response = await _client.PostAsJsonAsync(
            "/api/pendientes",
            request
        );

        Assert.Equal(
            HttpStatusCode.Created,
            response.StatusCode
        );

        Assert.NotNull(
            response.Headers.Location
        );
    }
}