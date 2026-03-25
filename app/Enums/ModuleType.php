<?php

namespace App\Enums;

enum ModuleType: string
{
    case Restaurant   = 'restaurant';
    case Quincaillerie = 'quincaillerie';
    case Boutique     = 'boutique';
    case Location     = 'location';

    public function label(): string
    {
        return match($this) {
            self::Restaurant    => 'Restaurant / Fast-food',
            self::Quincaillerie => 'Quincaillerie',
            self::Boutique      => 'Boutique / POS',
            self::Location      => 'Location',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Restaurant    => 'utensils',
            self::Quincaillerie => 'wrench',
            self::Boutique      => 'cart-shopping',
            self::Location      => 'key',
        };
    }
}
