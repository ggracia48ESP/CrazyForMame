using System.ComponentModel.DataAnnotations;
using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using RentingBartops.Data;
using RentingBartops.Models;

namespace RentingBartops.Pages;

public class SolicitudModel : PageModel
{
    private readonly IAlquilerRepository _repo;

    public SolicitudModel(IAlquilerRepository repo) => _repo = repo;

    [BindProperty]
    public SolicitudInput Input { get; set; } = new();

    public IReadOnlyList<Maquina> Maquinas { get; private set; } = [];
    public bool Enviado { get; private set; }
    public string? ErrorConflicto { get; private set; }

    public void OnGet(int? maquinaId, string? desde, string? hasta)
    {
        Maquinas = _repo.GetAllMaquinas();
        if (maquinaId.HasValue) Input.MaquinaId = maquinaId.Value;
        if (DateOnly.TryParse(desde, out var d)) Input.FechaInicio = d;
        if (DateOnly.TryParse(hasta, out var h)) Input.FechaFin = h;
    }

    public IActionResult OnPost()
    {
        Maquinas = _repo.GetAllMaquinas();

        if (!ModelState.IsValid) return Page();

        if (Input.FechaFin < Input.FechaInicio)
        {
            ModelState.AddModelError(nameof(Input.FechaFin), "La fecha de fin debe ser igual o posterior a la de inicio.");
            return Page();
        }

        // Comprobar conflictos de bloqueo
        bool bloqueo = _repo.GetBloqueosByMaquina(Input.MaquinaId)
            .Any(b => b.FechaInicio <= Input.FechaFin && b.FechaFin >= Input.FechaInicio);

        bool reservada = _repo.GetAllSolicitudes()
            .Any(s => s.MaquinaId == Input.MaquinaId
                   && s.Estado == EstadoSolicitud.Aprobada
                   && s.FechaInicio <= Input.FechaFin && s.FechaFin >= Input.FechaInicio);

        if (bloqueo || reservada)
        {
            ErrorConflicto = "La máquina no está disponible en las fechas seleccionadas. Por favor, elige otras fechas o consulta la disponibilidad.";
            return Page();
        }

        var solicitud = new Solicitud
        {
            MaquinaId = Input.MaquinaId,
            NombreCliente = Input.NombreCliente,
            EmailCliente = Input.EmailCliente,
            TelefonoCliente = Input.TelefonoCliente,
            FechaInicio = Input.FechaInicio,
            FechaFin = Input.FechaFin,
        };
        _repo.AddSolicitud(solicitud);

        Enviado = true;
        return Page();
    }
}

public class SolicitudInput
{
    [Required(ErrorMessage = "Selecciona una máquina.")]
    [Range(1, int.MaxValue, ErrorMessage = "Selecciona una máquina válida.")]
    public int MaquinaId { get; set; }

    [Required(ErrorMessage = "Indica la fecha de inicio.")]
    public DateOnly FechaInicio { get; set; } = DateOnly.FromDateTime(DateTime.Today);

    [Required(ErrorMessage = "Indica la fecha de fin.")]
    public DateOnly FechaFin { get; set; } = DateOnly.FromDateTime(DateTime.Today);

    [Required(ErrorMessage = "Introduce tu nombre.")]
    [MaxLength(100)]
    public string NombreCliente { get; set; } = string.Empty;

    [Required(ErrorMessage = "Introduce tu correo electrónico.")]
    [EmailAddress(ErrorMessage = "El formato del correo no es válido.")]
    public string EmailCliente { get; set; } = string.Empty;

    [Required(ErrorMessage = "Introduce tu teléfono.")]
    [Phone(ErrorMessage = "El formato del teléfono no es válido.")]
    public string TelefonoCliente { get; set; } = string.Empty;
}
