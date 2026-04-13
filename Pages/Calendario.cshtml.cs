using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using RentingBartops.Data;
using RentingBartops.Models;

namespace RentingBartops.Pages;

public class CalendarioModel : PageModel
{
    private readonly IAlquilerRepository _repo;

    public CalendarioModel(IAlquilerRepository repo) => _repo = repo;

    [BindProperty(SupportsGet = true)]
    public DateOnly? Desde { get; set; }

    [BindProperty(SupportsGet = true)]
    public DateOnly? Hasta { get; set; }

    public bool Consultado { get; private set; }
    public List<Maquina> MaquinasDisponibles { get; private set; } = [];
    public List<Maquina> MaquinasOcupadas { get; private set; } = [];

    public void OnGet()
    {
        if (Desde is null || Hasta is null) return;
        Consultado = true;

        foreach (var maquina in _repo.GetAllMaquinas())
        {
            bool ocupada = _repo.GetBloqueosByMaquina(maquina.Id)
                .Any(b => b.FechaInicio <= Hasta && b.FechaFin >= Desde);

            // También comprobar solicitudes aprobadas
            bool reservada = _repo.GetAllSolicitudes()
                .Any(s => s.MaquinaId == maquina.Id
                       && s.Estado == EstadoSolicitud.Aprobada
                       && s.FechaInicio <= Hasta && s.FechaFin >= Desde);

            if (ocupada || reservada)
                MaquinasOcupadas.Add(maquina);
            else
                MaquinasDisponibles.Add(maquina);
        }
    }
}
