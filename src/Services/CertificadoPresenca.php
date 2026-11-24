<?php

namespace iEducar\Packages\BuritiEventosFormacao\Services;

final class CertificadoPresenca
{
    public static function elegivel(float $presencaPercentual, float $minimo = 75.0): bool {
        return $presencaPercentual >= $minimo;
    }
    public static function codigo(int $eventoId, int $pessoaId): string {
        return sprintf('CERT-%d-%d-%s', $eventoId, $pessoaId, substr(sha1($eventoId.'|'.$pessoaId),0,8));
    }
}
