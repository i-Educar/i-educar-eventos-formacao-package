<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

final class Processos
{
    public const int RAIZ = 881900;

    public const int PAINEL = 881901;

    public const int EVENTOS = 881902;

    public const int INSCRICOES = 881903;

    public const int PRESENCA = 881904;

    public const int CERTIFICADOS = 881905;

    /** Relatórios — frequência por evento */
    public const int REL_FREQUENCIA = 881920;

    /** @return list<int> */
    public static function todos(): array
    {
        return [
            self::RAIZ,
            self::PAINEL,
            self::EVENTOS,
            self::INSCRICOES,
            self::PRESENCA,
            self::CERTIFICADOS,
            self::REL_FREQUENCIA,
        ];
    }

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Eventos e formação',
            self::PAINEL => 'Painel',
            self::EVENTOS => 'Eventos',
            self::INSCRICOES => 'Inscrições',
            self::PRESENCA => 'Presença',
            self::CERTIFICADOS => 'Certificados',
            self::REL_FREQUENCIA => 'Frequência',
        ];
    }
}
