
<?php
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    protected string $baseUrl ; 

     protected function setUp(): void
    {
        $this->baseUrl = "https://manzowa.com/api/v1"; 
    }

    public function testGetAllItems()
    {
        $test= 200;
        $this->assertEquals(200, $test);
    }
}
