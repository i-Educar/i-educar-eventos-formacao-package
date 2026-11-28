<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

final class Identidade
{
    public const COR = '#0a7a8a';

    public const DESTAQUE = '#20c997';

    public const COR_CLARA = '#e6f8f5';

    public const ICONE = 'fa-calendar';

    public const NOME = 'Eventos e formação';

    public const DESCRICAO = 'Gestão de eventos, inscrição externa, presença e certificados';

    /** @return array{cor:string,destaque:string,clara:string,icone:string,nome:string,descricao:string} */
    public static function tokens(): array
    {
        return [
            'cor' => self::COR,
            'destaque' => self::DESTAQUE,
            'clara' => self::COR_CLARA,
            'icone' => self::ICONE,
            'nome' => self::NOME,
            'descricao' => self::DESCRICAO,
        ];
    }
}
