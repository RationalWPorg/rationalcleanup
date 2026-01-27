<?php
/**
 * Base test case for RationalCleanup tests.
 */

namespace RationalCleanup\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {

    use MockeryPHPUnitIntegration;

    /**
     * @var \RationalCleanup|null
     */
    protected $instance;

    /**
     * Set up test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();

        // Reset the singleton before each test
        $this->resetSingleton();

        // Reset test state
        $this->setUserLoggedIn( false );
        $this->setIsAdmin( false );
    }

    /**
     * Tear down test environment.
     */
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Reset the singleton instance.
     */
    protected function resetSingleton(): void {
        $reflection = new \ReflectionClass( \RationalCleanup::class );
        $instance_property = $reflection->getProperty( 'instance' );
        $instance_property->setAccessible( true );
        $instance_property->setValue( null, null );
    }

    /**
     * Get a fresh instance of RationalCleanup for testing.
     *
     * @param array $options Optional options to override defaults.
     * @return \RationalCleanup
     */
    protected function createInstance( array $options = [] ): \RationalCleanup {
        $this->resetSingleton();
        return \RationalCleanup::get_instance();
    }

    /**
     * Set whether the user is logged in for testing.
     *
     * @param bool $logged_in Whether user is logged in.
     */
    protected function setUserLoggedIn( bool $logged_in ): void {
        global $rationalcleanup_test_state;
        $rationalcleanup_test_state['is_user_logged_in'] = $logged_in;
    }

    /**
     * Set whether we're in admin context for testing.
     *
     * @param bool $is_admin Whether in admin context.
     */
    protected function setIsAdmin( bool $is_admin ): void {
        global $rationalcleanup_test_state;
        $rationalcleanup_test_state['is_admin'] = $is_admin;
    }
}
