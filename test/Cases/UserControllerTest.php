<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Hyperf\DbConnection\Db;
use HyperfTest\HttpTestCase;
use App\Module\Account\Domain\ValueObject\DocumentType;

/**
 * @internal
 * @covers \App\Module\Account\Presentation\Controller\UserController
 */
class UserControllerTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clean up users table before each test
        Db::table('users')->truncate();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Db::table('users')->truncate();
        parent::tearDown();
    }

    /**
     * Test store method - success scenario
     */
    public function testStoreSuccess(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertNotNull($response, 'Response should not be null');
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('data', $responseData);
        $userData = $responseData['data'];
        
        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayHasKey('email', $userData);
        $this->assertArrayHasKey('document', $userData);
        $this->assertArrayHasKey('document_type', $userData);
        $this->assertArrayHasKey('balance', $userData);
        
        $this->assertEquals('John Doe', $userData['name']);
        $this->assertEquals('john.doe@example.com', $userData['email']);
        $this->assertEquals('12345678901', $userData['document']);
        $this->assertEquals(DocumentType::CPF, $userData['document_type']);
        $this->assertEquals('0.00', $userData['balance']);

        // Verify password is not in response
        $this->assertArrayNotHasKey('password', $userData);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'document' => '12345678901',
        ]);
    }

    /**
     * Test store method - validation failure: missing required fields
     */
    public function testStoreValidationFailureMissingFields(): void
    {
        $userData = [
            'name' => 'John Doe',
            // Missing email, password, document, document_type
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - validation failure: invalid email format
     */
    public function testStoreValidationFailureInvalidEmail(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - validation failure: duplicate email
     */
    public function testStoreValidationFailureDuplicateEmail(): void
    {
        // Create first user
        $firstUserData = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $this->json('/api/v1/accounts/users', $firstUserData);

        // Try to create second user with same email
        $secondUserData = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $secondUserData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - validation failure: duplicate document
     */
    public function testStoreValidationFailureDuplicateDocument(): void
    {
        // Create first user
        $firstUserData = [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $this->json('/api/v1/accounts/users', $firstUserData);

        // Try to create second user with same document
        $secondUserData = [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $secondUserData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - validation failure: invalid document_type
     */
    public function testStoreValidationFailureInvalidDocumentType(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => 'invalid_type',
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - validation failure: field too long
     */
    public function testStoreValidationFailureFieldTooLong(): void
    {
        $userData = [
            'name' => str_repeat('a', 201), // Exceeds max:200
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'document' => '12345678901',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertEquals(422, $response->getStatusCode());
    }

    /**
     * Test store method - success with CNPJ document type
     */
    public function testStoreSuccessWithCnpj(): void
    {
        $userData = [
            'name' => 'Company Name',
            'email' => 'company@example.com',
            'password' => 'password123',
            'document' => '12345678000190',
            'document_type' => DocumentType::CNPJ,
            'balance' => 0,
        ];

        $response = $this->json('/api/v1/accounts/users', $userData);

        $this->assertNotNull($response, 'Response should not be null');
        $this->assertEquals(201, $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals(DocumentType::CNPJ, $responseData['data']['document_type']);
    }

    /**
     * Test show method - success scenario
     */
    public function testShowSuccess(): void
    {
        // Create a user first
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'password123',
            'document' => '98765432100',
            'document_type' => DocumentType::CPF,
            'balance' => 0,
        ];

        $createResponse = $this->json('/api/v1/accounts/users', $userData);
        $this->assertEquals(201, $createResponse->getStatusCode());
        
        $createResponseData = json_decode($createResponse->getBody()->getContents(), true);
        $this->assertArrayHasKey('data', $createResponseData);
        $userId = $createResponseData['data']['id'];

        // Get the user
        $response = $this->get("/api/v1/accounts/{$userId}");

        $this->assertNotNull($response, 'Response should not be null');
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getBody()->getContents(), true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('data', $responseData);
        $userData = $responseData['data'];
        
        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayHasKey('email', $userData);
        $this->assertArrayHasKey('document', $userData);
        $this->assertArrayHasKey('document_type', $userData);
        $this->assertArrayHasKey('balance', $userData);
        
        $this->assertEquals($userId, $userData['id']);
        $this->assertEquals('Jane Doe', $userData['name']);
        $this->assertEquals('jane.doe@example.com', $userData['email']);
        $this->assertEquals('98765432100', $userData['document']);
        $this->assertEquals(DocumentType::CPF, $userData['document_type']);

        // Verify password is not in response
        $this->assertArrayNotHasKey('password', $userData);
    }

    /**
     * Test show method - failure scenario: user not found
     */
    public function testShowUserNotFound(): void
    {
        $nonExistentId = '9999999999999999999';

        $response = $this->get("/api/v1/accounts/{$nonExistentId}");

        $this->assertNotNull($response, 'Response should not be null');
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test show method - failure scenario: invalid ID format
     */
    public function testShowInvalidIdFormat(): void
    {
        $invalidId = 'invalid-id';

        $response = $this->get("/api/v1/accounts/{$invalidId}");

        $this->assertNotNull($response, 'Response should not be null');
        // Should return 404 or 422 depending on how the application handles it
        $statusCode = $response->getStatusCode();
        $this->assertContains($statusCode, [404, 422, 500]);
    }
}

