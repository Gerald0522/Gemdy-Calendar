using Gemdy_AspNet.Dtos;
using Gemdy_AspNet.Services;
using Microsoft.AspNetCore.Mvc;

namespace Gemdy_AspNet.Controllers;

[ApiController]
[Route("api")]
public class AuthController : ControllerBase
{
    private readonly AuthService _authService;

    public AuthController(AuthService authService)
    {
        _authService = authService;
    }

    [HttpPost("login")]
    public async Task<IActionResult> Login(LoginRequest request)
    {
        if (!ModelState.IsValid)
        {
            return UnprocessableEntity(new
            {
                message = "Los datos proporcionados no son válidos.",
                errors = ModelState
                    .Where(x => x.Value?.Errors.Count > 0)
                    .ToDictionary(
                        x => x.Key,
                        x => x.Value!.Errors
                            .Select(e => e.ErrorMessage)
                            .ToArray()
                    )
            });
        }

        var response = await _authService.LoginAsync(request);

        if (response is null)
        {
            return Unauthorized(new
            {
                message = "Las credenciales proporcionadas no son válidas."
            });
        }

        return Ok(response);
    }
}