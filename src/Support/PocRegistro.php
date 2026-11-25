<?php

namespace iEducar\Packages\BuritiEventosFormacao\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class PocRegistro
{
    private const TABELA = 'eventos_formacao.poc_registro';

    public static function pronto(): bool
    {
        try {
            return Schema::hasTable(self::TABELA);
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return list<array{id:int,tipo:string,titulo:string,meta:?string,payload:array,created_at:?string}> */
    public static function listar(string $tipo): array
    {
        if (! self::pronto()) {
            return [];
        }

        return DB::table(self::TABELA)
            ->where('tipo', $tipo)
            ->orderByDesc('id')
            ->get()
            ->map(static fn ($r): array => [
                'id' => (int) $r->id,
                'tipo' => (string) $r->tipo,
                'titulo' => (string) $r->titulo,
                'meta' => $r->meta !== null ? (string) $r->meta : null,
                'payload' => is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : ((array) ($r->payload ?? [])),
                'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
            ])
            ->all();
    }

    public static function contar(string $tipo): int
    {
        if (! self::pronto()) {
            return 0;
        }

        return (int) DB::table(self::TABELA)->where('tipo', $tipo)->count();
    }

    /** @param  array<string, mixed>  $payload */
    public static function salvar(string $tipo, string $titulo, ?string $meta, array $payload): int
    {
        return (int) DB::table(self::TABELA)->insertGetId([
            'tipo' => $tipo,
            'titulo' => $titulo,
            'meta' => $meta,
            'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }

    public static function buscar(int $id): ?array
    {
        if (! self::pronto()) {
            return null;
        }
        $r = DB::table(self::TABELA)->where('id', $id)->first();
        if ($r === null) {
            return null;
        }

        return [
            'id' => (int) $r->id,
            'tipo' => (string) $r->tipo,
            'titulo' => (string) $r->titulo,
            'meta' => $r->meta !== null ? (string) $r->meta : null,
            'payload' => is_string($r->payload) ? (json_decode($r->payload, true) ?: []) : ((array) ($r->payload ?? [])),
            'created_at' => $r->created_at !== null ? (string) $r->created_at : null,
        ];
    }

    /** @param  array<string, mixed>  $payload */
    public static function atualizar(int $id, array $payload, ?string $titulo = null, ?string $meta = null): void
    {
        $dados = ['payload' => json_encode($payload, JSON_UNESCAPED_UNICODE)];
        if ($titulo !== null) {
            $dados['titulo'] = $titulo;
        }
        if ($meta !== null) {
            $dados['meta'] = $meta;
        }
        DB::table(self::TABELA)->where('id', $id)->update($dados);
    }
}
