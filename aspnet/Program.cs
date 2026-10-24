using System.Text;
using Gemdy_AspNet.Data;
using Gemdy_AspNet.Services;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using Microsoft.AspNetCore.Mvc;
using Gemdy_AspNet.Middleware;

var builder = WebApplication.CreateBuilder(args);

// Base de datos SQLite compartida con Laravel.
// ASP.NET solo se conecta; no ejecutamos migraciones de EF.
builder.Services.AddDbContext<GemdyDbContext>(options =>
    options.UseSqlite(
        builder.Configuration.GetConnectionString("GemdyDatabase")
    )
);

// Servicios
builder.Services.AddScoped<AuthService>();
builder.Services.AddScoped<PendienteService>();

// Configuración JWT propia de ASP.NET
var jwtKey = builder.Configuration["Jwt:Key"]
    ?? throw new InvalidOperationException(
        "No se encontró la configuración Jwt:Key."
    );

var jwtIssuer = builder.Configuration["Jwt:Issuer"];
var jwtAudience = builder.Configuration["Jwt:Audience"];

builder.Services
    .AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuer = true,
            ValidateAudience = true,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,

            ValidIssuer = jwtIssuer,
            ValidAudience = jwtAudience,

            IssuerSigningKey = new SymmetricSecurityKey(
                Encoding.UTF8.GetBytes(jwtKey)
            ),

            ClockSkew = TimeSpan.Zero
        };
    });

// Controllers
builder.Services
    .AddControllers()
    .ConfigureApiBehaviorOptions(options =>
    {
        options.InvalidModelStateResponseFactory = context =>
        {
            var errors = context.ModelState
                .Where(x => x.Value?.Errors.Count > 0)
                .ToDictionary(
                    x => x.Key,
                    x => x.Value!.Errors
                        .Select(e => e.ErrorMessage)
                        .ToArray()
                );

            return new UnprocessableEntityObjectResult(new
            {
                message = "Los datos proporcionados no son válidos.",
                errors
            });
        };
    });

// OpenAPI / Swagger
builder.Services.AddEndpointsApiExplorer();

builder.Services.AddSwaggerGen(options =>
{
    options.AddSecurityDefinition(
        "Bearer",
        new Microsoft.OpenApi.Models.OpenApiSecurityScheme
        {
            Name = "Authorization",
            Type = Microsoft.OpenApi.Models.SecuritySchemeType.Http,
            Scheme = "bearer",
            BearerFormat = "JWT",
            In = Microsoft.OpenApi.Models.ParameterLocation.Header,
            Description = "Ingrese el token JWT."
        }
    );

    options.AddSecurityRequirement(
        new Microsoft.OpenApi.Models.OpenApiSecurityRequirement
        {
            {
                new Microsoft.OpenApi.Models.OpenApiSecurityScheme
                {
                    Reference =
                        new Microsoft.OpenApi.Models.OpenApiReference
                        {
                            Type =
                                Microsoft.OpenApi.Models.ReferenceType
                                    .SecurityScheme,

                            Id = "Bearer"
                        }
                },
                Array.Empty<string>()
            }
        }
    );
});

var app = builder.Build();

if (app.Environment.IsDevelopment())
{
    /*
     * COMPATIBILIDAD SWAGGER UI
     *
     * Algunas versiones de Swagger UI no reconocen
     * correctamente OpenAPI 3.0.4.
     *
     * Interceptamos únicamente swagger.json y cambiamos
     * la versión declarada de 3.0.4 a 3.0.3.
     *
     * Esto NO cambia endpoints, DTOs, JWT, SQLite
     * ni ninguna funcionalidad de la API.
     */
    app.UseWhen(
        context =>
            context.Request.Path.StartsWithSegments(
                "/swagger/v1/swagger.json"
            ),
        branch =>
        {
            branch.Use(async (context, next) =>
            {
                var originalBody = context.Response.Body;

                await using var memoryStream = new MemoryStream();

                context.Response.Body = memoryStream;

                await next();

                memoryStream.Position = 0;

                var swaggerJson =
                    await new StreamReader(memoryStream)
                        .ReadToEndAsync();

                swaggerJson = swaggerJson.Replace(
                    "\"openapi\": \"3.0.4\"",
                    "\"openapi\": \"3.0.3\""
                );

                var bytes = Encoding.UTF8.GetBytes(swaggerJson);

                context.Response.Body = originalBody;

                context.Response.ContentLength = bytes.Length;

                await context.Response.Body.WriteAsync(bytes);
            });
        }
    );

    app.UseSwagger();

    app.UseSwaggerUI(options =>
    {
        options.SwaggerEndpoint(
            "/swagger/v1/swagger.json",
            "Gemdy-AspNet v1"
        );
    });
}

// Manejo global de excepciones
app.UseMiddleware<ExceptionMiddleware>();

app.UseHttpsRedirection();

app.UseAuthentication();
app.UseAuthorization();

app.MapControllers();

app.Run();

public partial class Program { }