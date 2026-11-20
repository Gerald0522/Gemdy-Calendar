<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EvidenciaControlDespliegueTest extends TestCase
{
    public function test_fallo_controlado_bloquea_el_despliegue(): void
    {
        $this->assertTrue(false, 'Fallo controlado del Laboratorio 14 en main.');
    }
}
