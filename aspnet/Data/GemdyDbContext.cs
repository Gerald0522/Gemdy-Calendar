using Gemdy_AspNet.Models;
using Microsoft.EntityFrameworkCore;

namespace Gemdy_AspNet.Data;

public class GemdyDbContext : DbContext
{
    public GemdyDbContext(DbContextOptions<GemdyDbContext> options)
        : base(options)
    {
    }

    public DbSet<Usuario> Usuarios => Set<Usuario>();
    public DbSet<Curso> Cursos => Set<Curso>();
    public DbSet<Pendiente> Pendientes => Set<Pendiente>();

     public DbSet<PersonalAccessToken> PersonalAccessTokens => Set<PersonalAccessToken>();
    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        base.OnModelCreating(modelBuilder);

        // -------------------------
        // USUARIOS
        // -------------------------
        modelBuilder.Entity<Usuario>(entity =>
        {
            entity.ToTable("usuarios");

            entity.HasKey(e => e.Id);

            entity.Property(e => e.Id)
                .HasColumnName("id");

            entity.Property(e => e.Nombre1)
                .HasColumnName("nombre1")
                .HasMaxLength(50)
                .IsRequired();

            entity.Property(e => e.Nombre2)
                .HasColumnName("nombre2")
                .HasMaxLength(50);

            entity.Property(e => e.Apellido1)
                .HasColumnName("apellido1")
                .HasMaxLength(50)
                .IsRequired();

            entity.Property(e => e.Apellido2)
                .HasColumnName("apellido2")
                .HasMaxLength(50);

            entity.Property(e => e.Correo)
                .HasColumnName("correo")
                .HasMaxLength(100)
                .IsRequired();

            entity.HasIndex(e => e.Correo)
                .IsUnique();

            entity.Property(e => e.Telefono)
                .HasColumnName("telefono")
                .HasMaxLength(20);

            entity.Property(e => e.Contrasena)
                .HasColumnName("contrasena")
                .IsRequired();

            entity.Property(e => e.Rol)
                .HasColumnName("rol")
                .HasMaxLength(20)
                .HasDefaultValue("estudiante")
                .IsRequired();

            entity.Property(e => e.CreatedAt)
                .HasColumnName("created_at");

            entity.Property(e => e.UpdatedAt)
                .HasColumnName("updated_at");
        });

        // -------------------------
        // CURSOS
        // -------------------------
        modelBuilder.Entity<Curso>(entity =>
        {
            entity.ToTable("cursos");

            entity.HasKey(e => e.Id);

            entity.Property(e => e.Id)
                .HasColumnName("id");

            entity.Property(e => e.UsuarioId)
                .HasColumnName("usuario_id")
                .IsRequired();

            entity.Property(e => e.Nombre)
                .HasColumnName("nombre")
                .HasMaxLength(100)
                .IsRequired();

            entity.Property(e => e.Codigo)
                .HasColumnName("codigo")
                .HasMaxLength(20)
                .IsRequired();

            entity.Property(e => e.Semestre)
                .HasColumnName("semestre")
                .HasMaxLength(20)
                .IsRequired();

            entity.Property(e => e.Creditos)
                .HasColumnName("creditos")
                .IsRequired();

            entity.Property(e => e.CreatedAt)
                .HasColumnName("created_at");

            entity.Property(e => e.UpdatedAt)
                .HasColumnName("updated_at");

            entity.HasIndex(e => new
            {
                e.UsuarioId,
                e.Codigo
            })
            .IsUnique();

            entity.HasOne(e => e.Usuario)
                .WithMany(e => e.Cursos)
                .HasForeignKey(e => e.UsuarioId)
                .OnDelete(DeleteBehavior.Cascade);
        });

        // -------------------------
        // PENDIENTES
        // -------------------------
        modelBuilder.Entity<Pendiente>(entity =>
        {
            entity.ToTable("pendientes");

            entity.HasKey(e => e.Id);

            entity.Property(e => e.Id)
                .HasColumnName("id");

            entity.Property(e => e.UsuarioId)
                .HasColumnName("usuario_id")
                .IsRequired();

            entity.Property(e => e.CursoId)
                .HasColumnName("curso_id");

            entity.Property(e => e.Titulo)
                .HasColumnName("titulo")
                .HasMaxLength(150)
                .IsRequired();

            entity.Property(e => e.Descripcion)
                .HasColumnName("descripcion");

            entity.Property(e => e.Estado)
                .HasColumnName("estado")
                .HasMaxLength(20)
                .HasDefaultValue("pendiente")
                .IsRequired();

            entity.Property(e => e.FechaLimite)
                .HasColumnName("fecha_limite");

            entity.Property(e => e.HoraPendiente)
                .HasColumnName("hora_pendiente");

            entity.Property(e => e.CreatedAt)
                .HasColumnName("created_at");

            entity.Property(e => e.UpdatedAt)
                .HasColumnName("updated_at");

            entity.HasIndex(e => e.Estado);

            entity.HasOne(e => e.Usuario)
                .WithMany(e => e.Pendientes)
                .HasForeignKey(e => e.UsuarioId)
                .OnDelete(DeleteBehavior.Cascade);

            entity.HasOne(e => e.Curso)
                .WithMany(e => e.Pendientes)
                .HasForeignKey(e => e.CursoId)
                .OnDelete(DeleteBehavior.SetNull);
        });




        // -------------------------
        // PersonalAccessToken
        // -------------------------
       modelBuilder.Entity<PersonalAccessToken>(entity =>
{
    entity.ToTable("personal_access_tokens");

    entity.HasKey(e => e.Id);

    entity.Property(e => e.Id)
        .HasColumnName("id");

    entity.Property(e => e.TokenableType)
        .HasColumnName("tokenable_type")
        .IsRequired();

    entity.Property(e => e.TokenableId)
        .HasColumnName("tokenable_id")
        .IsRequired();

    entity.Property(e => e.Name)
        .HasColumnName("name")
        .IsRequired();

    entity.Property(e => e.Token)
        .HasColumnName("token")
        .HasMaxLength(64)
        .IsRequired();

    entity.HasIndex(e => e.Token)
        .IsUnique();

    entity.Property(e => e.Abilities)
        .HasColumnName("abilities");

    entity.Property(e => e.LastUsedAt)
        .HasColumnName("last_used_at");

    entity.Property(e => e.ExpiresAt)
        .HasColumnName("expires_at");

    entity.Property(e => e.CreatedAt)
        .HasColumnName("created_at");

    entity.Property(e => e.UpdatedAt)
        .HasColumnName("updated_at");

    entity.HasIndex(e => new
    {
        e.TokenableType,
        e.TokenableId
    });
});
    }
}