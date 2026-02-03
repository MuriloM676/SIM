<?php

namespace App\Enums;

/**
 * Gravidade das infrações conforme CTB
 */
enum GravidadeInfracao: string
{
    case LEVE = 'leve';
    case MEDIA = 'media';
    case GRAVE = 'grave';
    case GRAVISSIMA = 'gravissima';

    public function label(): string
    {
        return match($this) {
            self::LEVE => 'Leve',
            self::MEDIA => 'Média',
            self::GRAVE => 'Grave',
            self::GRAVISSIMA => 'Gravíssima',
        };
    }

    /**
     * Pontos na CNH conforme CTB
     */
    public function pontos(): int
    {
        return match($this) {
            self::LEVE => 3,
            self::MEDIA => 4,
            self::GRAVE => 5,
            self::GRAVISSIMA => 7,
        };
    }

    /**
     * Valor base em reais (orientativo)
     */
    public function valorBase(): float
    {
        return match($this) {
            self::LEVE => 88.38,
            self::MEDIA => 130.16,
            self::GRAVE => 195.23,
            self::GRAVISSIMA => 293.47,
        };
    }
}
