# Documentacion tecnica profesional - Liberacion de Canales

**Proyecto:** Liberacion de Canales  
**Tipo de sistema:** Aplicacion web operativa para registro, control, seguimiento y analisis de hallazgos en canales  
**Framework principal:** Laravel 12  
**Fecha de documentacion:** 20 de julio de 2026  
**Repositorio:** `liberacion-canales`

---

## 1. Resumen ejecutivo

Liberacion de Canales es un sistema desarrollado para apoyar el control de calidad y la trazabilidad operativa en una linea de proceso. La aplicacion permite registrar hallazgos por media canal, clasificar eventos de tolerancia cero, controlar animales procesados, administrar operarios por puesto de trabajo, generar indicadores diarios y mensuales, emitir reportes y mantener seguimiento de modulos especializados como verificacion PCC, titulacion de acido lactico, consumo de acido lactico y tiempo de usabilidad.

El sistema esta construido sobre una arquitectura Laravel con componentes Livewire. Esto permite desarrollar pantallas interactivas sin exponer una API JavaScript compleja para cada formulario. La logica de negocio principal reside en modelos Eloquent, componentes Livewire, controladores HTTP, observers y servicios de soporte.

La solucion utiliza PostgreSQL como base de datos principal y puede consultar una base PostgreSQL externa de trazabilidad para el modulo de Verificacion PCC. Tambien incorpora broadcasting en tiempo real con Laravel Reverb para notificar nuevos hallazgos a otros usuarios conectados.

---

## 2. Objetivo funcional del programa

El objetivo del programa es centralizar los registros relacionados con la liberacion de canales y convertirlos en indicadores de seguimiento utiles para operacion, calidad, administracion y gerencia.

El sistema cubre los siguientes procesos:

- Registro de hallazgos sobre medias canales.
- Registro de hallazgos de tolerancia cero.
- Conteo de animales procesados por fecha operativa.
- Calculo automatico de indicadores diarios y agregados mensuales.
- Gestion de operarios, puestos de trabajo y asignaciones por dia.
- Administracion de usuarios, roles y permisos por modulo.
- Dashboards diarios, mensuales, semanales y anuales.
- Exportacion de historiales y reportes a Excel y PDF.
- Verificacion PCC usando datos internos y, si esta configurada, datos externos de trazabilidad.
- Registro de titulacion y consumo de acido lactico.
- Medicion del tiempo de uso de usuarios dentro de la aplicacion.
- Notificaciones en tiempo real cuando se registran nuevos hallazgos.
- Soporte para empaquetado movil Android mediante Capacitor.

---

## 3. Lenguajes, frameworks y tecnologias

### 3.1 Backend

- **PHP 8.2 o superior:** lenguaje principal del servidor.
- **Laravel 12:** framework principal para rutas, controladores, ORM, validacion, migraciones, jobs, consola y estructura de aplicacion.
- **Eloquent ORM:** capa de acceso a datos mediante modelos como `RegistroHallazgo`, `IndicadorDiario`, `User`, `MenuModulo`, `VerificacionPccRegistro` y otros.
- **Livewire 3:** componentes interactivos del frontend renderizados desde PHP.
- **Livewire Volt:** pantallas de autenticacion generadas con la convencion de Breeze/Volt.
- **Laravel Breeze:** base de autenticacion y vistas iniciales.
- **Laravel Reverb:** servidor WebSocket para eventos en tiempo real.
- **Laravel Queue:** configurada con driver `database`.
- **DomPDF:** generacion de documentos PDF.
- **Maatwebsite Excel:** importacion y exportacion de archivos Excel.
- **Intervention Image:** soporte para manejo de imagenes.

### 3.2 Frontend

- **Blade:** motor de plantillas de Laravel.
- **Livewire:** interactividad de formularios y dashboards sin SPA completa.
- **JavaScript ES Modules:** inicializacion de graficas, notificaciones y utilidades del navegador.
- **Vite:** bundler de assets.
- **Tailwind CSS:** framework de estilos.
- **Chart.js:** graficas de indicadores.
- **chartjs-plugin-datalabels:** etiquetas y anotaciones en graficas.
- **Laravel Echo + Pusher JS:** cliente WebSocket para recibir eventos de Reverb.

### 3.3 Base de datos e integraciones

