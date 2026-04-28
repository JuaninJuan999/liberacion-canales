<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use Livewire\Component;
use App\Models\SesionUsuario;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TiempoUsabilidad extends Component
{
    use AuthorizaPorMenuModulo;

    public string $periodo = 'semana'; // semana | mes | personalizado
    public string $fechaInicio = '';
    public string $fechaFin = '';
    public ?int $usuarioSeleccionado = null;

    public array $datosBarras = [];
    public array $datosLinea = [];
    public array $sesionesTabla = [];
    public array $resumen = [];

    public function mount()
    {
        $this->autorizarVistaMenu('tiempo-usabilidad');

        $this->fechaFin = Carbon::now()->format('Y-m-d');
        $this->fechaInicio = Carbon::now()->subDays(6)->format('Y-m-d');
        $this->cargarDatos();
    }

    public function updatedPeriodo()
    {
        switch ($this->periodo) {
            case 'semana':
                $this->fechaFin = Carbon::now()->format('Y-m-d');
                $this->fechaInicio = Carbon::now()->subDays(6)->format('Y-m-d');
                break;
            case 'mes':
                $this->fechaFin = Carbon::now()->format('Y-m-d');
                $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
                break;
            case 'personalizado':
                return;
        }
        $this->cargarDatos();
    }

    public function updatedFechaInicio()
    {
        $this->periodo = 'personalizado';
        $this->cargarDatos();
    }

    public function updatedFechaFin()
    {
        $this->periodo = 'personalizado';
        $this->cargarDatos();
    }

    public function updatedUsuarioSeleccionado()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $inicio = Carbon::parse($this->fechaInicio)->startOfDay();
        $fin = Carbon::parse($this->fechaFin)->endOfDay();

        $this->cargarResumen($inicio, $fin);
        $this->cargarDatosBarras($inicio, $fin);
        $this->cargarDatosLinea($inicio, $fin);
        $this->cargarTabla($inicio, $fin);

        $this->dispatch('datosActualizados', [
            'barras' => $this->datosBarras,
            'linea' => $this->datosLinea,
        ]);
    }

    private function cargarResumen($inicio, $fin)
    {
        $query = SesionUsuario::whereBetween('login_at', [$inicio, $fin]);

        if ($this->usuarioSeleccionado) {
            $query->where('user_id', $this->usuarioSeleccionado);
        }

        $sesiones = $query->get();

        $totalMinutos = $sesiones->sum('duracion_minutos') ?? 0;
        $totalSesiones = $sesiones->count();
        $promedioMinutos = $totalSesiones > 0 ? round($totalMinutos / $totalSesiones, 1) : 0;
        $usuariosActivos = $sesiones->pluck('user_id')->unique()->count();

        $this->resumen = [
            'total_horas' => round($totalMinutos / 60, 1),
            'total_sesiones' => $totalSesiones,
            'promedio_minutos' => $promedioMinutos,
            'usuarios_activos' => $usuariosActivos,
        ];
    }

    private function cargarDatosBarras($inicio, $fin)
    {
        $query = SesionUsuario::select('user_id', DB::raw('COALESCE(SUM(duracion_minutos), 0) as total_minutos'))
            ->whereBetween('login_at', [$inicio, $fin])
            ->groupBy('user_id')
            ->with('user');

        if ($this->usuarioSeleccionado) {
            $query->where('user_id', $this->usuarioSeleccionado);
        }

        $datos = $query->get();

        $labels = [];
        $valores = [];
        $colores = [];
        $colorPaleta = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];

        foreach ($datos as $i => $dato) {
            $labels[] = $dato->user->name ?? 'Usuario #' . $dato->user_id;
            $valores[] = round($dato->total_minutos / 60, 2);
            $colores[] = $colorPaleta[$i % count($colorPaleta)];
        }

        $this->datosBarras = [
            'labels' => $labels,
            'valores' => $valores,
            'colores' => $colores,
        ];
    }

    private function cargarDatosLinea($inicio, $fin)
    {
        $query = SesionUsuario::select(
                DB::raw("DATE(login_at) as fecha"),
                DB::raw('COALESCE(SUM(duracion_minutos), 0) as total_minutos'),
                DB::raw('COUNT(*) as total_sesiones')
            )
            ->whereBetween('login_at', [$inicio, $fin])
            ->groupBy(DB::raw("DATE(login_at)"))
            ->orderBy('fecha');

        if ($this->usuarioSeleccionado) {
            $query->where('user_id', $this->usuarioSeleccionado);
        }

        $datos = $query->get();

        // Rellenar días sin datos
        $labels = [];
        $minutos = [];
        $sesiones = [];
        $current = $inicio->copy();
        $datosPorFecha = $datos->keyBy('fecha');

        while ($current->lte($fin)) {
            $fechaStr = $current->format('Y-m-d');
            $labels[] = $current->format('d/m');
            $minutos[] = isset($datosPorFecha[$fechaStr]) ? round($datosPorFecha[$fechaStr]->total_minutos / 60, 2) : 0;
            $sesiones[] = isset($datosPorFecha[$fechaStr]) ? $datosPorFecha[$fechaStr]->total_sesiones : 0;
            $current->addDay();
        }

        $this->datosLinea = [
            'labels' => $labels,
            'horas' => $minutos,
            'sesiones' => $sesiones,
        ];
    }

    private function cargarTabla($inicio, $fin)
    {
        $query = SesionUsuario::with('user')
            ->whereBetween('login_at', [$inicio, $fin])
            ->orderByDesc('login_at')
            ->limit(50);

        if ($this->usuarioSeleccionado) {
            $query->where('user_id', $this->usuarioSeleccionado);
        }

        $this->sesionesTabla = $query->get()->map(function ($s) {
            return [
                'usuario' => $s->user->name ?? 'N/A',
                'login_at' => $s->login_at?->format('d/m/Y H:i'),
                'logout_at' => $s->logout_at?->format('d/m/Y H:i') ?? 'Activa',
                'duracion' => $s->duracion_minutos ? round($s->duracion_minutos, 1) . ' min' : 'En curso',
                'ip' => $s->ip_address,
            ];
        })->toArray();
    }

    public function getUsuariosProperty()
    {
        return User::where('activo', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.tiempo-usabilidad')->layout('layouts.app');
    }
}
