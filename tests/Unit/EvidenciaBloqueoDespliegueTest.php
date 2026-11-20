<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EvidenciaBloqueoDespliegueTest extends TestCase
{
    public function test_fallo_deliberado_para_evidencia_del_laboratorio(): void
    {
        $this->fail('Fallo deliberado del Laboratorio 14 para verificar el bloqueo del despliegue.');
    }
}
