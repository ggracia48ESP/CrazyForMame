using Microsoft.EntityFrameworkCore;
using RentingBartops.Data;
using RentingBartops.Models;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddRazorPages();

// Base de datos SQLite con EF Core
builder.Services.AddDbContext<AppDbContext>(options =>
    options.UseSqlite(builder.Configuration.GetConnectionString("Default")
                     ?? "Data Source=rentingbartops.db"));

builder.Services.AddScoped<IAlquilerRepository, EfAlquilerRepository>();

var app = builder.Build();

// Aplicar migraciones y sembrar datos iniciales
using (var scope = app.Services.CreateScope())
{
    var db = scope.ServiceProvider.GetRequiredService<AppDbContext>();
    db.Database.Migrate();

    if (!db.Maquinas.Any())
    {
        db.Maquinas.AddRange(
            new Maquina { Nombre = "Bartop Retro Clásico", Descripcion = "Gabinete bartop de madera con más de 2000 juegos clásicos. Pantalla CRT de 21\". Perfecto para ambientes vintage.", Categoria = CategoriaMaquina.Grande, ImagenUrl = "/img/bartop-clasico.jpg", PrecioBase = 180 },
            new Maquina { Nombre = "Bartop Pixel Art", Descripcion = "Diseño artesanal con ilustraciones de píxel art pintadas a mano. 1500 juegos y pantalla LCD de 24\".", Categoria = CategoriaMaquina.Grande, ImagenUrl = "/img/bartop-pixel.jpg", PrecioBase = 200 },
            new Maquina { Nombre = "Mini Bartop Retro", Descripcion = "Versión compacta ideal para mesas y espacios pequeños. 800 juegos, pantalla de 15\". Fácil de transportar.", Categoria = CategoriaMaquina.Pequeña, ImagenUrl = "/img/mini-bartop.jpg", PrecioBase = 120 },
            new Maquina { Nombre = "Bartop Neon City", Descripcion = "Gabinete con iluminación LED neon y temática cyberpunk. Pantalla IPS de 27\", altavoces integrados.", Categoria = CategoriaMaquina.Grande, ImagenUrl = "/img/bartop-neon.jpg", PrecioBase = 220 },
            new Maquina { Nombre = "Mini Bartop Madera Natural", Descripcion = "Acabado en madera de haya vaporizada. Minimalista y elegante. Pantalla de 17\", 600 juegos seleccionados.", Categoria = CategoriaMaquina.Pequeña, ImagenUrl = "/img/mini-madera.jpg", PrecioBase = 130 }
        );
        db.SaveChanges();

        // Bloqueos de ejemplo vinculados a los IDs ya asignados
        var m1 = db.Maquinas.First(m => m.Nombre == "Bartop Retro Clásico");
        var m2 = db.Maquinas.First(m => m.Nombre == "Bartop Pixel Art");
        db.BloqueosFecha.AddRange(
            new BloqueoFecha { MaquinaId = m1.Id, FechaInicio = DateOnly.FromDateTime(DateTime.Today.AddDays(5)), FechaFin = DateOnly.FromDateTime(DateTime.Today.AddDays(7)), Motivo = "Reservado" },
            new BloqueoFecha { MaquinaId = m2.Id, FechaInicio = DateOnly.FromDateTime(DateTime.Today.AddDays(10)), FechaFin = DateOnly.FromDateTime(DateTime.Today.AddDays(12)), Motivo = "Reservado" }
        );
        db.SaveChanges();
    }
}

if (!app.Environment.IsDevelopment())
{
    app.UseExceptionHandler("/Error");
    app.UseHsts();
}

app.UseHttpsRedirection();
app.UseRouting();
app.UseAuthorization();
app.MapStaticAssets();
app.MapRazorPages().WithStaticAssets();

app.Run();
