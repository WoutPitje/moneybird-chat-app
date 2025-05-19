<?php

namespace Tests\Unit\Helpers\Tools\Moneybird;

use App\Helpers\Moneybird;
use App\Helpers\Tools\Moneybird\MoneybirdContactPeopleToolbox;
use Mockery;
use PHPUnit\Framework\TestCase;
use stdClass;

class MoneybirdContactPeopleToolboxTest extends TestCase
{
    protected $moneybirdMock;
    protected $contactPersonRepoMock;
    protected $contactPersonMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock objects
        $this->moneybirdMock = Mockery::mock('Picqer\Financials\Moneybird\Moneybird');
        $this->contactPersonRepoMock = Mockery::mock(stdClass::class);
        $this->contactPersonMock = Mockery::mock(stdClass::class);

        // Mock the Moneybird helper to return our mock client
        Mockery::mock('alias:' . Moneybird::class)
            ->shouldReceive('getMoneybird')
            ->andReturn($this->moneybirdMock);

        // Set up the contact person repository mock
        $this->moneybirdMock->shouldReceive('contactPerson')
            ->andReturn($this->contactPersonRepoMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_gets_available_tools()
    {
        $tools = MoneybirdContactPeopleToolbox::getTools();
        
        $this->assertIsArray($tools);
        $this->assertNotEmpty($tools);
        $this->assertCount(4, $tools); // There should be 4 tools defined
    }
    
    /** @test */
    public function it_gets_contact_person_by_id()
    {
        // Set up mock to return a contact person
        $this->contactPersonRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactPersonMock);
            
        // Call the method under test
        $result = MoneybirdContactPeopleToolbox::getContactPersonById('123');
        
        // Assert the result
        $this->assertSame($this->contactPersonMock, $result);
    }
    
    /** @test */
    public function it_creates_a_contact_person()
    {
        // Contact person parameter data
        $newContactPerson = [
            'contact_id' => '456',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'phone' => '1234567890',
            'email' => 'john.doe@example.com',
            'department' => 'Sales'
        ];
        
        // Mock the contactPerson method with the proper parameter
        $this->moneybirdMock->shouldReceive('contactPerson')
            ->once()
            ->with($newContactPerson)
            ->andReturn($this->contactPersonMock);
            
        // Call the method under test
        $result = MoneybirdContactPeopleToolbox::createContactPerson($newContactPerson);
        
       
        $this->assertEquals($this->contactPersonMock->id, $result->id);
        $this->assertEquals($this->contactPersonMock->contact_id, $result->contact_id);
        $this->assertEquals($this->contactPersonMock->firstname, $result->firstname);
        $this->assertEquals($this->contactPersonMock->lastname, $result->lastname);
        $this->assertEquals($this->contactPersonMock->phone, $result->phone);
        $this->assertEquals($this->contactPersonMock->email, $result->email);
        $this->assertEquals($this->contactPersonMock->department, $result->department);


    }
    
    /** @test */
    public function it_updates_a_contact_person()
    {
        // Update data with the id
        $updateData = [
            'id' => '123',
            'firstname' => 'Updated',
            'lastname' => 'Name',
            'email' => 'updated@example.com'
        ];
        
        // Set up mock to find and return a contact person
        $this->contactPersonRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactPersonMock);
            
        // Set up property access and save
        $this->contactPersonMock->shouldReceive('save')
            ->once()
            ->andReturn($this->contactPersonMock);
            
        // Set up properties to be modified
        $this->contactPersonMock->firstname = null;
        $this->contactPersonMock->lastname = null;
        $this->contactPersonMock->email = null;
        
        // Call the method under test
        $result = MoneybirdContactPeopleToolbox::updateContactPerson($updateData);
        
        // Set properties that should be modified
        $this->contactPersonMock->firstname = 'Updated';
        $this->contactPersonMock->lastname = 'Name';
        $this->contactPersonMock->email = 'updated@example.com';
        
        // Assert the result
        $this->assertSame($this->contactPersonMock, $result);
    }
    
    /** @test */
    public function it_deletes_a_contact_person()
    {
        // Set up mock to find and return a contact person
        $this->contactPersonRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactPersonMock);
            
        // Set up delete expectation
        $this->contactPersonMock->shouldReceive('delete')
            ->once();
            
        // Call the method under test
        $result = MoneybirdContactPeopleToolbox::deleteContactPerson('123');
        
        // Assert the result
        $this->assertSame($this->contactPersonMock, $result);
    }
    
    /** @test */
    public function it_runs_a_tool_by_name()
    {
        // Set up mock to find and return a contact person
        $this->contactPersonRepoMock->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($this->contactPersonMock);
        
        // Call runTool with the tool name and parameters
        $result = MoneybirdContactPeopleToolbox::runTool('getContactPersonById', '123');
        
        // The result should be the contact person mock
        $this->assertSame($this->contactPersonMock, $result);
    }
} 