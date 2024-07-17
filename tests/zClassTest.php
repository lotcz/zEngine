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

		$this->assertEquals(
			15,
			z::parseInt(15)
		);

		$this->assertEquals(
			0,
			z::parseInt(0)
		);

		$this->assertEquals(
			0,
			z::parseInt('0')
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
			'test',
			z::trim($str)
		);

	}

	public function testTrimSpecial(): void
	{
		$str = ' .,-*/test.,-*/ ';

		$this->assertEquals(
			'test',
			z::trimSpecial($str)
		);

	}

	public function testTrimSlashes(): void
	{
		$str = '/test/';

		$this->assertEquals(
			'test',
			z::trimSlashes($str)
		);

	}

	public function testStrlen(): void	{
		$str = '123456789';

		$this->assertEquals(
			9,
			z::strlen($str)
		);

		$str = 'test';

		$this->assertEquals(
			4,
			z::strlen($str)
		);

		$str = 'příliš žluťoučký kůň';

		$this->assertEquals(
			20,
			z::strlen($str)
		);

		$str = 0;

		$this->assertEquals(
			1,
			z::strlen($str)
		);


		$str = 15;

		$this->assertEquals(
			2,
			z::strlen($str)
		);

		$str = null;

		$this->assertEquals(
			0,
			z::strlen($str)
		);

	}

}