- **PostgreSQL:** base de datos principal (`DB_CONNECTION=pgsql`).
- **PostgreSQL externo opcional:** conexion `pgsql_trazabilidad` para leer datos de insensibilizacion del sistema externo de trazabilidad.
- **Sesiones en base de datos:** `SESSION_DRIVER=database`.
- **Cache en base de datos:** `CACHE_STORE=database`.

### 3.4 Movil

- **Capacitor 7:** empaquetado Android con configuracion en `capacitor.config.json`.
- **Android project:** carpeta `android/`, usada para construir o abrir la aplicacion movil.

### 3.5 Herramientas de desarrollo

- **Composer:** gestion de dependencias PHP.
- **NPM:** gestion de dependencias JavaScript.
- **Laravel Pint:** formato de codigo PHP.
- **PHPUnit:** pruebas automatizadas.
- **Concurrently:** ejecucion simultanea de servidor Laravel, queue listener, logs y Vite.

---

## 4. Arquitectura general

La aplicacion sigue una arquitectura Laravel modular basada en MVC, complementada con componentes Livewire.

Flujo general:

1. El usuario ingresa a una ruta definida en `routes/web.php`.
2. Laravel aplica middleware de autenticacion y, en algunos modulos, autorizacion por rol/menu.
3. La pantalla se renderiza con Blade o con un componente Livewire.
4. Livewire recibe la entrada del usuario, valida datos y ejecuta la logica de negocio.
5. Los modelos Eloquent persisten o consultan datos en PostgreSQL.
6. Los observers reaccionan a cambios relevantes, por ejemplo recalculando indicadores o emitiendo eventos.
7. Los dashboards y reportes consumen datos ya agregados desde `indicadores_diarios` y tablas relacionadas.
8. JavaScript renderiza graficas y escucha eventos WebSocket para notificaciones.

Separacion de responsabilidades:

- **Rutas:** definen entradas HTTP y modulos disponibles.
- **Controladores:** manejan paginas tradicionales, reportes, exportaciones y endpoints auxiliares.
- **Livewire:** contiene formularios interactivos y vistas operativas.
- **Modelos:** representan tablas y relaciones.
- **Observers:** automatizan efectos secundarios ante cambios de datos.
- **Servicios:** encapsulan calculos, lectura externa y generacion de informacion reutilizable.
- **Seeders y migraciones:** construyen estructura y datos iniciales.
- **Assets JS/CSS:** gestionan graficas, notificaciones y presentacion.

---

## 5. Estructura del proyecto

```text
app/
  Console/Commands/       Comandos artisan para importacion, limpieza y mantenimiento
  Events/                 Eventos broadcast, como HallazgoPublicado
  Exports/                Exportaciones Excel
  Http/Controllers/       Controladores web, reportes, historiales y descargas
  Http/Middleware/        Middleware de actividad, administracion y permisos
  Livewire/               Componentes interactivos principales
  Models/                 Modelos Eloquent y relaciones
  Observers/              Recalculo de indicadores y broadcasting automatico
  Providers/              Registro de servicios, helpers y observers
  Services/               Logica reutilizable de calculo y lectura externa
  Support/                Helpers de dominio

config/
  services.php            Servicios externos
  usabilidad.php          Configuracion del cierre por inactividad

database/
  migrations/             Definicion evolutiva de tablas
  seeders/                Datos iniciales de roles, menus, productos, ubicaciones y usuarios
  factories/              Factories para pruebas

resources/
  views/                  Plantillas Blade y vistas Livewire
  js/                     Entrada Vite, graficas y notificaciones
  css/                    Estilos Tailwind

routes/
  web.php                 Rutas principales autenticadas y publicas
  auth.php                Rutas de login, registro, recuperacion y logout
  console.php             Rutas/comandos de consola
  channels.php            Canales broadcast

public/
  manual/                 Material de manual/capturas
  sounds/                 Sonidos para alertas de hallazgos

android/
  Proyecto Android generado por Capacitor

docs/
  Documentacion tecnica y referencias del sistema
```

---

## 6. Modulos funcionales principales

### 6.1 Autenticacion y perfiles

La autenticacion se apoya en Laravel Breeze y Livewire Volt. Las rutas se encuentran en `routes/auth.php`.

Funciones principales:

- Login.
- Registro.
- Recuperacion de contrasena.
- Verificacion de email.
- Confirmacion de contrasena.
- Logout con cierre de sesion de usabilidad si existe una sesion abierta.

El modelo `User` incluye campos como `name`, `email`, `username`, `password`, `rol_id`, `activo` y `puede_verificar_titulacion`. La relacion con roles se realiza mediante `rol()`.

