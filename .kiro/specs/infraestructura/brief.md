# Brief: infraestructura

## Problem
Sin una base bien definida (modelos, repositorio, layout), los specs posteriores tendrían que tomar decisiones de arquitectura ad-hoc, generando inconsistencias y deuda técnica desde el inicio.

## Current State
Proyecto nuevo, sin código existente. Working directory: localhost.

## Desired Outcome
- Proyecto ASP.NET Core 10 ejecutable en localhost
- Modelos de dominio definidos: `Maquina`, `Solicitud`, `BloqueoFecha`
- Repositorio en memoria implementado con interfaz (IRepository) intercambiable
- Layout base HTML/CSS con HTMX cargado: cabecera, navegación, pie de página
- Página de inicio funcional (placeholder) que sirva como punto de entrada

## Approach
ASP.NET Core 10 con Razor Pages. Patrón Repository con interfaz `IAlquilerRepository` registrada en DI como Singleton (en memoria). Modelos en carpeta `Models/`. HTMX cargado via CDN en el layout base `_Layout.cshtml`.

## Scope
- **In**: Estructura de proyecto, modelos de dominio, repositorio en memoria, layout base, DI wiring, página de inicio básica
- **Out**: Lógica de negocio, vistas de catálogo, formularios, panel de admin, emails

## Boundary Candidates
- Modelos de dominio (Maquina, Solicitud, BloqueoFecha)
- Capa de repositorio (interfaz + implementación en memoria)
- Layout y assets CSS base

## Out of Boundary
- Persistencia real (SQLite, SQL Server) — se añadirá en el futuro
- Autenticación — se tratará en panel-administracion con solución mínima

## Upstream / Downstream
- **Upstream**: ninguno
- **Downstream**: todos los specs dependen de esta infraestructura

## Existing Spec Touchpoints
- **Extends**: ninguno
- **Adjacent**: ninguno

## Constraints
- .NET 10 SDK
- HTMX via CDN (sin npm/bundler)
- Repositorio en memoria thread-safe (ConcurrentDictionary o similar)
