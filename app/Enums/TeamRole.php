<?php

namespace App\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Cashier = 'cashier';
    case Supervisor = 'supervisor';

    /**
     * Nombre mostrado al usuario.
     */
    public function label(): string
    {
        return match ($this) {
            self::Cashier => 'Cajero',
            self::Supervisor => 'Supervisor',
            default => ucfirst($this->value),
        };
    }

    /**
     * Permisos administrativos del equipo.
     *
     * Estos permisos son diferentes de los
     * permisos financieros de la cooperativa.
     *
     * @return array<TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => TeamPermission::cases(),

            self::Admin => [
                TeamPermission::UpdateTeam,
                TeamPermission::CreateInvitation,
                TeamPermission::CancelInvitation,
            ],

            self::Member,
            self::Cashier,
            self::Supervisor => [],
        };
    }

    public function hasPermission(
        TeamPermission $permission
    ): bool {
        return in_array(
            $permission,
            $this->permissions(),
            true
        );
    }

    /**
     * Jerarquía de administración del equipo.
     *
     * Cajero y Supervisor no heredan permisos
     * administrativos por su jerarquía.
     */
    public function level(): int
    {
        return match ($this) {
            self::Owner => 3,
            self::Admin => 2,

            self::Member,
            self::Cashier,
            self::Supervisor => 1,
        };
    }

    public function isAtLeast(TeamRole $role): bool
    {
        return $this->level() >= $role->level();
    }

    /**
     * Roles asignables a integrantes.
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(
                fn (self $role) => $role !== self::Owner
            )
            ->map(
                fn (self $role) => [
                    'value' => $role->value,
                    'label' => $role->label(),
                ]
            )
            ->values()
            ->toArray();
    }
}