# ── Etapa 1: build ────────────────────────────────────────────────────────
FROM mcr.microsoft.com/dotnet/sdk:10.0 AS build
WORKDIR /src

# Restaurar dependencias primero (capa cacheada si no cambia el csproj)
COPY RentingBartops.csproj .
RUN dotnet restore

# Copiar el resto y publicar en modo Release
COPY . .
RUN dotnet publish -c Release -o /app/publish --no-restore

# ── Etapa 2: runtime ───────────────────────────────────────────────────────
FROM mcr.microsoft.com/dotnet/aspnet:10.0 AS runtime
WORKDIR /app

# Copiar el artefacto publicado
COPY --from=build /app/publish .

# Puerto estándar de ASP.NET Core en contenedores
ENV ASPNETCORE_URLS=http://+:8080
EXPOSE 8080

ENTRYPOINT ["dotnet", "RentingBartops.dll"]
