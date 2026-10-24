namespace Gemdy_AspNet.Models;

public class Curso
{
    public long Id { get; set; }

    public long UsuarioId { get; set; }

    public string Nombre { get; set; } = null!;

    public string Codigo { get; set; } = null!;

    public string Semestre { get; set; } = null!;

    public byte Creditos { get; set; }

    public DateTime? CreatedAt { get; set; }

    public DateTime? UpdatedAt { get; set; }

    public Usuario Usuario { get; set; } = null!;

    public ICollection<Pendiente> Pendientes { get; set; } = new List<Pendiente>();
}