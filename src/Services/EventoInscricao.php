<?php

namespace iEducar\Packages\BuritiEventosFormacao\Services;

final class EventoInscricao
{
    public function __construct(private readonly int $vagas, private int $inscritos = 0) {}
    public function podeInscrever(): bool { return $this->inscritos < $this->vagas; }
    public function inscrever(): void {
        if (!$this->podeInscrever()) throw new \RuntimeException('Sem vagas');
        $this->inscritos++;
    }
    public function ocupacao(): array { return ['vagas'=>$this->vagas,'inscritos'=>$this->inscritos,'livres'=>$this->vagas-$this->inscritos]; }
}