### 6.2 Gestion de roles, usuarios y permisos

Los roles se almacenan en `roles` y se representan con `App\Models\Rol`. La normalizacion de nombres permite tratar `ADMIN` como `ADMINISTRADOR`.

Los modulos visibles para cada rol se almacenan en `menu_modulos`, mediante el modelo `MenuModulo`. Cada modulo contiene:

- Nombre visual.
- Ruta asociada.
- Icono.
- Orden de aparicion.
- Lista JSON de roles autorizados.

El trait `AuthorizaPorMenuModulo` valida que el usuario autenticado tenga acceso a la ruta del modulo, usando la misma regla que el menu lateral.

Roles principales detectados:

- `ADMINISTRADOR`
- `OPERACIONES`
- `CALIDAD`
- `GERENCIA`

El componente `GestionUsuarios` permite crear, editar, activar/desactivar y eliminar usuarios. En creacion genera un `username` basado en nombre y apellido y crea un email local del tipo `usuario@sistema.local`.

### 6.3 Registro de hallazgos

El componente `RegistroHallazgo` administra el formulario principal para hallazgos de liberacion de canales.

Datos capturados:

- Producto o media canal.
- Tipo de hallazgo.
- Codigo de canal.
- Fotografia/evidencia opcional.
- Observacion.
- Ubicacion, cuando aplica.
- Lado par/impar, cuando aplica.
- Usuario que registra.
- Fecha de registro y fecha operativa.

Reglas de negocio:

- La fecha operativa cambia automaticamente si el registro ocurre entre 00:00 y 06:59; en ese caso se considera del dia anterior.
- Los productos se filtran a `Media Canal 1 Lengua` y `Media Canal 2 Cola`.
- Los tipos de hallazgo de tolerancia cero se excluyen del registro normal.
- Para `COBERTURA DE GRASA`, se pide ubicacion y se restringe a `Cadera` o `Pierna`.
- Para hallazgos relacionados con corte en pierna se solicita lado.
- Si la ubicacion es `Pierna`, tambien se solicita lado.
- La fotografia se guarda en el disco `public`, dentro de `hallazgos/`.

Persistencia:

- Tabla principal: `registros_hallazgos`.
- Modelo: `App\Models\RegistroHallazgo`.
- Relaciones: producto, tipo de hallazgo, puesto de trabajo, ubicacion, lado, operario y usuario.

### 6.4 Calculo automatico de indicadores

El observer `RegistroHallazgoObserver` recalcula indicadores cuando un hallazgo se crea, actualiza o elimina.

Logica principal:

- Obtiene animales procesados del dia operativo.
- Calcula medias canales totales como `animales_procesados * 2`.
- Cuenta hallazgos totales por fecha operativa.
- Separa registros de media canal 1 y media canal 2.
- Genera desglose por tipo de hallazgo.
- Mapea tipos de hallazgo a columnas principales:
  - `cobertura_grasa`
  - `hematomas`
  - `cortes_piernas`
  - `sobrebarriga_rota`
- Calcula `participacion_total` como porcentaje sobre medias canales.
- Actualiza o crea el registro en `indicadores_diarios`.

El servicio `CalculadoraIndicadores` permite recalcular indicadores diarios y construir agregados mensuales a partir de `IndicadorDiario`.

### 6.5 Hallazgos de tolerancia cero

El componente `RegistroHallazgoToleranciaZero` gestiona eventos criticos de tolerancia cero.

Datos capturados:

- Cuarto anterior o cuarto posterior.
- Tipo de hallazgo: materia fecal, contenido ruminal o leche visible.
- Ubicacion especifica cuando aplica.
- Codigo ingresado.
- Media canal 1 o 2.
- Par o impar.
- Usuario.
- Fecha operativa.

Reglas de negocio:

- `CUARTO ANTERIOR` permite `CONTENIDO RUMINAL`, `LECHE VISIBLE` y `MATERIA FECAL`.
- `CUARTO POSTERIOR` permite `LECHE VISIBLE` y `MATERIA FECAL`.
- `CUARTO POSTERIOR + CONTENIDO RUMINAL` no esta permitido.
- Para `LECHE VISIBLE`, la ubicacion se asigna automaticamente a `TRANSFERENCIA`.
- Las ubicaciones disponibles cambian segun la combinacion de cuarto y tipo.

