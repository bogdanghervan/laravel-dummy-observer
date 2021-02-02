<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;
use BogdanGhervan\DummyObserver;

class DummyObserverTest extends TestCase
{
    /**
     * @dataProvider modelProvider
     * @return void
     */
    public function testSaving($modelStub): void
    {
        $returnValue = (new DummyObserver())->saving($modelStub);
        
        $this->assertFalse($returnValue);
        $this->assertEquals(1, DummyObserver::getCallCount());
        $this->assertCount(1, DummyObserver::getStorage());
    }
    
    /**
     * @return array
     */
    public function modelProvider(): array
    {
        $modelStub = $this->getMockBuilder(Model::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array('getAttribute'))
            ->getMockForAbstractClass();
    
        $modelStub->expects($this->any())
            ->method('getAttribute')
            ->willReturn([
                'departure' => 'Bucharest',
                'destination' => 'New York',
                'price' => 799
            ]);
        
        return [
            $modelStub
        ];
    }
}
