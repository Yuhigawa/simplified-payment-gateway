<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\DbConnection\Db;
use HyperfTest\HttpTestCase;
use App\Module\Account\Domain\ValueObject\DocumentType;

/**
 * @internal
 * @covers \App\Module\Transaction\Presentation\Controller\TransferController
 * @covers \App\Module\Transaction\Application\Service\TransferService
 * @covers \App\Module\Transaction\Presentation\Request\TransferRequest
 * 
 * Note: These tests call real external services (authorization and notification).
 * For complete test isolation, consider mocking the Guzzle HTTP client in the handlers.
 */
class TransferControllerTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Db::table('Transfers')->truncate();
        Db::table('users')->truncate();
    }

    protected function tearDown(): void
    {
        Db::table('Transfers')->truncate();
        Db::table('users')->truncate();
        parent::tearDown();
    }

    /**
     * Helper method to create a user
     */
    private function createUser(array $data): array
    {
        // Generate unique email and document to avoid conflicts
        $uniqueId = uniqid('', true);
        $uniqueId = str_replace('.', '', $uniqueId);
        
        $documentType = $data['document_type'] ?? DocumentType::CPF;
        $documentLength = $documentType === DocumentType::CNPJ ? 14 : 11;
        $document = str_pad(substr($uniqueId, 0, $documentLength), $documentLength, '0', STR_PAD_LEFT);
        
        $userData = array_merge([
            'name' => 'Test User',
            'email' => "test{$uniqueId}@example.com",
            'password' => 'password123',
            'document' => $document,
            'document_type' => $documentType,
            'balance' => 0,
        ], $data);

        $response = $this->json('/api/v1/accounts/users', $userData);
        
        if ($response->getStatusCode() !== 201) {
            $body = $response->getBody()->getContents();
            throw new \RuntimeException("Failed to create user: {$body}");
        }
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData['data'];
    }

    /**
     * Test transfer - success scenario between two CPF users
     */
    public function testTransferSuccessBetweenUsers(): void
    {
        // Create payer with balance
        $payer = $this->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 10000, // 100.00 in cents
        ]);

        // Create payee
        $payee = $this->createUser([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ]);

        $transferData = [
            'value' => 50.00,
            'payer' => (int) $payer['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);

        $this->assertNotNull($response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('payer', $responseData);
        $this->assertArrayHasKey('payee', $responseData);
        $this->assertArrayHasKey('value', $responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('completed', $responseData['status']);
        $this->assertEquals(50.0, $responseData['value']);

        // Verify balances were updated
        $updatedPayer = $this->json("/api/v1/accounts/{$payer['id']}");
        $payerData = json_decode($updatedPayer->getBody()->getContents(), true)['data'];
        $this->assertEquals('50.00', $payerData['balance']); // 100 - 50 = 50

        $updatedPayee = $this->json("/api/v1/accounts/{$payee['id']}");
        $payeeData = json_decode($updatedPayee->getBody()->getContents(), true)['data'];
        $this->assertEquals('50.00', $payeeData['balance']); // 0 + 50 = 50
    }

    /**
     * Test transfer - success scenario from user to merchant
     */
    public function testTransferSuccessUserToMerchant(): void
    {
        $payer = $this->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 10000,
        ]);

        $merchant = $this->createUser([
            'name' => 'Store Name',
            'email' => 'store@example.com',
            'document' => '12345678000190',
            'document_type' => DocumentType::CNPJ,
            'balance' => 0,
        ]);

        $transferData = [
            'value' => 75.50,
            'payer' => (int) $payer['id'],
            'payee' => (int) $merchant['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('completed', $responseData['status']);
    }

    /**
     * Test transfer - validation failure: missing required fields
     */
    public function testTransferValidationFailureMissingFields(): void
    {
        $transferData = [
            'value' => 50.00,
            // Missing payer and payee
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test transfer - validation failure: invalid value
     */
    public function testTransferValidationFailureInvalidValue(): void
    {
        $payer = $this->createUser([
            'document_type' => DocumentType::CPF,
            'balance' => 10000,
        ]);

        $payee = $this->createUser([
            'document_type' => DocumentType::CPF,
        ]);

        // Test negative value
        $transferData = [
            'value' => -10.00,
            'payer' => (int) $payer['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());

        // Test zero value
        $transferData['value'] = 0;
        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());

        // Test value too small
        $transferData['value'] = 0.001;
        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test transfer - validation failure: non-existent payer
     */
    public function testTransferValidationFailureNonExistentPayer(): void
    {
        $payee = $this->createUser([
            'document_type' => DocumentType::CPF,
        ]);

        $transferData = [
            'value' => 50.00,
            'payer' => 999999999,
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test transfer - validation failure: non-existent payee
     */
    public function testTransferValidationFailureNonExistentPayee(): void
    {
        $payer = $this->createUser([
            'document_type' => DocumentType::CPF,
            'balance' => 10000,
        ]);

        $transferData = [
            'value' => 50.00,
            'payer' => (int) $payer['id'],
            'payee' => 999999999,
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test transfer - business rule failure: merchant cannot send money
     */
    public function testTransferBusinessRuleFailureMerchantCannotSend(): void
    {
        $merchant = $this->createUser([
            'name' => 'Merchant Store',
            'email' => 'merchant@example.com',
            'document' => '12345678000190',
            'document_type' => DocumentType::CNPJ,
            'balance' => 10000,
        ]);

        $payee = $this->createUser([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
        ]);

        $transferData = [
            'value' => 50.00,
            'payer' => (int) $merchant['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        
        // Should fail with 500 or appropriate error status
        $statusCode = $response->getStatusCode();
        $this->assertContains($statusCode, [400, 500]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        
        // Verify error message contains information about merchant restriction
        $responseBody = $response->getBody()->getContents();
        $this->assertStringContainsStringIgnoringCase('merchant', $responseBody);
    }

    /**
     * Test transfer - business rule failure: insufficient balance
     */
    public function testTransferBusinessRuleFailureInsufficientBalance(): void
    {
        $payer = $this->createUser([
            'name' => 'Poor User',
            'email' => 'poor@example.com',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 1000, // 10.00 in cents
        ]);

        $payee = $this->createUser([
            'name' => 'Rich User',
            'email' => 'rich@example.com',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
        ]);

        $transferData = [
            'value' => 100.00, // More than available balance
            'payer' => (int) $payer['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        
        // Should fail
        $statusCode = $response->getStatusCode();
        $this->assertContains($statusCode, [400, 500]);

        $responseBody = $response->getBody()->getContents();
        $this->assertStringContainsStringIgnoringCase('balance', $responseBody);

        // Verify balance was not changed
        $updatedPayer = $this->json("/api/v1/accounts/{$payer['id']}");
        $payerData = json_decode($updatedPayer->getBody()->getContents(), true)['data'];
        $this->assertEquals('10.00', $payerData['balance']); // Balance should remain unchanged
    }

    /**
     * Test transfer - validation failure: invalid value type
     */
    public function testTransferValidationFailureInvalidValueType(): void
    {
        $payer = $this->createUser([
            'document_type' => DocumentType::CPF,
            'balance' => 10000,
        ]);

        $payee = $this->createUser([
            'document_type' => DocumentType::CPF,
        ]);

        $transferData = [
            'value' => 'not-a-number',
            'payer' => (int) $payer['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test transfer - validation failure: payer and payee are the same
     */
    public function testTransferValidationFailureSamePayerAndPayee(): void
    {
        $user = $this->createUser([
            'name' => 'Self User',
            'email' => 'self@example.com',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 10000,
        ]);

        $transferData = [
            'value' => 50.00,
            'payer' => (int) $user['id'],
            'payee' => (int) $user['id'],
        ];

        // This might pass validation but should be handled by business logic
        // Or it could fail at validation level
        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        
        // Accept either validation error or business logic error
        $statusCode = $response->getStatusCode();
        $this->assertContains($statusCode, [400, 422, 500]);
    }

    /**
     * Test transfer - exact balance transfer
     */
    public function testTransferExactBalance(): void
    {
        $payer = $this->createUser([
            'name' => 'Exact Balance User',
            'email' => 'exact@example.com',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 5000, // 50.00 in cents
        ]);

        $payee = $this->createUser([
            'name' => 'Receiver',
            'email' => 'receiver@example.com',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ]);

        $transferData = [
            'value' => 50.00, // Exact balance
            'payer' => (int) $payer['id'],
            'payee' => (int) $payee['id'],
        ];

        $response = $this->json('/api/v1/Transfers/transfer', $transferData);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('completed', $responseData['status']);

        // Verify payer balance is now 0
        $updatedPayer = $this->json("/api/v1/accounts/{$payer['id']}");
        $payerData = json_decode($updatedPayer->getBody()->getContents(), true)['data'];
        $this->assertEquals('0.00', $payerData['balance']);
    }
}

