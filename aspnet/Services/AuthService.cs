using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Gemdy_AspNet.Data;
using Gemdy_AspNet.Dtos;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;

namespace Gemdy_AspNet.Services;

public class AuthService
{
    private readonly GemdyDbContext _context;
    private readonly IConfiguration _configuration;

    public AuthService(
        GemdyDbContext context,
        IConfiguration configuration)
    {
        _context = context;
        _configuration = configuration;
    }

    public async Task<LoginResponse?> LoginAsync(LoginRequest request)
    {
        var usuario = await _context.Usuarios
            .FirstOrDefaultAsync(u => u.Correo == request.Correo);

        if (usuario is null)
        {
            return null;
        }

        var hash = usuario.Contrasena;

        // Compatibilidad con hashes bcrypt generados por Laravel ($2y$)
        if (hash.StartsWith("$2y$"))
        {
            hash = "$2b$" + hash[4..];
        }

        var passwordValida = BCrypt.Net.BCrypt.Verify(
            request.Contrasena,
            hash
        );

        if (!passwordValida)
        {
            return null;
        }

        var key = _configuration["Jwt:Key"]
            ?? throw new InvalidOperationException(
                "No se encontró Jwt:Key."
            );

        var issuer = _configuration["Jwt:Issuer"];
        var audience = _configuration["Jwt:Audience"];

        var expiresHours =
            _configuration.GetValue<int>("Jwt:ExpiresHours");

        var expiresAt = DateTime.UtcNow
            .AddHours(expiresHours);

        var claims = new List<Claim>
        {
            new(
                JwtRegisteredClaimNames.Sub,
                usuario.Id.ToString()
            ),
            new(
                JwtRegisteredClaimNames.Email,
                usuario.Correo
            ),
            new(
                ClaimTypes.Role,
                usuario.Rol
            )
        };

        var credentials = new SigningCredentials(
            new SymmetricSecurityKey(
                Encoding.UTF8.GetBytes(key)
            ),
            SecurityAlgorithms.HmacSha256
        );

        var token = new JwtSecurityToken(
            issuer: issuer,
            audience: audience,
            claims: claims,
            expires: expiresAt,
            signingCredentials: credentials
        );

        var tokenString =
            new JwtSecurityTokenHandler()
                .WriteToken(token);

        return new LoginResponse
        {
            Message = "Inicio de sesión exitoso.",
            Usuario = new UsuarioLoginResponse
            {
                Id = usuario.Id,
                Nombre1 = usuario.Nombre1,
                Nombre2 = usuario.Nombre2,
                Apellido1 = usuario.Apellido1,
                Apellido2 = usuario.Apellido2,
                Correo = usuario.Correo,
                Telefono = usuario.Telefono
            },
            Token = tokenString,
            TokenType = "Bearer",
            ExpiresAt = expiresAt
        };
    }
}

