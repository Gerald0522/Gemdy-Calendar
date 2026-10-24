namespace Gemdy_AspNet.Models;

public class Pendiente
{
    public long Id { get; set; }

    public long UsuarioId { get; set; }

    public long? CursoId { get; set; }

    public string Titulo { get; set; } = null!;

    public string? Descripcion { get; set; }

    public string Estado { get; set; } = "pendiente";

    public DateTime? FechaLimite { get; set; }

    public TimeOnly? HoraPendiente { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public Usuario Usuario { get; set; } = null!;

    public Curso? Curso { get; set; }
}