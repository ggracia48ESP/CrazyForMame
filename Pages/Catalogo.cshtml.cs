using Microsoft.AspNetCore.Mvc;
using Microsoft.AspNetCore.Mvc.RazorPages;
using RentingBartops.Data;
using RentingBartops.Models;

namespace RentingBartops.Pages;

public class CatalogoModel : PageModel
{
    private readonly IAlquilerRepository _repo;

    public CatalogoModel(IAlquilerRepository repo) => _repo = repo;

    [BindProperty(SupportsGet = true)]
    public string? Filtro { get; set; }

    public IReadOnlyList<Maquina> Maquinas { get; private set; } = [];

    public void OnGet()
    {
        var todas = _repo.GetAllMaquinas();
        if (Enum.TryParse<CategoriaMaquina>(Filtro, out var cat))
            Maquinas = todas.Where(m => m.Categoria == cat).ToList().AsReadOnly();
        else
            Maquinas = todas;
    }
}
