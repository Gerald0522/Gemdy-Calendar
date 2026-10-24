namespace Gemdy_AspNet.Models;

public class Usuario
{
    public long Id { get; set; }

    public string Nombre1 { get; set; } = null!;

    public string? Nombre2 { get; set; }

    public string Apellido1 { get; set; } = null!;

    public string? Apellido2 { get; set; }

    public string Correo { get; set; } = null!;

    public string? Telefono { get; set; }

    public string Contrasena { get; set; } = null!;

    public string Rol { get; set; } = "estudiante";

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public ICollection<Curso> Cursos { get; set; } = new List<Curso>();

    public ICollection<Pendiente> Pendientes { get; set; } = new List<Pendiente>();
}