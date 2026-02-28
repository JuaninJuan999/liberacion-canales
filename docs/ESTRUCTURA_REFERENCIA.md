# Estructura de referencia – Liberación de Canales

Este proyecto está alineado con la especificación contenida en la carpeta **Liberacion de canales** del repositorio.

## Tablas y modelos (configuración general)

| Especificación        | Tabla / modelo en el proyecto     | Estado   |
|-----------------------|-----------------------------------|----------|
| configuracion_app     | `ConfiguracionApp`                | ✓        |
| users / roles         | `User`, `Rol`                     | ✓        |
| productos             | `Producto`                        | ✓        |
| tipos_hallazgo        | `TipoHallazgo`                    | ✓        |
| ubicaciones           | `Ubicacion`                       | ✓        |
| lados                 | `Lado`                            | ✓        |
| puestos_trabajo       | `PuestoTrabajo`                   | ✓        |
| operarios             | `Operario`                        | ✓        |
| operarios_por_dia     | `OperarioPorDia`                  | ✓        |
| animales_procesados   | `AnimalProcesado`                 | ✓        |
| registros_hallazgos   | `RegistroHallazgo`                | ✓        |
| indicadores_diarios   | `IndicadorDiario`                 | ✓        |
| menu_modulos          | `MenuModulo`                      | ✓        |
| filtros_usuario       | `FiltroUsuario`                   | ✓        |

## Catálogos según especificación

- **Roles:** Admin, Calidad, Operaciones, Gerencia (`RolSeeder`).
- **Productos:** Media Canal 1 Lengua, Media Canal 2 Cola (`ProductoSeeder`).
- **Tipos de hallazgo:** Cobertura de grasa, Hematomas, Cortes en la pierna, Sobrebariga rota (`TipoHallazgoSeeder`).
- **Ubicaciones:** Cadera, Pierna (`UbicacionSeeder`).
- **Lados:** Par, Impar (`LadoSeeder`).
- **Puestos de trabajo:** Primera par, Primera impar, Segunda par, etc. (`PuestoTrabajoSeeder`).

## Controladores y Livewire

Los controladores y componentes Livewire siguen la estructura de la especificación; los componentes Livewire viven en `app/Livewire/` (convención estándar de Laravel):

- `HallazgoController`, `DashboardController`, `IndicadorController`
- `OperarioController`, `AnimalesController`, `UsuarioController`
- `RegistroHallazgo`, `HistorialRegistros`, `DashboardDia`, `DashboardMes`, `IndicadoresDia`, `GestionOperariosDia`, `AsignacionOperarios`

## Servicios y observers

- `CalculadoraIndicadores`, `GeneradorReportes` en `app/Services/`
- `RegistroHallazgoObserver` en `app/Observers/`

---

*Documento generado para mantener coherencia con la carpeta **Liberacion de canales**.*
