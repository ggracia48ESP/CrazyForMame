using Microsoft.EntityFrameworkCore;
using RentingBartops.Models;

namespace RentingBartops.Data;

public class EfAlquilerRepository : IAlquilerRepository
{
    private readonly AppDbContext _db;

    public EfAlquilerRepository(AppDbContext db) => _db = db;

    // ── Maquina ────────────────────────────────────────────────────────────
    public IReadOnlyList<Maquina> GetAllMaquinas() =>
        _db.Maquinas.OrderBy(m => m.Id).ToList().AsReadOnly();

    public Maquina? GetMaquinaById(int id) =>
        _db.Maquinas.Find(id);

    public void AddMaquina(Maquina maquina)
    {
        _db.Maquinas.Add(maquina);
        _db.SaveChanges();
    }

    public void UpdateMaquina(Maquina maquina)
    {
        _db.Maquinas.Update(maquina);
        _db.SaveChanges();
    }

    // ── Solicitud ──────────────────────────────────────────────────────────
    public IReadOnlyList<Solicitud> GetAllSolicitudes() =>
        _db.Solicitudes.OrderByDescending(s => s.FechaCreacion).ToList().AsReadOnly();

    public Solicitud? GetSolicitudById(int id) =>
        _db.Solicitudes.Find(id);

    public void AddSolicitud(Solicitud solicitud)
    {
        _db.Solicitudes.Add(solicitud);
        _db.SaveChanges();
    }

    public void UpdateSolicitud(Solicitud solicitud)
    {
        _db.Solicitudes.Update(solicitud);
        _db.SaveChanges();
    }

    // ── BloqueoFecha ───────────────────────────────────────────────────────
    public IReadOnlyList<BloqueoFecha> GetAllBloqueos() =>
        _db.BloqueosFecha.ToList().AsReadOnly();

    public IReadOnlyList<BloqueoFecha> GetBloqueosByMaquina(int maquinaId) =>
        _db.BloqueosFecha.Where(b => b.MaquinaId == maquinaId).ToList().AsReadOnly();

    public void AddBloqueo(BloqueoFecha bloqueo)
    {
        _db.BloqueosFecha.Add(bloqueo);
        _db.SaveChanges();
    }

    public void RemoveBloqueo(int id)
    {
        var bloqueo = _db.BloqueosFecha.Find(id);
        if (bloqueo is not null)
        {
            _db.BloqueosFecha.Remove(bloqueo);
            _db.SaveChanges();
        }
    }
}
