<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Manual de usuario — Liberación de Canales</title>
    <style>
        @page { margin: 22mm 18mm 24mm 18mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #1f2937;
        }
        h1 {
            font-size: 20pt;
            color: #1e40af;
            margin: 0 0 8px 0;
            page-break-after: avoid;
        }
        h2 {
            font-size: 13pt;
            color: #1e3a8a;
            margin: 18px 0 8px 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            page-break-after: avoid;
        }
        h3 {
            font-size: 11pt;
            margin: 12px 0 6px 0;
            color: #334155;
            page-break-after: avoid;
        }
        p { margin: 6px 0; }
        ul, ol { margin: 6px 0 6px 18px; padding: 0; }
        li { margin: 3px 0; }
        .portada {
            text-align: center;
            padding-top: 40mm;
            page-break-after: always;
        }
        .portada .sub { font-size: 12pt; color: #64748b; margin-top: 12px; }
        .portada .meta { font-size: 9pt; color: #94a3b8; margin-top: 28px; }
        .figura {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 8px;
            margin: 10px 0 14px 0;
            page-break-inside: avoid;
        }
        .figura img { width: 100%; height: auto; display: block; }
        .figura .titulo-img {
            font-size: 9pt;
            color: #475569;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .placeholder {
            border: 2px dashed #cbd5e1;
            background: #f1f5f9;
            color: #64748b;
            font-size: 9pt;
            padding: 28px 12px;
            text-align: center;
        }
        table.roles {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 9.5pt;
        }
        table.roles th, table.roles td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.roles th { background: #e2e8f0; text-align: left; }
        .nota {
            background: #eff6ff;
            border-left: 3px solid #2563eb;
            padding: 8px 10px;
            margin: 10px 0;
            font-size: 9.5pt;
        }
        .pie-pagina { font-size: 8pt; color: #94a3b8; margin-top: 6px; }
        .salto { page-break-after: always; }
    </style>
</head>
<body>

<div class="portada">
    <h1 style="font-size: 26pt; border: none;">Manual de usuario</h1>
    <div class="sub"><strong>Liberación de Canales</strong><br>Sistema de control de calidad</div>
    <p style="margin-top: 24px; font-size: 11pt;">Colbeef — orientación operativa por rol</p>
    <div class="meta">Documento generado: {{ $generado }}</div>
</div>

<h1>1. Objetivo del sistema</h1>
<p>Esta aplicación permite registrar y consultar <strong>hallazgos de calidad</strong>, <strong>tolerancia cero</strong>, <strong>animales procesados</strong>, la <strong>asignación de operarios</strong> por día y los <strong>indicadores</strong> asociados, con accesos diferenciados según el rol del usuario.</p>

<h1>2. Acceso e inicio de sesión</h1>
<p>El acceso se realiza con <strong>usuario</strong> y <strong>contraseña</strong> (no use el correo como usuario salvo que así lo haya definido su administrador).</p>

<div class="figura">
    <div class="titulo-img">Figura 1 — Pantalla de inicio de sesión</div>
    @if(!empty($capturas['login']))
        <img src="{{ $capturas['login'] }}" alt="Inicio de sesión">
    @else
        <div class="placeholder">Sin captura (01-login.png). Ejecute en la raíz del proyecto:<br><code>npm run manual:capturas</code> con la aplicación en marcha y las variables MANUAL_USERNAME / MANUAL_PASSWORD en un archivo <code>.env.manual</code> (ver <code>public/manual/INSTRUCCIONES-CAPTURAS.txt</code>).</div>
    @endif
</div>
<ol>
    <li>Abra la URL que le indique su área de sistemas (por ejemplo, la de su servidor interno).</li>
    <li>Si no ha iniciado sesión, será redirigido al formulario de login.</li>
    <li>Ingrese su <strong>Usuario</strong> y <strong>Contraseña</strong> y pulse <strong>Iniciar sesión</strong>.</li>
</ol>

<div class="nota"><strong>Seguridad:</strong> no comparta sus credenciales. Si olvida la contraseña, use el enlace de recuperación si está habilitado o contacte al administrador.</div>

<div class="salto"></div>

<h1>3. Pantalla de bienvenida y menú</h1>
<p>Tras el ingreso verá una pantalla de bienvenida con accesos a los módulos autorizados para su rol. También dispone de un <strong>menú lateral</strong> (barra oscura) para navegar en cualquier momento.</p>

<div class="figura">
    <div class="titulo-img">Figura 2 — Bienvenida y accesos por módulo</div>
    @if(!empty($capturas['bienvenida']))
        <img src="{{ $capturas['bienvenida'] }}" alt="Bienvenida">
    @else
        <div class="placeholder">Sin captura (02-bienvenida.png). Genere las capturas con el script indicado en la sección 2.</div>
    @endif
</div>

<h1>4. Roles y responsabilidades</h1>
<p>El sistema contempla cuatro roles principales. La siguiente tabla resume el uso típico (el menú concreto puede variar ligeramente según la configuración de su planta).</p>

<table class="roles">
    <thead>
        <tr>
            <th style="width:22%">Rol</th>
            <th>Uso principal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>ADMINISTRADOR</strong></td>
            <td>Configuración completa: usuarios, puestos de trabajo, operarios, asignación por día, todos los registros e indicadores, reportes y tiempo de usabilidad.</td>
        </tr>
        <tr>
            <td><strong>CALIDAD</strong></td>
            <td>Registro de hallazgos estándar; consulta de historiales, indicadores y dashboards; animales procesados; historial de tolerancia cero (consulta).</td>
        </tr>
        <tr>
            <td><strong>OPERACIONES</strong></td>
            <td>Operarios y asignación por día; animales procesados; registro de hallazgos <em>tolerancia cero</em>; consulta de historiales e indicadores.</td>
        </tr>
        <tr>
            <td><strong>GERENCIA</strong></td>
            <td>Consulta de indicadores, dashboards, historiales y animales procesados para seguimiento y decisiones.</td>
        </tr>
    </tbody>
</table>

<div class="salto"></div>

<h1>5. Dashboard e indicadores</h1>
<p>Los módulos de <strong>Dashboard diario</strong>, <strong>Dashboard mensual</strong> e <strong>Indicador diario</strong> permiten visualizar el desempeño agregado. Los indicadores se actualizan en gran medida de forma automática al registrar hallazgos y animales procesados.</p>

<div class="figura">
    <div class="titulo-img">Figura 3 — Dashboard diario (ejemplo)</div>
    @if(!empty($capturas['dashboard']))
        <img src="{{ $capturas['dashboard'] }}" alt="Dashboard">
    @else
        <div class="placeholder">Sin captura (03-dashboard.png).</div>
    @endif
</div>

<div class="figura">
    <div class="titulo-img">Figura 4 — Indicadores por día (ejemplo)</div>
    @if(!empty($capturas['indicadores']))
        <img src="{{ $capturas['indicadores'] }}" alt="Indicadores">
    @else
        <div class="placeholder">Sin captura (09-indicadores-dia.png).</div>
    @endif
</div>

<div class="salto"></div>

<h1>6. Registro e historial de hallazgos (calidad)</h1>
<h2>6.1 Registro de hallazgos</h2>
<p>Desde <strong>Registro de hallazgos</strong> se documentan incidencias sobre el producto y el canal. Debe seleccionar producto, tipo de hallazgo y datos del canal; según el tipo, el sistema solicitará <strong>ubicación</strong> y/o <strong>lado</strong> (par/impar). Puede adjuntar una fotografía y observaciones.</p>

<div class="figura">
    <div class="titulo-img">Figura 5 — Formulario de registro de hallazgos</div>
    @if(!empty($capturas['registro_hallazgos']))
        <img src="{{ $capturas['registro_hallazgos'] }}" alt="Registro hallazgos">
    @else
        <div class="placeholder">Sin captura (04-registro-hallazgos.png).</div>
    @endif
</div>

<h2>6.2 Historial</h2>
<p>El <strong>Historial de registros</strong> permite revisar lo capturado, filtrar por fechas y apoyar auditorías.</p>

<div class="figura">
    <div class="titulo-img">Figura 6 — Historial de hallazgos</div>
    @if(!empty($capturas['historial']))
        <img src="{{ $capturas['historial'] }}" alt="Historial">
    @else
        <div class="placeholder">Sin captura (05-historial.png).</div>
    @endif
</div>

<div class="nota">En turnos de madrugada (aprox. 00:00 a 06:59) el sistema puede asociar registros a la <strong>fecha operativa del día anterior</strong>, según la lógica configurada en el registro.</div>

<div class="salto"></div>

<h1>7. Hallazgos tolerancia cero</h1>
<p>Este módulo está pensado para incidencias críticas. Solo usuarios con rol <strong>ADMINISTRADOR</strong> u <strong>OPERACIONES</strong> pueden registrar; otros roles suelen tener acceso de <strong>consulta</strong> al historial.</p>

<div class="figura">
    <div class="titulo-img">Figura 7 — Registro tolerancia cero</div>
    @if(!empty($capturas['tolerancia_cero']))
        <img src="{{ $capturas['tolerancia_cero'] }}" alt="Tolerancia cero">
    @else
        <div class="placeholder">Sin captura (08-tolerancia-cero.png).</div>
    @endif
</div>

<div class="salto"></div>

<h1>8. Operarios y asignación por día</h1>
<p>Mantenga el catálogo de <strong>Operarios</strong> y utilice <strong>Asignación por día</strong> para reflejar quién trabaja en cada puesto en la fecha correspondiente.</p>

<div class="figura">
    <div class="titulo-img">Figura 8 — Listado de operarios</div>
    @if(!empty($capturas['operarios']))
        <img src="{{ $capturas['operarios'] }}" alt="Operarios">
    @else
        <div class="placeholder">Sin captura (06-operarios.png).</div>
    @endif
</div>

<div class="figura">
    <div class="titulo-img">Figura 9 — Asignación por día</div>
    @if(!empty($capturas['asignacion_dia']))
        <img src="{{ $capturas['asignacion_dia'] }}" alt="Asignación">
    @else
        <div class="placeholder">Sin captura (07-asignacion-dia.png).</div>
    @endif
</div>

<div class="salto"></div>

<h1>9. Animales procesados</h1>
<p>El registro de <strong>animales procesados</strong> alimenta los indicadores de volumen. Consulte con su supervisor la periodicidad y responsable del registro en su turno.</p>

<h1>10. Administración (solo administrador)</h1>
<p>Los administradores gestionan <strong>puestos de trabajo</strong>, <strong>usuarios</strong> (rol y estado activo) y pueden consultar <strong>tiempo de usabilidad</strong>.</p>

<div class="figura">
    <div class="titulo-img">Figura 10 — Gestión de usuarios (ejemplo)</div>
    @if(!empty($capturas['usuarios']))
        <img src="{{ $capturas['usuarios'] }}" alt="Usuarios">
    @else
        <div class="placeholder">Sin captura (10-gestion-usuarios.png).</div>
    @endif
</div>

<h1>11. Reportes y exportaciones</h1>
<p>Según los permisos definidos por su organización, podrá acceder a reportes en PDF/Excel y exportaciones de hallazgos o indicadores desde las secciones correspondientes del sistema.</p>

<h1>12. Perfil y cierre de sesión</h1>
<p>Use <strong>Mi perfil</strong> para actualizar sus datos personales cuando el administrador lo permita. Cierre siempre la sesión con el botón <strong>Cerrar sesión</strong> al terminar, especialmente en equipos compartidos.</p>

<p class="pie-pagina">Liberación de Canales — Manual de usuario. Para actualizar las capturas del PDF, siga las instrucciones en <code>public/manual/INSTRUCCIONES-CAPTURAS.txt</code>.</p>

</body>
</html>