Despues de guardar, el componente actualiza los campos de tolerancia cero dentro de `indicadores_diarios`:

- `materia_fecal_tc`
- `contenido_ruminal_tc`
- `leche_visible_tc`
- `total_hallazgos_tolerancia_cero`

### 6.6 Notificaciones en tiempo real

Cuando se crea un hallazgo normal o de tolerancia cero, los observers publican el evento `HallazgoPublicado`.

Backend:

- `RegistroHallazgoObserver` transmite hallazgos normales.
- `HallazgoToleranciaZeroObserver` transmite hallazgos de tolerancia cero.
- Laravel Reverb actua como servidor WebSocket.

Frontend:

- `resources/js/hallazgo-notifications.js` inicializa Echo/Reverb.
- Se suscribe al canal `hallazgos`.
- Escucha el evento `.registrado`.
- Evita notificar al usuario que realizo el registro.
- Muestra modal emergente, vibracion y sonido `public/sounds/not.mp3` cuando el navegador lo permite.
- En contextos seguros puede usar Notification API del navegador.

Configuracion relevante:

- `BROADCAST_CONNECTION=reverb`
- `REVERB_APP_KEY`
- `REVERB_HOST`
- `REVERB_PORT`
- `ECHO_WS_HOST`
- `VITE_REVERB_*`

### 6.7 Animales procesados

El sistema permite registrar cantidad de animales procesados por fecha operativa. Estos datos son indispensables porque los indicadores se calculan usando animales y medias canales como denominador.

El observer `AnimalProcesadoObserver` participa en la sincronizacion de indicadores cuando cambian los animales procesados.

### 6.8 Dashboards e indicadores

Los dashboards usan registros agregados en `indicadores_diarios`.

Dashboards detectados:

- Dashboard principal.
- Dashboard mensual.
- Dashboard diario.
- Indicadores por dia.
- Graficas de tolerancia cero por dia.
- Graficas de tolerancia cero por mes.

El controlador `DashboardMensualController` construye:

- Totales del mes.
- Porcentajes diarios por tipo de hallazgo.
- Metas de referencia:
  - Sobrebarriga rota: 1.0 %
  - Hematomas: 0.5 %
  - Cortes en piernas: 1.0 %
  - Cobertura grasa: 1.5 %
- Datos para Chart.js.
- Seguimiento semanal tipo Excel.
- Seguimiento anual consolidado.
- Exportacion de graficas a Excel.

El archivo `resources/js/charts-dashboard.js` registra Chart.js, configura etiquetas y muestra callouts solamente cuando un indicador cumple o supera la meta.

### 6.9 Gestion de operarios y puestos de trabajo

El sistema mantiene catalogos de operarios y puestos de trabajo, asi como asignaciones por fecha.

Tablas y modelos relacionados:

- `operarios`
- `puestos_trabajo`
- `operarios_por_dia`
- `Operario`
- `PuestoTrabajo`
- `OperarioPorDia`

Esta informacion se usa para asociar responsables a procesos y para resolver datos como el responsable del puesto `Desinfeccion` en Verificacion PCC.

### 6.10 Verificacion PCC

El componente `VerificacionPcc` administra una cola operativa de productos pendientes por verificar.

Flujo:

1. Calcula la fecha operativa usando `TurnoVerificacionPcc`.
2. Lee registros externos desde `TrazabilidadInsensibilizacionReader` si la conexion esta configurada.
3. Filtra los registros ya verificados en esta app durante el turno.
4. Toma el primer pendiente.
5. El usuario marca si cumple media canal 1 y media canal 2.
6. Guarda observacion y accion correctiva si aplica.
7. Persiste un snapshot del registro externo para trazabilidad historica.

Modelo principal:

- `VerificacionPccRegistro`.

Datos clave:

- Usuario que verifica.
- ID externo de insensibilizacion.
- Codigo de producto.
- Snapshot externo JSON.
- Cumplimiento por media canal.
- Responsable del puesto de trabajo.
- Observacion.
- Accion correctiva.

La lectura externa se realiza mediante SQL sobre tablas de trazabilidad de insensibilizacion y considera el dia operativo, incluyendo la madrugada del dia siguiente cuando pertenece al mismo turno.

### 6.11 Titulacion de acido lactico

El componente `TitulacionAcidoLactico` registra controles de titulacion.

Campos:

