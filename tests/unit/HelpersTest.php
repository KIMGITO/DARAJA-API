<?php

namespace Codenson\Daraja\Tests\Unit;

use Codenson\Daraja\Tests\TestCase;

class HelpersTest extends TestCase
{
    /** @test */
    public function it_formats_phone_number_correctly()
    {
        // Test with 07XXXXXXXX format
        $this->assertEquals('254712345678', formatPhoneNumber('0712345678'));
        
        // Test with 254XXXXXXXXX format
        $this->assertEquals('254712345678', formatPhoneNumber('254712345678'));
        
        // Test with +254XXXXXXXXX format
        $this->assertEquals('254712345678', formatPhoneNumber('+254712345678'));
        
        // Test with 2540XXXXXXXX format
        $this->assertEquals('254712345678', formatPhoneNumber('2540712345678'));
        
        // Test with spaces and special characters
        $this->assertEquals('254712345678', formatPhoneNumber('+254 712 345 678'));
    }

    /** @test */
    public function it_formats_money_correctly()
    {
        $this->assertEquals(1000, formatMoney(1000));
        $this->assertEquals(1000, formatMoney(1000.50));
        $this->assertEquals(1001, formatMoney(1000.99));
        $this->assertEquals(0, formatMoney(0));
    }
}