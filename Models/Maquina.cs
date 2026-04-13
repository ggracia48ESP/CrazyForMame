namespace RentingBartops.Models;

public enum CategoriaMaquina { Grande, Pequeña }

public class Maquina
{
    public int Id { get; set; }
    public string Nombre { get; set; } = string.Empty;
    public string Descripcion { get; set; } = string.Empty;
    public CategoriaMaquina Categoria { get; set; }
    public string ImagenUrl { get; set; } = string.Empty;
    public decimal PrecioBase { get; set; }
}