- Volumen NaOH en ml.
- Concentracion de solucion en porcentaje.
- Hora.
- Cumple/no cumple.
- Correccion.
- Actividad.
- Usuario que registra.
- Usuario verificador autorizado.

Validaciones de negocio:

- `volumen_naoh_ml` debe estar entre 2.2 y 2.3.
- `concentracion_sol_pct` debe estar entre 1.9 y 2.1.
- El verificador debe estar activo y tener `puede_verificar_titulacion=true`.
- Actividades permitidas:
  - Operativo.
  - Preoperativo.
  - Monitoreo PCC.

Modelo:

- `TitulacionAcidoLacticoRegistro`.

### 6.12 Consumo de acido lactico

El componente `ConsumoAcidoLactico` registra y resume consumo.

Campos:

- Litros preparados.
- Cantidad de acido lactico en ml.
- Observacion.
- Usuario.
- Fecha y hora del registro.

La vista calcula totales por:

- Dia seleccionado.
- Mes seleccionado.
- Total general.

### 6.13 Tiempo de usabilidad

El middleware `TrackUserActivity` crea o actualiza una sesion de uso por usuario autenticado.

Modelo:

- `SesionUsuario`.

Logica:

- Si el usuario tiene sesion abierta, actualiza `ultima_actividad`.
- Si no existe sesion abierta, crea una nueva con `login_at`, `ultima_actividad` e IP.
- Las sesiones inactivas se cierran usando el umbral de `config/usabilidad.php`.
- El comando `usabilidad:cerrar-sesiones-inactivas` ejecuta cierre manual o programable.

### 6.14 Reportes, PDF y Excel

El sistema usa DomPDF y Maatwebsite Excel.

Exportaciones relevantes:

- Historial de hallazgos a Excel/PDF.
- Historial de Verificacion PCC a Excel.
- Historial de titulacion a Excel.
- Reportes mensuales a PDF y Excel.
- Graficas mensuales a Excel.

Los controladores de exportacion preparan consultas con relaciones Eloquent y descargan archivos con nombres descriptivos.

### 6.15 Importaciones y mantenimiento

El proyecto incluye comandos artisan para carga historica y mantenimiento.

Comandos detectados:

- `import:registros-hallazgos-excel`
- `import:operarios-excel`
- `import:animales-procesados-excel`
- `registros-hallazgos:dedupe`
- `usabilidad:cerrar-sesiones-inactivas`
- `make:admin`
- `generate:usernames`
- `manual:export`
- `fix:hallazgos-paths`

El comando `import:registros-hallazgos-excel` lee hojas `Registros` y `Operarios`, mapea catalogos internos, permite `--dry-run`, inserta por lotes y recalcula indicadores de las fechas afectadas.

---

## 7. Modelo de datos principal

### 7.1 Entidades de seguridad

- `users`: usuarios del sistema.
- `roles`: roles funcionales.
- `menu_modulos`: modulos visibles por rol.
- `sesiones_usuario`: sesiones de uso y duracion.

### 7.2 Catalogos operativos

- `productos`: medias canales, cuartos y productos usados por modulos.
- `tipos_hallazgo`: clasificacion de hallazgos.
- `ubicaciones`: zonas fisicas o puntos de proceso.
- `lados`: par/impar.
- `puestos_trabajo`: puestos operativos.
- `operarios`: personas asignables a puestos.

### 7.3 Operacion diaria

- `operarios_por_dia`: asignacion de operarios por fecha y puesto.
- `animales_procesados`: cantidad de animales por fecha operativa.
- `registros_hallazgos`: hallazgos normales.
- `hallazgos_tolerancia_cero`: hallazgos criticos TC.
- `indicadores_diarios`: agregados calculados para dashboards.

### 7.4 Modulos especializados

- `verificacion_pcc_registros`: verificaciones PCC y snapshot externo.
- `titulacion_acido_lactico_registros`: controles de titulacion.
- `consumo_acido_lactico_registros`: consumo de acido lactico.
- `filtros_usuario`: preferencias o filtros guardados por usuario.
- `historial_registros`: soporte historico.
- `registros_hallazgos_eliminados`: auditoria de eliminaciones.

---

## 8. Reglas de negocio criticas

### 8.1 Fecha operativa

La aplicacion diferencia entre fecha calendario y fecha operativa. En registros de hallazgos, si el usuario registra entre 00:00 y 06:59, el sistema considera que el registro pertenece al dia operativo anterior.

Esta regla afecta:

