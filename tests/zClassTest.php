<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class zClassTest extends TestCase
{
    public function testParseInt(): void
    {
		$this->assertNull(
            z::parseInt(null)
        );
		
        $this->assertEquals(
			0,
            z::parseInt('Hello')
        );
		
		$this->assertEquals(
			15,
            z::parseInt('15')
        );
    }

	public function testParseFloat(): void
    {
		$this->assertNull(
            z::parseFloat(null)
        );
		
        $this->assertEquals(
			0,
            z::parseFloat('Hello')
        );
		
		$this->assertEquals(
			15.55,
            z::parseFloat('15.55')
        );
    }
	
	public function testSafeDivide(): void
    {
        $this->assertEquals(
			2,
            z::safeDivide(4, 2)
        );
		
		$this->assertEquals(
			0,
            z::safeDivide(4, 0)
        );
    }
	
	public function testPasswordHashFunction(): void
    {
		$password = 'test password';
		$hash = z::createHash($password);
		
        $this->assertNotTrue(			
            z::verifyHash($password, 'not a real hash')
        );
		
		$this->assertTrue(
			z::verifyHash($password, $hash)
        );
    }

	public function testRandomTokenGenerator(): void
    {
		$len = 10;
		$token = z::generateRandomToken($len);
		
        $this->assertEquals(			
			$len,
            strlen($token)
        );
		
    }

	public function testTrim(): void
    {
		$str = '   test   ';
		
        $this->assertEquals(			
			z::trim($str),
            'test'
        );
		
    }

    public function testTrimSpecial(): void
    {
        $str = ' .,-*/test.,-*/ ';

        $this->assertEquals(
            z::trimSpecial($str),
            'test'
        );

    }

    public function testTrimSlashes(): void
    {
        $str = '/test/';

        $this->assertEquals(
            z::trimSlashes($str),
            'test'
        );

    }

}