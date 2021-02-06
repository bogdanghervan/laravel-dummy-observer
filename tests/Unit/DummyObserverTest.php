<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use BogdanGhervan\DummyObserver;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Constraint\ExceptionMessage;

/**
 * Class DummyObserverTest
 * @package Tests\Unit
 */
class DummyObserverTest extends TestCase
{
    public function tearDown(): void
    {
        DummyObserver::clear();
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testSaving(Model $modelStub): void
    {
        $returnValue = (new DummyObserver)->saving($modelStub);
        
        $this->assertFalse($returnValue);
        $this->assertEquals(1, DummyObserver::getCallCount());
        $this->assertCount(1, DummyObserver::getStorage());
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedAttributes(Model $modelStub): void
    {
        $attributes = [
            'departure' => 'Bucharest',
            'destination' => 'New York',
            'price' => 799
        ];
        
        // Expect assertion to fail
        try {
            DummyObserver::assertSavedAttributes($attributes);
            $this->fail(); // Or fail this test otherwise
        } catch (ExpectationFailedException $e) {
            $this->assertThat($e, new ExceptionMessage('Failed asserting that an array has the subset'));
        }
    
        $modelStub->expects($this->any())
            ->method('getAttributes')
            ->willReturn($attributes);
        
        // Do some saving and expect assertion to pass
        (new DummyObserver())->saving($modelStub);
        DummyObserver::assertSavedAttributes($attributes);
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedAttributesChecksSavedAttributesInOrder(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->will($this->onConsecutiveCalls([
                'departure' => 'Bucharest',
                'destination' => 'New York'
            ], [
                'price' => 799
            ]));
        
        (new DummyObserver)->saving($modelStub); // Fake-save flight origin and destination
        (new DummyObserver)->saving($modelStub); // Fake-save flight price
    
        DummyObserver::assertSavedAttributes([
            'departure' => 'Bucharest',
            'destination' => 'New York'
        ]);
        
        DummyObserver::assertSavedAttributes([
            'price' => 799
        ]);
    
        // Attempting to assert an old attribute set should fail
        try {
            DummyObserver::assertSavedAttributes([
                'departure' => 'Bucharest',
                'destination' => 'New York'
            ]);
        } catch (ExpectationFailedException $e) {
            $this->assertThat($e, new ExceptionMessage('Failed asserting that an array has the subset'));
        }
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedAttributesEmptiesInternalStorage(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->will($this->onConsecutiveCalls([
                'departure' => 'Bucharest',
                'destination' => 'New York'
            ], [
                'price' => 799
            ]));
        
        (new DummyObserver)->saving($modelStub);
        (new DummyObserver)->saving($modelStub);
        
        DummyObserver::assertSavedAttributes([
            'departure' => 'Bucharest',
            'destination' => 'New York'
        ]);
        DummyObserver::assertSavedAttributes([
            'price' => 799
        ]);
        
        $this->assertEmpty(DummyObserver::getStorage());
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedAttributesVerifiesSubsetOfAttributesSaved(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->willReturn([
                'departure' => 'Bucharest',
                'destination' => 'New York',
                'price' => 799
            ]);
    
        (new DummyObserver)->saving($modelStub);
        
        try {
            DummyObserver::assertSavedAttributes([
                'price' => 799
            ]);
        } catch (ExpectationFailedException $e) {
            $this->fail(sprintf('Assertion failed to verify a subset: %s', $e->getMessage()));
        }
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedTimes(Model $modelStub): void
    {
        try {
            DummyObserver::assertSavedTimes();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertThat($e, new ExceptionMessage('Save was triggered 0 times instead of 1 times.'));
        }
    
        (new DummyObserver())->saving($modelStub);
        DummyObserver::assertSavedTimes();
    
        try {
            DummyObserver::assertSavedTimes(2);
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertThat($e, new ExceptionMessage('Save was triggered 1 times instead of 2 times.'));
        }
    
        (new DummyObserver())->saving($modelStub);
        DummyObserver::assertSavedTimes(2);
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertSavedTimesWhenConsecutiveAttributeAssertionsMade(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->will($this->onConsecutiveCalls([
                'departure' => 'Bucharest',
                'destination' => 'New York'
            ], [
                'price' => 799
            ]));
        
        (new DummyObserver)->saving($modelStub); // Fake-save flight origin and destination
        (new DummyObserver)->saving($modelStub); // Fake-save flight price
        
        DummyObserver::assertSavedAttributes([
            'departure' => 'Bucharest',
            'destination' => 'New York'
        ]);
        
        try {
            DummyObserver::assertSavedTimes(2);
        } catch (ExpectationFailedException $e) {
            $this->fail(sprintf('Assertion failed to corroborate the correct number of saves: %s', $e->getMessage()));
        }
    }
    
    /**
     * @return void
     */
    public function testAssertNothingSaved(): void
    {
        try {
            DummyObserver::assertNothingSaved();
        } catch (ExpectationFailedException $e) {
            $this->fail(sprintf('Assertion failed to corroborate that nothing was saved: %s', $e->getMessage()));
        }
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testAssertNothingSavedWhenModelWasSaved(Model $modelStub): void
    {
        // Do some saving
        (new DummyObserver())->saving($modelStub);
    
        // Validate assertion will fail
        try {
            DummyObserver::assertNothingSaved();
            $this->fail();
        } catch (ExpectationFailedException $e) {
            $this->assertThat($e, new ExceptionMessage('Save was triggered 1 times instead of 0 times.'));
        }
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testGetStorage(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->willReturn(
                ['departure' => 'Bucharest'],
                ['destination' => 'New York'],
                ['price' => 799]
            );
    
        (new DummyObserver)->saving($modelStub);
        (new DummyObserver)->saving($modelStub);
        (new DummyObserver)->saving($modelStub);
        
        $actual = DummyObserver::getStorage();
        
        $this->assertEquals([
            ['departure' => 'Bucharest'],
            ['destination' => 'New York'],
            ['price' => 799]
        ], $actual);
    }
    
    /**
     * @dataProvider modelProvider
     * @param Model $modelStub
     * @return void
     */
    public function testGetCallCountWhenAttributeAssertionsMade(Model $modelStub): void
    {
        $modelStub->method('getAttributes')
            ->willReturn([
                'departure' => 'Bucharest',
                'destination' => 'New York',
                'price' => 799
            ]);
        
        (new DummyObserver)->saving($modelStub);
        
        DummyObserver::assertSavedAttributes([
            'departure' => 'Bucharest',
            'destination' => 'New York',
            'price' => 799
        ]);
        
        // Storage must be empty by now but ensure number of saves
        // would still be correctly reported
        $this->assertEquals(1, DummyObserver::getCallCount());
    }
    
    /**
     * @return array
     */
    public function modelProvider(): array
    {
        $modelStub = $this->getMockBuilder(Model::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array('getAttributes'))
            ->getMockForAbstractClass();
        
        return [
            [$modelStub]
        ];
    }
}