- Registro de hallazgos.
- Conteos diarios.
- Dashboards.
- Indicadores.
- Historiales.
- Turnos PCC.

### 8.2 Medias canales

Los indicadores usan como base:

```text
medias_canales_total = animales_procesados * 2
```

Esta base permite calcular participacion y porcentajes por tipo de hallazgo.

### 8.3 Indicadores de hallazgos normales

El sistema calcula conteos y porcentajes para:

- Cobertura de grasa.
- Hematomas.
- Cortes en piernas.
- Sobrebarriga rota.

La participacion total se calcula sumando la proporcion de cada tipo relevante sobre el total de medias canales.

### 8.4 Indicadores de tolerancia cero

Los hallazgos de tolerancia cero se manejan separados de los hallazgos normales, pero actualizan `indicadores_diarios` para que dashboards y reportes puedan mostrar:

- Materia fecal.
- Contenido ruminal.
- Leche visible.
- Total TC.

### 8.5 Permisos por menu

El acceso visual y funcional a varios modulos se controla con `menu_modulos.roles`. El mismo criterio se usa para mostrar opciones en el menu y para autorizar la vista mediante `AuthorizaPorMenuModulo`.

### 8.6 Trazabilidad externa PCC

La integracion externa es opcional. Si no existe configuracion de `pgsql_trazabilidad`, el modulo no consulta datos externos y devuelve colecciones vacias. Si existe, se consulta el dia operativo con ventana de turno.

---

## 9. Rutas principales

Las rutas principales estan en `routes/web.php`.

Rutas publicas:

- `/` pagina de bienvenida.

Rutas autenticadas:

- `/dashboard`
- `/dashboard/mensual`
- `/operarios`
- `/operarios/dia`
- `/operarios/asignacion`
- `/hallazgos/registrar`
- `/hallazgos/historial`
- `/tolerancia-cero/registrar`
- `/tolerancia-cero/historial`
- `/animales`
- `/indicadores/dia`
- `/indicadores/mes`
- `/usuarios`
- `/usuarios/roles`
- `/usuarios/permisos-verificadores`
- `/tiempo-usabilidad`
- `/titulacion-acido-lactico`
- `/consumo-acido-lactico`
- `/verificacion-pcc`
- `/reportes`
- `/manual/usuario`

Endpoints auxiliares:

- `/api/indicadores/graficos`
- `/api/indicadores/recalcular`
- `/api/usuarios/activos`
- `/api/usuarios/{usuario}/toggle-activo`

---

## 10. Configuracion del entorno

Variables principales:

```env
APP_NAME=LiberacionDeCanales
APP_ENV=production
APP_URL=http://192.168.20.11:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=liberacion_canales
SESSION_DRIVER=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=reverb
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=6001
```

Variables opcionales de trazabilidad:

```env
POSTGRES_HOST=
POSTGRES_PORT=
POSTGRES_DB=
POSTGRES_USER=
POSTGRES_PASSWORD=
DB_TRAZABILIDAD_SEARCH_PATH=trazabilidad_proceso,organizaciones,public
```

Variables de Reverb/Echo:

```env
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=6001
REVERB_SCHEME=http
ECHO_WS_HOST=
HALLAZGO_ECHO_DEBUG=false
```

Nota senior: el archivo `.env.example` contiene valores reales de ejemplo para base de datos y claves. Para ambientes productivos se recomienda rotar credenciales, desactivar `APP_DEBUG`, y mantener secretos fuera del repositorio.

---

## 11. Instalacion y ejecucion

### 11.1 Requisitos

- PHP 8.2+
- Composer
- Node.js y npm
- PostgreSQL
- Extensiones PHP requeridas por Laravel, PostgreSQL, imagenes, zip y XML

### 11.2 Instalacion inicial

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

El proyecto tambien define el script Composer:

```bash
composer run setup
```

Ese script instala dependencias, crea `.env` si no existe, genera la key, ejecuta migraciones, instala NPM y compila assets.

### 11.3 Desarrollo local

```bash
composer run dev
```

Este comando levanta:

- Servidor Laravel.
- Queue listener.
- Logs con Pail.
- Vite.

Para Reverb:

```bash
php artisan reverb:start
```

### 11.4 Pruebas

```bash
composer run test
```

Internamente limpia configuracion y ejecuta:

```bash
php artisan test
```

Estado de cobertura observado: existen pruebas automatizadas principalmente para autenticacion y perfil base de Laravel/Breeze. No se detecto una suite amplia para reglas de dominio como turnos operativos, recalculo de indicadores, tolerancia cero, Verificacion PCC o importaciones historicas.

