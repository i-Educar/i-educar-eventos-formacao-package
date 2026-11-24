#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__.'/../src/Services/EventoInscricao.php';
require __DIR__.'/../src/Services/CertificadoPresenca.php';

$ok=0; $fail=0;
$assert = static function(bool $c, string $m) use (&$ok,&$fail): void {
  echo ($c? '  OK  ':' FAIL ').$m."\n";
  $c ? $ok++ : $fail++;
};

$e = new \iEducar\Packages\BuritiEventosFormacao\Services\EventoInscricao(2);
$e->inscrever(); $e->inscrever();
$assert($e->podeInscrever()===false, 'evento lotado');
$assert(\iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca::elegivel(80)===true, 'certificado elegível');
$assert(str_starts_with(\iEducar\Packages\BuritiEventosFormacao\Services\CertificadoPresenca::codigo(1,2),'CERT-'), 'código certificado');

echo "\nResultado: $ok ok, $fail falhas\n";
exit($fail>0?1:0);
