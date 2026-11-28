<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

final class EventoPublico
{
    public static function tokenNovo(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function portalModuloHabilitado(): bool
    {
        if (! class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class)) {
            return true;
        }
        $cfg = \iEducar\Packages\BuritiLagunaAmbiente\Support\ModuloConfiguracao::class;

        return $cfg::ativo('eventos') && $cfg::portalExterno('eventos');
    }

    public static function aceitaExterna(array $evento): bool
    {
        if (! self::portalModuloHabilitado()) {
            return false;
        }
        $p = $evento['payload'] ?? [];

        return (bool) ($p['inscricao_externa'] ?? false);
    }

    public static function token(array $evento): ?string
    {
        $t = $evento['payload']['publico_token'] ?? null;

        return is_string($t) && $t !== '' ? $t : null;
    }

    public static function urlInscricao(array $evento): ?string
    {
        $token = self::token($evento);
        if ($token === null || ! self::aceitaExterna($evento)) {
            return null;
        }

        return url('/eventos-formacao/portal/evento/'.$token);
    }

    public static function urlPresenca(array $evento): ?string
    {
        $token = self::token($evento);
        if ($token === null || ! self::aceitaExterna($evento)) {
            return null;
        }

        return url('/eventos-formacao/portal/presenca/'.$token);
    }

    public static function urlCertificado(array $evento): ?string
    {
        $token = self::token($evento);
        if ($token === null || ! self::aceitaExterna($evento)) {
            return null;
        }

        return url('/eventos-formacao/portal/certificado/'.$token);
    }

    public static function buscarPorToken(string $token): ?array
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }
        foreach (PocRegistro::listar('evento') as $evento) {
            if (hash_equals((string) self::token($evento), $token)) {
                return $evento;
            }
        }

        return null;
    }

    public static function inscricoesAbertas(array $evento): bool
    {
        $p = $evento['payload'] ?? [];
        if (($p['status'] ?? '') !== 'aberto') {
            return false;
        }
        if (! self::aceitaExterna($evento)) {
            return false;
        }
        $inicio = $p['inscricao_inicio'] ?? null;
        if (is_string($inicio) && $inicio !== '' && $inicio > date('Y-m-d')) {
            return false;
        }
        $fim = $p['fim'] ?? null;
        if (is_string($fim) && $fim !== '' && $fim < date('Y-m-d')) {
            return false;
        }

        return true;
    }

    public static function vagasRestantes(array $evento): ?int
    {
        $vagas = (int) ($evento['payload']['vagas'] ?? 0);
        if ($vagas <= 0) {
            return null;
        }
        $id = (int) $evento['id'];
        $ja = 0;
        foreach (PocRegistro::listar('inscricao') as $i) {
            if ((int) ($i['payload']['evento_id'] ?? 0) === $id) {
                $ja++;
            }
        }

        return max(0, $vagas - $ja);
    }

    public static function limparCpf(?string $cpf): string
    {
        return preg_replace('/\D+/', '', (string) $cpf) ?? '';
    }

    public static function protocolo(int $inscricaoId): string
    {
        return sprintf('EVT-%s-%05d', date('Y'), $inscricaoId);
    }
}
