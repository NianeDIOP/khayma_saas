<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin  = 'super_admin';
    case Owner       = 'owner';
    case Manager     = 'manager';
    case Caissier    = 'caissier';
    case Magasinier  = 'magasinier';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Owner      => 'Propriétaire',
            self::Manager    => 'Manager',
            self::Caissier   => 'Caissier',
            self::Magasinier => 'Magasinier',
        };
    }
}
