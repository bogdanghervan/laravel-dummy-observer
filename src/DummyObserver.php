<?php

namespace BogdanGhervan;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Assert as PHPUnit;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

class DummyObserver
{
    use ArraySubsetAsserts;
    
    /**
     * @var int
     */
    protected static $callCount = 0;
    
    /**
     * Local storage of attributes being saved.
     *
     * @var array
     */
    protected static $storage = [];
    
    /**
     * Intercept Eloquent model save.
     *
     * @param Model $model
     * @return bool
     */
    public function saving(Model $model): bool
    {
        self::$callCount++;
        array_push(self::$storage, $model->getAttributes());
        
        // Prevent save from going through
        return false;
    }
    
    /**
     * @param array $expected
     * @return void
     */
    public static function assertSavedAttributes(array $expected): void
    {
        $actual = array_shift(self::$storage);
        
        self::assertArraySubset($expected, $actual ?: [], true);
    }
    
    /**
     * Assert if a model was saved a number of times.
     *
     * @param int $times
     * @return void
     */
    public static function assertSavedTimes(int $times = 1): void
    {
        PHPUnit::assertTrue(
            ($count = self::$callCount) === $times,
            sprintf("Save was triggered {$count} times instead of {$times} times.")
        );
    }
    
    /**
     * Get how many times save was called.
     * 
     * @return int
     */
    public static function getCallCount(): int
    {
        return self::$callCount;
    }
    
    /**
     * Retrieve internal attribute storage for inspection.
     * 
     * @return array
     */
    public static function getStorage(): array
    {
        return self::$storage;
    }
    
    /**
     * Empty out our attribute storage.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$callCount = 0;
        self::$storage = [];
    }
}
