Test Directory Structure:
tests/
├── Unit/                     # Unit tests
│   ├── Entities/            # Entity-specific tests
│   ├── Services/            # Service layer tests
│   └── Validators/          # Validation logic tests
├── Integration/             # Integration tests
│   ├── API/                 # API endpoint tests
│   ├── Database/           # Database integration tests
│   └── Services/           # Service integration tests
├── Functional/             # Functional/Feature tests
│   └── Controllers/        # Controller tests
├── Fixtures/               # Test data fixtures
├── bootstrap.php           # Test initialization
├── config.php           # Test initialization
├── TestCase.php           # Base test class
└── TestFactory.php        # Test data factory

2. Key Components:

a) Base TestCase Class (TestCase.php):
- Handles database transactions (already implemented)
- Provides helper methods for authentication
- Manages test environment setup/teardown
- Includes assertion helpers

b) Test Factory (TestFactory.php):
- Creates test entities and fixtures
- Manages test data generation
- Provides helper methods for common test scenarios

3. Testing Practices:

a) Unit Tests:
```php
class UserEntityTest extends \MapasCulturais\Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup
    }

    public function testUserValidation()
    {
        // Test entity validation
    }
}
b) Integration Tests:

class UserServiceTest extends \MapasCulturais\Tests\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->app->em->rollback();
        parent::tearDown();
    }

    public function testUserCreation()
    {
        // Test service with database
    }
}
c) API Tests:

class ApiEndpointTest extends \MapasCulturais\Tests\TestCase
{
    public function testGetEndpoint()
    {
        $response = $this->get('/api/endpoint');
        $this->assertEquals(200, $response->http_status_code);
    }
}
Best Practices:
a) Database Testing:

Use transactions for isolation (already implemented)
Create specific test fixtures
Reset database state between tests
b) Dependency Injection:

Use the App container for service access
Mock external services when needed
Utilize TestFactory for entity creation
c) Test Organization:

One test class per entity/service
Descriptive test method names
Group related tests using PHPUnit annotations
Configuration (phpunit.xml):
Separate test suites for unit/integration tests
Configure test database
Set up code coverage reporting
This structure provides:

Clear separation of concerns
Easy test maintenance
Reliable test isolation
Efficient test execution
Good test coverage management
The existing TestCase.php already provides excellent foundations with transaction management, authentication helpers, and assertion methods. Build upon this structure while maintaining the established patterns.

