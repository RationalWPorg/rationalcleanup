<?php
/**
 * Tests for RationalCleanup security features.
 */

namespace RationalCleanup\Tests\Unit;

use RationalCleanup\Tests\TestCase;

class SecurityTest extends TestCase {

    /**
     * Test block_author_enumeration blocks author query parameter.
     */
    public function test_block_author_enumeration_blocks_author_param(): void {
        $instance = $this->createInstance();

        $result = $instance->block_author_enumeration( 'http://example.com/?author=1', '?author=1' );

        $this->assertFalse( $result );
    }

    /**
     * Test block_author_enumeration allows normal requests.
     */
    public function test_block_author_enumeration_allows_normal_requests(): void {
        $instance = $this->createInstance();

        $redirect = 'http://example.com/about/';
        $result = $instance->block_author_enumeration( $redirect, '/about/' );

        $this->assertEquals( $redirect, $result );
    }

    /**
     * Test block_author_enumeration handles various author formats.
     *
     * @dataProvider authorEnumerationProvider
     */
    public function test_block_author_enumeration_patterns( string $request, bool $should_block ): void {
        $instance = $this->createInstance();

        $redirect = 'http://example.com/';
        $result = $instance->block_author_enumeration( $redirect, $request );

        if ( $should_block ) {
            $this->assertFalse( $result );
        } else {
            $this->assertEquals( $redirect, $result );
        }
    }

    /**
     * Data provider for author enumeration patterns.
     */
    public static function authorEnumerationProvider(): array {
        return [
            'author=1'           => [ '?author=1', true ],
            'author=123'         => [ '?author=123', true ],
            'Author=1 (case)'    => [ '?Author=1', true ],
            'author=admin (non-numeric)' => [ '?author=admin', false ],
            'no author param'    => [ '/some-page/', false ],
            'author in path'     => [ '/author/john/', false ],
        ];
    }

    /**
     * Test block_rest_users_endpoint removes user endpoints for logged out users.
     */
    public function test_block_rest_users_endpoint_for_logged_out(): void {
        $this->setUserLoggedIn( false );

        $instance = $this->createInstance();

        $endpoints = [
            '/wp/v2/posts'                     => [ 'methods' => 'GET' ],
            '/wp/v2/users'                     => [ 'methods' => 'GET' ],
            '/wp/v2/users/(?P<id>[\d]+)'       => [ 'methods' => 'GET' ],
            '/wp/v2/categories'                => [ 'methods' => 'GET' ],
        ];

        $result = $instance->block_rest_users_endpoint( $endpoints );

        $this->assertArrayHasKey( '/wp/v2/posts', $result );
        $this->assertArrayHasKey( '/wp/v2/categories', $result );
        $this->assertArrayNotHasKey( '/wp/v2/users', $result );
        $this->assertArrayNotHasKey( '/wp/v2/users/(?P<id>[\d]+)', $result );
    }

    /**
     * Test block_rest_users_endpoint preserves endpoints for logged in users.
     */
    public function test_block_rest_users_endpoint_for_logged_in(): void {
        $this->setUserLoggedIn( true );

        $instance = $this->createInstance();

        $endpoints = [
            '/wp/v2/posts' => [ 'methods' => 'GET' ],
            '/wp/v2/users' => [ 'methods' => 'GET' ],
            '/wp/v2/users/(?P<id>[\d]+)' => [ 'methods' => 'GET' ],
        ];

        $result = $instance->block_rest_users_endpoint( $endpoints );

        $this->assertArrayHasKey( '/wp/v2/users', $result );
        $this->assertArrayHasKey( '/wp/v2/users/(?P<id>[\d]+)', $result );
    }

    /**
     * Test obfuscate_login_errors returns generic message.
     */
    public function test_obfuscate_login_errors_returns_generic_message(): void {
        $instance = $this->createInstance();

        // Various error messages that WordPress might return
        $errors = [
            '<strong>Error</strong>: Invalid username.',
            '<strong>Error</strong>: Invalid password.',
            '<strong>Error</strong>: Unknown username.',
            'The password you entered for the username admin is incorrect.',
        ];

        foreach ( $errors as $error ) {
            $result = $instance->obfuscate_login_errors( $error );
            $this->assertEquals( 'Invalid username or password.', $result );
        }
    }

    /**
     * Test disable_self_pingbacks removes home URLs from links.
     */
    public function test_disable_self_pingbacks_removes_home_urls(): void {
        $instance = $this->createInstance();

        $links = [
            'http://example.com/my-post/',
            'http://external.com/another-post/',
            'http://example.com/page/',
        ];

        $instance->disable_self_pingbacks( $links );

        // Self-pingbacks should be removed
        $this->assertNotContains( 'http://example.com/my-post/', $links );
        $this->assertNotContains( 'http://example.com/page/', $links );
        // External links should remain
        $this->assertContains( 'http://external.com/another-post/', $links );
    }

    /**
     * Test disable_self_pingbacks preserves external URLs.
     */
    public function test_disable_self_pingbacks_preserves_external(): void {
        $instance = $this->createInstance();

        $links = [
            'http://other-site.com/post/',
            'https://another-domain.org/article/',
        ];

        $instance->disable_self_pingbacks( $links );

        $this->assertCount( 2, $links );
    }

    /**
     * Test disable_rest_api_public returns WP_Error for logged out users.
     */
    public function test_disable_rest_api_public_blocks_logged_out(): void {
        $this->setUserLoggedIn( false );

        $instance = $this->createInstance();

        $result = $instance->disable_rest_api_public( null );

        $this->assertInstanceOf( \WP_Error::class, $result );
        $this->assertEquals( 'rest_not_logged_in', $result->get_error_code() );
    }

    /**
     * Test disable_rest_api_public allows logged in users.
     */
    public function test_disable_rest_api_public_allows_logged_in(): void {
        $this->setUserLoggedIn( true );

        $instance = $this->createInstance();

        $result = $instance->disable_rest_api_public( null );

        $this->assertNull( $result );
    }

    /**
     * Test disable_rest_api_public preserves existing result for logged in users.
     */
    public function test_disable_rest_api_public_preserves_existing_result_when_logged_in(): void {
        $this->setUserLoggedIn( true );

        $instance = $this->createInstance();

        $existing_error = new \WP_Error( 'existing_error', 'Existing error message' );
        $result = $instance->disable_rest_api_public( $existing_error );

        $this->assertInstanceOf( \WP_Error::class, $result );
        $this->assertEquals( 'existing_error', $result->get_error_code() );
    }
}