### 11.5 Assets

```bash
npm run dev
npm run build
```

### 11.6 Android / Capacitor

```bash
npm run cap:sync
npm run cap:sync:android
npm run cap:open:android
```

La configuracion actual apunta a `http://192.168.20.11:8006` y permite `cleartext`, lo cual es util en red local pero debe revisarse para distribuciones productivas.

---

## 12. Seguridad y controles recomendados

Controles existentes:

- Autenticacion con Laravel.
- Password hashing automatico.
- Middleware de autenticacion en rutas internas.
- Control de vistas por rol y modulo.
- Usuarios activos/inactivos.
- Verificadores autorizados para titulacion.
- Validaciones server-side en Livewire.
- Restricciones de tipos de archivo para evidencias.
- Snapshots de datos externos PCC para auditoria.

Recomendaciones:

- Desactivar `APP_DEBUG` en produccion.
- Rotar credenciales expuestas en archivos de ejemplo si fueron usadas.
- Retirar, proteger o documentar como uso interno los scripts de administracion manual ubicados en la raiz, como `create-juan-admin.php` y `setup-admin.php`.
- Configurar HTTPS si se usan notificaciones del sistema del navegador.
- Configurar backup automatico de PostgreSQL.
- Usar un usuario de base de datos con privilegios minimos.
- Proteger rutas debug como `/debug/reverb-ping` solo en desarrollo.
- Tener presente que las alertas de hallazgos usan un canal broadcast compartido llamado `hallazgos`; si se requiere mayor privacidad por rol o area, conviene migrarlo a canales privados o presencia.
- Revisar logs de importaciones masivas antes de ejecutar sin `--dry-run`.
- Mantener permisos de escritura controlados en `storage/` y `public/`.

---

## 13. Mantenimiento operativo

### 13.1 Recalculo de indicadores

Los indicadores se recalculan automaticamente al crear, actualizar o eliminar hallazgos, y al modificar animales procesados.

Si se importan datos historicos, los comandos de importacion recalculan fechas afectadas. Para recalculos manuales se puede usar la logica de `CalculadoraIndicadores` o endpoints internos de indicadores segun permisos.

### 13.2 Limpieza de sesiones

Comando:

```bash
php artisan usabilidad:cerrar-sesiones-inactivas
```

Se recomienda programarlo en scheduler si el sistema requiere metricas consistentes aun cuando no haya trafico web.

Nota operativa: en Laravel 12 la configuracion efectiva de middleware, health check y scheduler suele estar en `bootstrap/app.php`. Si existe `app/Http/Kernel.php`, debe tratarse con cautela porque puede corresponder a estructura heredada y no necesariamente ser el punto activo de configuracion.

### 13.3 Importacion historica

Ejemplo seguro:

```bash
php artisan import:registros-hallazgos-excel "C:\ruta\archivo.xlsx" --dry-run --user-id=1 --omitir-duplicados
```

Despues de validar:

```bash
php artisan import:registros-hallazgos-excel "C:\ruta\archivo.xlsx" --user-id=1 --omitir-duplicados
```

### 13.4 Monitoreo de Reverb

Para alertas en tiempo real, confirmar:

- `php artisan reverb:start` activo.
- Puerto WebSocket abierto en firewall.
- `ECHO_WS_HOST` sin puerto HTTP.
- `REVERB_SERVER_PORT` correcto.
- `VITE_REVERB_*` recompilado si se cambia configuracion frontend.

---

## 14. Convenciones de codigo observadas

- Los modelos Eloquent declaran `$fillable`, relaciones y casts.
- Las reglas de validacion se ubican en componentes Livewire o controladores.
- Los observers concentran efectos derivados de persistencia.
- Los servicios agrupan logica reutilizable que no pertenece a una vista concreta.
- Las migraciones son incrementales y reflejan evolucion del dominio.
- Los seeders inicializan catalogos criticos.
- Los nombres de rutas son usados como identificadores de permisos de menu.
- La logica de fecha operativa se repite en modulos criticos; esto debe mantenerse consistente.

---

## 15. Puntos de atencion tecnica

