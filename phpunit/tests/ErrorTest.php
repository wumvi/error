<?php

use PHPUnit\Framework\TestCase;
use Wumvi\Errors\Errors;

class ErrorTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testExceptionHandle(): void
    {
        $this->expectException(\Exception::class);
        error_reporting(E_ALL);
        Errors::attachExceptionHandler();
        throw new Exception('some-error');
    }
}
