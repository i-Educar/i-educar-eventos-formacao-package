<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

final class Identidade
{
    public const COR = '#0f5c4c';

    public const DESTAQUE = '#1a8a72';

    public const COR_CLARA = '#e6f5f1';

    public const ICONE = 'fa-calendar';

    public const NOME = 'Eventos e formação';

    public const DESCRICAO = 'Cursos, palestras, inscrição e certificados';

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
