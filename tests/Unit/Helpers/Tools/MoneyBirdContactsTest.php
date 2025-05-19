<?php

namespace Tests\Unit\Helpers\Tools;

use App\Helpers\Moneybird;
use App\Helpers\Tools\Moneybird\MoneyBirdContactsToolbox;
use Mockery;
use Picqer\Financials\Moneybird\Entities\Contact;
use Picqer\Financials\Moneybird\Moneybird as MoneybirdClient;
use PHPUnit\Framework\TestCase;
use stdClass;

class MoneyBirdContactsTest extends TestCase
{
    protected $moneybirdMock;
    protected $contactRepoMock;
    protected $contactMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock objects
        $this->moneybirdMock = Mockery::mock(MoneybirdClient::class);
        $this->contactRepoMock = Mockery::mock(stdClass::class);
        $this->contactMock = Mockery::mock(Contact::class);

        // Mock the Moneybird helper to return our mock client
        Mockery::mock('alias:' . Moneybird::class)
            ->shouldReceive('getMoneybird')
            ->andReturn($this->moneybirdMock);

        // Set up the contact repository mock
        $this->moneybirdMock->shouldReceive('contact')
            ->andReturn($this->contactRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_gets_available_tools()
    {
        $tools = MoneyBirdContactsToolbox::getTools();
        
        $this->assertIsArray($tools);
        $this->assertNotEmpty($tools);
        $this->assertCount(9, $tools); // There should be 9 tools defined
    }
    
    /** @test */
    public function it_gets_all_contacts()
    {
        // Create sample contact data
        $contact1 = new stdClass();
        $contact1->id = '1';
        $contact1->firstname = 'John';
        $contact1->lastname = 'Doe';
        $contact1->company_name = 'ACME Inc.';
        
        $contact2 = new stdClass();
        $contact2->id = '2';
        $contact2->firstname = 'Jane';
        $contact2->lastname = 'Smith';
        $contact2->company_name = 'Tech Co.';
        
        // Set up mock to return sample contacts
        $this->contactRepoMock->shouldReceive('getAll')
            ->once()
            ->andReturn([$contact1, $contact2]);
            
        // Call the method under test
        $result = MoneyBirdContactsToolbox::getContacts();
        
        // Assert the result
        $this->assertCount(2, $result);
        $this->assertEquals('1', $result[0]['id']);
        $this->assertEquals('John', $result[0]['firstname']);
        $this->assertEquals('Doe', $result[0]['lastname']);
        $this->assertEquals('ACME Inc.', $result[0]['company_name']);
    }
    
    /** @test */
    public function it_gets_contact_by_id()
    {
        // Set up mock to return a contact
        $this->contactRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactMock);
            
        // Call the method under test
        $result = MoneyBirdContactsToolbox::getContactById(['id' => '123']);
        
        // Assert the result
        $this->assertSame($this->contactMock, $result);
    }
    
    /** @test */
    public function it_gets_contact_by_customer_id()
    {
        // Set up mock to return a contact
        $this->contactRepoMock->shouldReceive('findByCustomerId')
            ->once()
            ->with('CUST123')
            ->andReturn($this->contactMock);
            
        // Call the method under test
        $result = MoneyBirdContactsToolbox::getContactByCustomerId(['customer_id' => 'CUST123']);
        
        // Assert the result
        $this->assertSame($this->contactMock, $result);
    }
    
    /** @test */
    public function it_creates_a_contact()
    {
        // Contact parameter data
        $newContact = [
            'firstname' => 'Alice',
            'lastname' => 'Wonder',
            'company_name' => 'Wonderland Inc.'
        ];
        
        // Mock the contact entity
        $this->contactRepoMock->shouldReceive('save')
            ->once()
            ->andReturn($this->contactMock);
            
        // Call the method under test
        $result = MoneyBirdContactsToolbox::createContact($newContact);
        
        // Assert property assignments
        $this->assertEquals('Alice', $this->contactRepoMock->firstname);
        $this->assertEquals('Wonder', $this->contactRepoMock->lastname);
        $this->assertEquals('Wonderland Inc.', $this->contactRepoMock->company_name);
    }
    
    /** @test */
    public function it_updates_a_contact()
    {
        // Update data
        $updateData = [
            'id' => '123',
            'firstname' => 'Updated',
            'lastname' => 'Name',
            'email' => 'updated@example.com'
        ];
        
        // Set up mock to find and return a contact
        $this->contactRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactMock);
            
        // Set up save expectation
        $this->contactMock->shouldReceive('save')
            ->once()
            ->andReturn($this->contactMock);
            
        // Ensure property_exists returns true for our properties
        $this->contactMock->firstname = null;
        $this->contactMock->lastname = null;
        $this->contactMock->email = null;
        
        // Call the method under test
        $result = MoneyBirdContactsToolbox::updateContact($updateData);
        
        // Assert property assignments
        $this->assertEquals('Updated', $this->contactMock->firstname);
        $this->assertEquals('Name', $this->contactMock->lastname);
        $this->assertEquals('updated@example.com', $this->contactMock->email);
        
        $this->assertSame($this->contactMock, $result);
    }
    
    /** @test */
    public function it_archives_a_contact()
    {
        // Set up mock to find and return a contact
        $this->contactRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactMock);
            
        // Set up archive expectation
        $this->contactMock->shouldReceive('archive')
            ->once();
            
        // Call the method under test
        $result = MoneyBirdContactsToolbox::archiveContact(['id' => '123']);
        
        // Assert the result
        $this->assertTrue($result['success']);
        $this->assertEquals('Contact archived successfully', $result['message']);
    }
    
    /** @test */
    public function it_runs_a_tool_by_name()
    {
        // Mock getContactById to be called with specific parameters
        $this->contactRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactMock);
        
        // Call runTool with the tool name and parameters
        $result = MoneyBirdContactsToolbox::runTool('getContactById', ['id' => '123']);
        
        // The result should be the contact mock
        $this->assertSame($this->contactMock, $result);
    }
} 