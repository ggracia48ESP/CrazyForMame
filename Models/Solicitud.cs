namespace RentingBartops.Models;

public enum EstadoSolicitud { Pendiente, Aprobada, Rechazada }

public class Solicitud
{
    public int Id { get; set; }
    public int MaquinaId { get; set; }
    public string NombreCliente { get; set; } = string.Empty;
    public string EmailCliente { get; set; } = string.Empty;
    public string TelefonoCliente { get; set; } = string.Empty;
    public DateOnly FechaInicio { get; set; }
    public DateOnly FechaFin { get; set; }
    public EstadoSolicitud Estado { get; set; } = EstadoSolicitud.Pendiente;
    public DateTime FechaCreacion { get; set; } = DateTime.UtcNow;
}
