using Microsoft.EntityFrameworkCore;
using RentingBartops.Models;

namespace RentingBartops.Data;

public class AppDbContext : DbContext
{
    public AppDbContext(DbContextOptions<AppDbContext> options) : base(options) { }

    public DbSet<Maquina> Maquinas => Set<Maquina>();
    public DbSet<Solicitud> Solicitudes => Set<Solicitud>();
    public DbSet<BloqueoFecha> BloqueosFecha => Set<BloqueoFecha>();

    protected override void OnModelCreating(ModelBuilder modelBuilder)
    {
        modelBuilder.Entity<Maquina>(e =>
        {
            e.HasKey(m => m.Id);
            e.Property(m => m.Nombre).IsRequired().HasMaxLength(200);
            e.Property(m => m.Descripcion).HasMaxLength(1000);
            e.Property(m => m.ImagenUrl).HasMaxLength(500);
            e.Property(m => m.PrecioBase).HasColumnType("decimal(10,2)");
            e.Property(m => m.Categoria).HasConversion<string>();
        });

        modelBuilder.Entity<Solicitud>(e =>
        {
            e.HasKey(s => s.Id);
            e.Property(s => s.NombreCliente).IsRequired().HasMaxLength(100);
            e.Property(s => s.EmailCliente).IsRequired().HasMaxLength(200);
            e.Property(s => s.TelefonoCliente).IsRequired().HasMaxLength(30);
            e.Property(s => s.Estado).HasConversion<string>();
        });

        modelBuilder.Entity<BloqueoFecha>(e =>
        {
            e.HasKey(b => b.Id);
            e.Property(b => b.Motivo).HasMaxLength(300);
        });
    }
}
