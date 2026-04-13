using System.Collections.Concurrent;
using RentingBartops.Models;

namespace RentingBartops.Data;

public class InMemoryAlquilerRepository : IAlquilerRepository
{
    private readonly ConcurrentDictionary<int, Maquina> _maquinas = new();
    private readonly ConcurrentDictionary<int, Solicitud> _solicitudes = new();
    private readonly ConcurrentDictionary<int, BloqueoFecha> _bloqueos = new();

    private int _maquinaCounter;
    private int _solicitudCounter;
    private int _bloqueoCounter;

    // Maquina
    public IReadOnlyList<Maquina> GetAllMaquinas() =>
        _maquinas.Values.ToList().AsReadOnly();

    public Maquina? GetMaquinaById(int id) =>
        _maquinas.TryGetValue(id, out var m) ? m : null;

    public void AddMaquina(Maquina maquina)
    {
        maquina.Id = Interlocked.Increment(ref _maquinaCounter);
        _maquinas[maquina.Id] = maquina;
    }

    public void UpdateMaquina(Maquina maquina) =>
        _maquinas[maquina.Id] = maquina;

    // Solicitud
    public IReadOnlyList<Solicitud> GetAllSolicitudes() =>
        _solicitudes.Values.ToList().AsReadOnly();

    public Solicitud? GetSolicitudById(int id) =>
        _solicitudes.TryGetValue(id, out var s) ? s : null;

    public void AddSolicitud(Solicitud solicitud)
    {
        solicitud.Id = Interlocked.Increment(ref _solicitudCounter);
        _solicitudes[solicitud.Id] = solicitud;
    }

    public void UpdateSolicitud(Solicitud solicitud) =>
        _solicitudes[solicitud.Id] = solicitud;

    // BloqueoFecha
    public IReadOnlyList<BloqueoFecha> GetAllBloqueos() =>
        _bloqueos.Values.ToList().AsReadOnly();

    public IReadOnlyList<BloqueoFecha> GetBloqueosByMaquina(int maquinaId) =>
        _bloqueos.Values.Where(b => b.MaquinaId == maquinaId).ToList().AsReadOnly();

    public void AddBloqueo(BloqueoFecha bloqueo)
    {
        bloqueo.Id = Interlocked.Increment(ref _bloqueoCounter);
        _bloqueos[bloqueo.Id] = bloqueo;
    }

    public void RemoveBloqueo(int id) =>
        _bloqueos.TryRemove(id, out _);
}
