<?php

namespace Tests\Unit\Support;

use App\Support\Rut;
use PHPUnit\Framework\TestCase;

class RutTest extends TestCase
{
    public function test_normalizes_rut_with_or_without_points(): void
    {
        $this->assertSame('12345678-5', Rut::normalize('12.345.678-5'));
        $this->assertSame('12345678-5', Rut::normalize('123456785'));
    }

    public function test_validates_check_digit(): void
    {
        $this->assertTrue(Rut::isValid('12.345.678-5'));
        $this->assertFalse(Rut::isValid('12.345.678-4'));
    }
}