- La documentacion del README original era la generica de Laravel; esta documentacion describe el sistema real.
- La fecha operativa es una regla central: cualquier nuevo modulo que agrupe por fecha debe respetarla.
- Los dashboards dependen de `indicadores_diarios`; si faltan animales procesados, los porcentajes pueden quedar en cero o usar denominadores de seguridad.
- Verificacion PCC depende opcionalmente de una base externa. Sin configuracion, el modulo no tendra cola externa.
- Las notificaciones del navegador pueden estar limitadas en HTTP/LAN por politicas del navegador.
- Capacitor esta configurado para una URL de red local; al cambiar servidor debe sincronizarse la app movil.
- El archivo `.env.example` debe tratarse como plantilla, no como fuente segura de credenciales productivas.
- Aunque existen middlewares administrativos, la proteccion real de varios modulos se aplica dentro de componentes Livewire mediante `AuthorizaPorMenuModulo`; no se debe asumir que todo acceso administrativo esta centralizado en rutas.
- Las operaciones de importacion, deduplicacion y correccion de rutas de evidencia deben ejecutarse con respaldo previo y, cuando sea posible, con `--dry-run`.
- La aplicacion esta configurada en idioma funcional espanol, aunque algunas variables base de Laravel pueden conservar valores por defecto en ingles.

---

## 16. Glosario

- **Hallazgo:** registro de una condicion encontrada durante la liberacion de canales.
- **Tolerancia cero:** hallazgo critico que requiere tratamiento separado.
- **Fecha operativa:** fecha de trabajo del turno; no siempre coincide con la fecha calendario.
- **Media canal:** unidad de referencia derivada de cada animal procesado.
- **PCC:** Punto Critico de Control.
- **Snapshot externo:** copia JSON del registro externo usado al momento de una verificacion.
- **Observer:** clase que reacciona a eventos del modelo, como crear o actualizar.
- **Seeder:** clase que carga datos iniciales en la base.
- **Livewire:** framework para interfaces reactivas usando PHP y Blade.
- **Reverb:** servidor WebSocket oficial de Laravel.

---

## 17. Archivos clave de referencia

- `routes/web.php`: mapa principal de rutas y modulos.
- `routes/auth.php`: autenticacion y logout.
- `bootstrap/app.php`: configuracion activa de bootstrap, middleware, health check y scheduler en Laravel 12.
- `app/Livewire/RegistroHallazgo.php`: formulario principal de hallazgos.
- `app/Livewire/RegistroHallazgoToleranciaZero.php`: formulario de tolerancia cero.
- `app/Livewire/VerificacionPcc.php`: cola y guardado de verificaciones PCC.
- `app/Livewire/TitulacionAcidoLactico.php`: controles de titulacion.
- `app/Livewire/ConsumoAcidoLactico.php`: consumo de acido lactico.
- `app/Livewire/GestionUsuarios.php`: administracion de usuarios.
- `app/Models/RegistroHallazgo.php`: modelo principal de hallazgos.
- `app/Models/IndicadorDiario.php`: indicadores agregados.
- `app/Models/MenuModulo.php`: permisos por modulo.
- `app/Observers/RegistroHallazgoObserver.php`: recalculo y broadcasting de hallazgos.
- `app/Observers/HallazgoToleranciaZeroObserver.php`: broadcasting TC.
- `app/Services/CalculadoraIndicadores.php`: calculos diarios y mensuales.
- `app/Services/TrazabilidadInsensibilizacionReader.php`: integracion PostgreSQL externa.
- `resources/js/hallazgo-notifications.js`: notificaciones en tiempo real.
- `resources/js/charts-dashboard.js`: graficas de dashboard.
- `database/seeders/MenuModuloSeeder.php`: modulos y roles visibles.
- `composer.json`: dependencias PHP y scripts.
- `package.json`: dependencias frontend y scripts NPM.
- `capacitor.config.json`: configuracion movil.

---

## 18. Conclusion tecnica

Liberacion de Canales es una aplicacion Laravel orientada a procesos operativos de calidad, con una estructura funcional madura: captura datos en tiempo real, calcula indicadores, ofrece reportes, integra datos externos cuando estan disponibles y segmenta el acceso por rol.

La parte mas importante del sistema es la consistencia entre fecha operativa, animales procesados, hallazgos e indicadores diarios. Cualquier mejora futura debe preservar esa relacion para evitar inconsistencias en dashboards y reportes.

La base tecnica permite crecer hacia mayor auditoria, pruebas automatizadas de reglas de negocio, monitoreo de WebSockets, versionado formal de reportes y hardening de seguridad para ambientes productivos.
