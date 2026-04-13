using RentingBartops.Models;

namespace RentingBartops.Data;

public interface IAlquilerRepository
{
    // Maquina
    IReadOnlyList<Maquina> GetAllMaquinas();
    Maquina? GetMaquinaById(int id);
    void AddMaquina(Maquina maquina);
    void UpdateMaquina(Maquina maquina);

    // Solicitud
    IReadOnlyList<Solicitud> GetAllSolicitudes();
    Solicitud? GetSolicitudById(int id);
    void AddSolicitud(Solicitud solicitud);
    void UpdateSolicitud(Solicitud solicitud);

    // BloqueoFecha
    IReadOnlyList<BloqueoFecha> GetAllBloqueos();
    IReadOnlyList<BloqueoFecha> GetBloqueosByMaquina(int maquinaId);
    void AddBloqueo(BloqueoFecha bloqueo);
    void RemoveBloqueo(int id);
}
