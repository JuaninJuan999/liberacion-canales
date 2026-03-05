<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateUsernamesForUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-usernames-for-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera nombres de usuario para todos los usuarios sin username asignado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obtener usuarios sin username
        $usersWithoutUsername = User::whereNull('username')->get();

        if ($usersWithoutUsername->isEmpty()) {
            $this->info('Todos los usuarios ya tienen un nombre de usuario asignado.');
            return;
        }

        $this->info("Generando nombres de usuario para {$usersWithoutUsername->count()} usuarios...");

        foreach ($usersWithoutUsername as $user) {
            // Convertir nombre a formato "nombre.apellido"
            $parts = explode(' ', trim($user->name));
            
            if (count($parts) >= 2) {
                // primer nombre + punto + primer apellido
                $username = strtolower($parts[0] . '.' . $parts[1]);
            } else {
                // Solo usar el nombre si no hay apellido
                $username = strtolower($parts[0]);
            }

            // Generar username único si ya existe
            $baseUsername = $username;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user->update(['username' => $username]);
            $this->info("Usuario '{$user->name}' → '{$username}'");
        }

        $this->info('✓ Nombres de usuario generados exitosamente.');
    }
}
