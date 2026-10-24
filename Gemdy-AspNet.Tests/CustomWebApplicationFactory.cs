using Gemdy_AspNet.Data;
using Gemdy_AspNet.Models;
using Microsoft.AspNetCore.Hosting;
using Microsoft.AspNetCore.Mvc.Testing;
using Microsoft.Data.Sqlite;
using Microsoft.EntityFrameworkCore;
using Microsoft.Extensions.DependencyInjection;

namespace Gemdy_AspNet.Tests;

public class CustomWebApplicationFactory
    : WebApplicationFactory<Program>
{
    private const string TestJwtKey =
        "gemdy-aspnet-test-key-only-for-automated-tests";

    private readonly string? _previousJwtKey;

    private SqliteConnection? _connection;

    public CustomWebApplicationFactory()
    {
        _previousJwtKey =
            Environment.GetEnvironmentVariable(
                "Jwt__Key"
            );

        Environment.SetEnvironmentVariable(
            "Jwt__Key",
            TestJwtKey
        );
    }

    protected override void ConfigureWebHost(
        IWebHostBuilder builder)
    {
        builder.ConfigureServices(services =>
        {
            var descriptor = services
                .SingleOrDefault(d =>
                    d.ServiceType ==
                    typeof(DbContextOptions<GemdyDbContext>)
                );

            if (descriptor is not null)
            {
                services.Remove(descriptor);
            }

            _connection = new SqliteConnection(
                "Data Source=:memory:"
            );

            _connection.Open();

            services.AddDbContext<GemdyDbContext>(
                options =>
                {
                    options.UseSqlite(
                        _connection
                    );
                }
            );

            var serviceProvider =
                services.BuildServiceProvider();

            using var scope =
                serviceProvider.CreateScope();

            var context =
                scope.ServiceProvider
                    .GetRequiredService<GemdyDbContext>();

            context.Database.EnsureCreated();

            SeedDatabase(context);
        });
    }

    private static void SeedDatabase(
        GemdyDbContext context)
    {
        var usuario = new Usuario
        {
            Id = 1,
            Nombre1 = "Usuario",
            Nombre2 = null,
            Apellido1 = "Prueba",
            Apellido2 = null,
            Correo = "test@gemdy.local",
            Telefono = "88888888",
            Contrasena =
                BCrypt.Net.BCrypt.HashPassword(
                    "12345678"
                ),
            Rol = "estudiante",
            CreatedAt = DateTime.Now,
            UpdatedAt = DateTime.Now
        };

        context.Usuarios.Add(usuario);

        var curso = new Curso
        {
            Id = 1,
            UsuarioId = 1,
            Nombre = "Curso de Prueba",
            Codigo = "TEST-01",
            Semestre = "II-2026",
            Creditos = 3,
            CreatedAt = DateTime.Now,
            UpdatedAt = DateTime.Now
        };

        context.Cursos.Add(curso);

        var pendiente = new Pendiente
        {
            Id = 1,
            UsuarioId = 1,
            CursoId = 1,
            Titulo = "Pendiente de prueba",
            Descripcion =
                "Dato generado solo para tests",
            Estado = "pendiente",
            FechaLimite =
                DateTime.Today.AddDays(5),
            HoraPendiente =
                new TimeOnly(18, 0),
            CreatedAt = DateTime.Now,
            UpdatedAt = DateTime.Now
        };

        context.Pendientes.Add(
            pendiente
        );

        context.SaveChanges();
    }

    protected override void Dispose(
        bool disposing)
    {
        base.Dispose(disposing);

        if (disposing)
        {
            _connection?.Dispose();

            Environment.SetEnvironmentVariable(
                "Jwt__Key",
                _previousJwtKey
            );
        }
    }
}