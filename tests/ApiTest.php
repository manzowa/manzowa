
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
        $response = $this->makeRequest('GET', '/items');
        $this->assertEquals(200, $response['status']);
        $this->assertIsArray(json_decode($response['body'], true));
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status' => $status, 'body' => $body];
    }
}
