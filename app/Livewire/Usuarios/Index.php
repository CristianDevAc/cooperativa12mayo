<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class Index extends Component
{
    #[Locked]
    public ?int $usuarioEditarId = null;

    #[Locked]
    public string $usuarioEditarNombre = '';

    public string $rolNuevo = '';

    public string $mensaje = '';
    public string $errorOperacion = '';
    public bool $formularioNuevoVisible = false;

    public string $nuevoNombre = '';
    public string $nuevoEmail = '';
    public string $nuevoPassword = '';
    public string $nuevoPassword_confirmation = '';
    public string $nuevoRolUsuario = 'cashier';
    #[Locked]
    public ?int $claveUsuarioId = null;

    public string $claveNueva = '';
    public string $claveNueva_confirmation = '';
    public string $miClaveActual = '';

    private function teamId(): int
    {
        $usuario = Auth::user();

        abort_unless(
            $usuario instanceof User
            && $usuario->current_team_id !== null,
            403
        );

        return (int) $usuario->current_team_id;
    }

    private function rolActual(): ?string
    {
        return DB::table('team_members')
            ->where('team_id', $this->teamId())
            ->where('user_id', Auth::id())
            ->value('role');
    }

    private function puedeEditar(
        string $rolActor,
        int $actorId,
        object $miembro
    ): bool {
        if ((int) $miembro->user_id === $actorId) {
            return false;
        }

        if ($miembro->role === 'owner') {
            return false;
        }

        if (
            $rolActor === 'admin'
            && $miembro->role === 'admin'
        ) {
            return false;
        }

        return in_array($rolActor, ['owner', 'admin'], true);
    }

    public function editar(int $usuarioId): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->mensaje = '';
        $this->errorOperacion = '';
        $this->resetValidation();

        $this->usuarioEditarId = null;

        $miembro = DB::table('team_members as tm')
            ->join('users as u', 'u.id', '=', 'tm.user_id')
            ->where('tm.team_id', $this->teamId())
            ->where('tm.user_id', $usuarioId)
            ->select(
                'tm.user_id',
                'tm.role',
                'u.name'
            )
            ->first();

        if ($miembro === null) {
            $this->errorOperacion =
                'El usuario no pertenece a esta cooperativa.';

            return;
        }

        $rolActor = $this->rolActual();

        if (! $this->puedeEditar(
            (string) $rolActor,
            (int) Auth::id(),
            $miembro
        )) {
            $this->errorOperacion =
                'No tiene autorización para modificar este usuario.';

            return;
        }

        $this->usuarioEditarId = (int) $miembro->user_id;
        $this->usuarioEditarNombre = $miembro->name;
        $this->rolNuevo = $miembro->role;
    }

    public function guardarRol(): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->mensaje = '';
        $this->errorOperacion = '';

        if ($this->usuarioEditarId === null) {
            $this->errorOperacion =
                'Seleccione primero un usuario.';

            return;
        }

        $this->validate([
            'rolNuevo' => [
                'required',
                Rule::in([
                    'admin',
                    'cashier',
                    'supervisor',
                    'member',
                ]),
            ],
        ]);

        $teamId = $this->teamId();
        $actorId = (int) Auth::id();
        $usuarioId = $this->usuarioEditarId;
        $nuevoRol = $this->rolNuevo;

        try {

            DB::transaction(function () use (
                $teamId,
                $actorId,
                $usuarioId,
                $nuevoRol
            ) {

                /*
                 * Comprobamos nuevamente el permiso
                 * dentro de la transacción.
                 */
                $rolActor = DB::table('team_members')
                    ->where('team_id', $teamId)
                    ->where('user_id', $actorId)
                    ->lockForUpdate()
                    ->value('role');

                if (! in_array(
                    $rolActor,
                    ['owner', 'admin'],
                    true
                )) {
                    throw new DomainException(
                        'Ya no tiene permiso para administrar usuarios.'
                    );
                }

                $miembro = DB::table('team_members')
                    ->where('team_id', $teamId)
                    ->where('user_id', $usuarioId)
                    ->lockForUpdate()
                    ->first();

                if ($miembro === null) {
                    throw new DomainException(
                        'El usuario ya no pertenece a la cooperativa.'
                    );
                }

                if (! $this->puedeEditar(
                    $rolActor,
                    $actorId,
                    $miembro
                )) {
                    throw new DomainException(
                        'No está permitido modificar este usuario.'
                    );
                }

                /*
                 * Solamente el propietario puede
                 * asignar el rol administrador.
                 */
                $rolesPermitidos = $rolActor === 'owner'
                    ? ['admin', 'cashier', 'supervisor', 'member']
                    : ['cashier', 'supervisor', 'member'];

                if (! in_array(
                    $nuevoRol,
                    $rolesPermitidos,
                    true
                )) {
                    throw new DomainException(
                        'No tiene autorización para asignar ese rol.'
                    );
                }

                DB::table('team_members')
                    ->where('id', $miembro->id)
                    ->where('team_id', $teamId)
                    ->update([
                        'role' => $nuevoRol,
                        'updated_at' => now(),
                    ]);

            }, 3);

            $this->cancelar();

            $this->mensaje =
                'El rol del usuario fue actualizado correctamente.';

        } catch (DomainException $e) {

            $this->errorOperacion = $e->getMessage();

        }
    }

    public function cancelar(): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->usuarioEditarId = null;
        $this->usuarioEditarNombre = '';
        $this->rolNuevo = '';

        $this->resetValidation();
    }

    public function abrirNuevoUsuario(): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->reset([
            'nuevoNombre',
            'nuevoEmail',
            'nuevoPassword',
            'nuevoPassword_confirmation',
            'nuevoRolUsuario',
        ]);

        $this->nuevoRolUsuario = 'cashier';
        $this->mensaje = '';
        $this->errorOperacion = '';
        $this->formularioNuevoVisible = true;

        $this->resetValidation();
    }

    public function cancelarNuevoUsuario(): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->reset([
            'nuevoNombre',
            'nuevoEmail',
            'nuevoPassword',
            'nuevoPassword_confirmation',
            'nuevoRolUsuario',
        ]);

        $this->formularioNuevoVisible = false;

        $this->resetValidation();
    }

    public function crearUsuario(): void
    {
        Gate::authorize('gestionar-usuarios');

        $this->mensaje = '';
        $this->errorOperacion = '';

        $this->nuevoEmail = strtolower(trim($this->nuevoEmail));
        $this->nuevoNombre = trim($this->nuevoNombre);

        $this->validate([
            'nuevoNombre' => ['required', 'string', 'max:255'],

            'nuevoEmail' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'nuevoPassword' => [
                'required',
                'confirmed',
                Password::min(10)->letters()->numbers(),
            ],

            'nuevoRolUsuario' => [
                'required',
                Rule::in([
                    'admin',
                    'cashier',
                    'supervisor',
                    'member',
                ]),
            ],
        ]);

        $teamId = $this->teamId();
        $actorId = (int) Auth::id();

        DB::transaction(function () use ($teamId, $actorId) {

            $rolActor = DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $actorId)
                ->lockForUpdate()
                ->value('role');

            $rolesPermitidos = match ($rolActor) {
                'owner' => [
                    'admin',
                    'cashier',
                    'supervisor',
                    'member',
                ],
                'admin' => [
                    'cashier',
                    'supervisor',
                    'member',
                ],
                default => [],
            };

            if (! in_array(
                $this->nuevoRolUsuario,
                $rolesPermitidos,
                true
            )) {
                throw new DomainException(
                    'No tiene permiso para asignar este rol.'
                );
            }

            $usuario = User::create([
                'name' => $this->nuevoNombre,
                'email' => $this->nuevoEmail,
                'password' => Hash::make($this->nuevoPassword),
            ]);

            DB::table('team_members')->insert([
                'team_id' => $teamId,
                'user_id' => $usuario->id,
                'role' => $this->nuevoRolUsuario,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            * La administración confirma previamente
            * que el correo pertenece al usuario.
            * Así podrá acceder sin activación por email.
            */
            $usuario->forceFill([
                'current_team_id' => $teamId,
                'email_verified_at' => now(),
            ])->save();

        }, 3);

        $this->cancelarNuevoUsuario();

        $this->mensaje = 'Usuario creado correctamente.';
    }
    public function abrirCambioClave(int $usuarioId): void
    {
        Gate::authorize('gestionar-usuarios');

        $rolActor = $this->rolActual();

        $miembro = DB::table('team_members')
            ->where('team_id', $this->teamId())
            ->where('user_id', $usuarioId)
            ->first();

        abort_if(! $miembro, 404);

        $esPropio = $usuarioId === (int) Auth::id();

        abort_unless(
            in_array($rolActor, ['owner', 'admin'], true)
            && (
                $esPropio
                || (
                    $miembro->role !== 'owner'
                    && ! (
                        $rolActor === 'admin'
                        && $miembro->role === 'admin'
                    )
                )
            ),
            403
        );

        $this->claveUsuarioId = $usuarioId;
        $this->claveNueva = '';
        $this->claveNueva_confirmation = '';
        $this->miClaveActual = '';

        $this->resetValidation();
    }

    public function guardarCambioClave(): void
    {
        Gate::authorize('gestionar-usuarios');

        abort_if($this->claveUsuarioId === null, 400);

        $this->validate([
            'claveNueva' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'miClaveActual' => ['required', 'string'],
        ]);

        $usuarioId = $this->claveUsuarioId;
        $actorId = (int) Auth::id();
        $teamId = $this->teamId();

        DB::transaction(function () use (
            $usuarioId,
            $actorId,
            $teamId
        ) {
            $actor = User::query()
                ->whereKey($actorId)
                ->lockForUpdate()
                ->firstOrFail();

            $rolActor = DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $actorId)
                ->lockForUpdate()
                ->value('role');

            $rolDestino = DB::table('team_members')
                ->where('team_id', $teamId)
                ->where('user_id', $usuarioId)
                ->lockForUpdate()
                ->value('role');

            $esPropio = $actorId === $usuarioId;

            abort_unless(
                in_array($rolActor, ['owner', 'admin'], true)
                && $rolDestino !== null
                && (
                    $esPropio
                    || (
                        $rolDestino !== 'owner'
                        && ! (
                            $rolActor === 'admin'
                            && $rolDestino === 'admin'
                        )
                    )
                ),
                403
            );

            if (! Hash::check($this->miClaveActual, $actor->password)) {
                throw ValidationException::withMessages([
                    'miClaveActual' => 'Tu contraseña actual no es correcta.',
                ]);
            }

            $usuario = User::query()
                ->whereKey($usuarioId)
                ->lockForUpdate()
                ->firstOrFail();

            $usuario->forceFill([
                'password' => Hash::make($this->claveNueva),
                'remember_token' => null,
            ])->save();

            if (config('session.driver') === 'database' && ! $esPropio) {
                DB::table('sessions')
                    ->where('user_id', $usuarioId)
                    ->delete();
            }
        });

        $this->claveUsuarioId = null;
        $this->claveNueva = '';
        $this->claveNueva_confirmation = '';
        $this->miClaveActual = '';

        $this->mensaje = 'Contraseña actualizada correctamente.';
    }
    public function render()
    {
        Gate::authorize('gestionar-usuarios');

        $teamId = $this->teamId();
        $rolActor = $this->rolActual();

        $usuarios = DB::table('team_members as tm')
            ->join('users as u', 'u.id', '=', 'tm.user_id')
            ->where('tm.team_id', $teamId)
            ->select(
                'u.id',
                'u.name',
                'u.email',
                'tm.role',
                'tm.created_at'
            )
            ->orderByRaw("
                CASE tm.role
                    WHEN 'owner' THEN 1
                    WHEN 'admin' THEN 2
                    WHEN 'cashier' THEN 3
                    WHEN 'supervisor' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('u.name')
            ->get();

        return view('livewire.usuarios.index', [
            'usuarios' => $usuarios,
            'rolActor' => $rolActor,
            'actorId' => (int) Auth::id(),
        ]);
    }
}