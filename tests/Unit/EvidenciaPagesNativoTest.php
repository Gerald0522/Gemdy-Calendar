<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EvidenciaPagesNativoTest extends TestCase
{
    public function test_una_prueba_fallida_bloquea_pages_nativo(): void
    {
        $this->assertTrue(false, 'Fallo controlado para verificar el bloqueo de Pages nativo.');
    }
}
