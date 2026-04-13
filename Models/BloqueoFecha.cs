namespace RentingBartops.Models;

public class BloqueoFecha
{
    public int Id { get; set; }
    public int MaquinaId { get; set; }
    public DateOnly FechaInicio { get; set; }
    public DateOnly FechaFin { get; set; }
    public string? Motivo { get; set; }
}
