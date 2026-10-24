using Gemdy_AspNet.Exceptions;
using System.Net;
using System.Text.Json;

namespace Gemdy_AspNet.Middleware;

public class ExceptionMiddleware
{
    private readonly RequestDelegate _next;

    public ExceptionMiddleware(RequestDelegate next)
    {
        _next = next;
    }

    public async Task InvokeAsync(HttpContext context)
    {
        try
        {
            await _next(context);
        }
        catch (BusinessRuleException ex)
        {
            context.Response.StatusCode =
                (int)HttpStatusCode.Conflict;

            context.Response.ContentType =
                "application/json";

            var response = new
            {
                message = ex.Message
            };

            await context.Response.WriteAsync(
                JsonSerializer.Serialize(response)
            );
        }
        catch (RequestValidationException ex)
        {
            context.Response.StatusCode = 422;

            context.Response.ContentType =
                "application/json";

            var response = new
            {
                message =
                    "Los datos proporcionados no son válidos.",

                errors = new
                {
                    general = new[]
                    {
                        ex.Message
                    }
                }
            };

            await context.Response.WriteAsync(
                JsonSerializer.Serialize(response)
            );
        }
    }
}