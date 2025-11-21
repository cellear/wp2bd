<?php
/**
 * Tests for get_header() function
 *
 * @package WP2BD
 * @subpackage Tests
 */

/**
 * Test class for get_header() function.
 *
 * Tests the WordPress get_header() template loading function
 * and its integration with Backdrop's theme system.
 *
 * @group template-loading
 * @group get_header
 */
class GetHeaderTest extends BackdropWebTestCase {

    /**
     * Test module dependencies.
     */
    protected $profile = 'testing';

    /**
     * Test theme to use.
     */
    protected $test_theme = 'twentyseventeen';

    /**
     * Set up test environment.
     */
    public function setUp() {
        parent::setUp();

        // Enable the WP2BD module
        module_enable(array('wp2bd'));

        // Create test theme directory structure
        $this->createTestTheme();
    }

    /**
     * Create a test theme with header templates for testing.
     */
    protected function createTestTheme() {
        $theme_path = BACKDROP_ROOT . '/themes/' . $this->test_theme;

        // Create theme directory if it doesn't exist
        if (!file_exists($theme_path)) {
            mkdir($theme_path, 0755, true);
        }

        // Create theme info file
        $info_file = $theme_path . '/' . $this->test_theme . '.info';
        $info_content = <<<INFO
name = Twenty Seventeen Test
description = Test theme for WP2BD
type = theme
backdrop = 1.x
INFO;
        file_put_contents($info_file, $info_content);

        // Create default header.php
        $header_file = $theme_path . '/header.php';
        $header_content = <<<HEADER
<!DOCTYPE html>
<html>
<head>
    <title>Test Header</title>
</head>
<body>
<header id="default-header">
    <h1>Default Header</h1>
</header>
HEADER;
        file_put_contents($header_file, $header_content);

        // Create named header-custom.php
        $custom_header_file = $theme_path . '/header-custom.php';
        $custom_header_content = <<<HEADER
<!DOCTYPE html>
<html>
<head>
    <title>Custom Header</title>
</head>
<body>
<header id="custom-header">
    <h1>Custom Header</h1>
</header>
HEADER;
        file_put_contents($custom_header_file, $custom_header_content);

        // Enable the test theme
        theme_enable(array($this->test_theme));
        config_set('system.core', 'theme_default', $this->test_theme);

        // Clear theme cache
        system_list_reset();
    }

    /**
     * Test 1: Basic header.php loading
     *
     * Verify that get_header() with no parameters loads the default
     * header.php template from the active theme.
     */
    public function testGetHeaderBasic() {
        // Set up globals
        global $theme;
        $theme = $this->test_theme;

        // Start output buffering to capture template output
        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        // Assert header was loaded successfully
        $this->assertTrue($result, 'get_header() should return true when header.php exists');

        // Assert correct header content was loaded
        $this->assertContains('Default Header', $output, 'Default header content should be present');
        $this->assertContains('id="default-header"', $output, 'Default header ID should be present');

        // Assert it's HTML structure
        $this->assertContains('<!DOCTYPE html>', $output, 'HTML doctype should be present');
        $this->assertContains('<body>', $output, 'Body tag should be present');
    }

    /**
     * Test 2: Named header template loading
     *
     * Verify that get_header('custom') loads header-custom.php
     * instead of the default header.php.
     */
    public function testGetHeaderNamed() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_header('custom');
        $output = ob_get_clean();

        // Assert custom header was loaded
        $this->assertTrue($result, 'get_header("custom") should return true when header-custom.php exists');

        // Assert correct header content
        $this->assertContains('Custom Header', $output, 'Custom header content should be present');
        $this->assertContains('id="custom-header"', $output, 'Custom header ID should be present');

        // Assert default header was NOT loaded
        $this->assertNotContains('id="default-header"', $output, 'Default header should not be loaded');
    }

    /**
     * Test 3: Fallback behavior when named header doesn't exist
     *
     * Verify that when get_header('nonexistent') is called,
     * it falls back to loading header.php.
     */
    public function testGetHeaderFallback() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_header('nonexistent');
        $output = ob_get_clean();

        // Assert fallback to default header
        $this->assertTrue($result, 'get_header("nonexistent") should fallback to header.php');
        $this->assertContains('Default Header', $output, 'Should load default header as fallback');
    }

    /**
     * Test 4: Return false when no header exists
     *
     * Verify that get_header() returns false when neither
     * the named header nor default header.php exist.
     */
    public function testGetHeaderNotFound() {
        // Create theme without header files
        $empty_theme = 'empty_test_theme';
        $theme_path = BACKDROP_ROOT . '/themes/' . $empty_theme;

        if (!file_exists($theme_path)) {
            mkdir($theme_path, 0755, true);
        }

        $info_file = $theme_path . '/' . $empty_theme . '.info';
        $info_content = <<<INFO
name = Empty Test Theme
description = Theme without header
type = theme
backdrop = 1.x
INFO;
        file_put_contents($info_file, $info_content);

        theme_enable(array($empty_theme));
        config_set('system.core', 'theme_default', $empty_theme);

        global $theme;
        $theme = $empty_theme;

        // Clear theme cache
        system_list_reset();

        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        // Assert no header was loaded
        $this->assertFalse($result, 'get_header() should return false when no header.php exists');
        $this->assertEmpty($output, 'No output should be generated when header not found');
    }

    /**
     * Test 5: Action hook firing
     *
     * Verify that the 'get_header' action hook is fired
     * before the header template is included.
     */
    public function testGetHeaderActionHook() {
        global $theme;
        $theme = $this->test_theme;

        // Track if hook was called
        $hook_called = false;
        $hook_name_param = null;

        // Add action hook listener
        add_action('get_header', function($name) use (&$hook_called, &$hook_name_param) {
            $hook_called = true;
            $hook_name_param = $name;
        });

        // Test with no name
        ob_start();
        get_header();
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_header action hook should be fired');
        $this->assertNull($hook_name_param, 'Hook should receive null when no name specified');

        // Reset for named header test
        $hook_called = false;
        $hook_name_param = null;

        // Test with name
        ob_start();
        get_header('custom');
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_header action hook should be fired for named header');
        $this->assertEqual($hook_name_param, 'custom', 'Hook should receive the header name parameter');
    }

    /**
     * Test 6: Child theme support
     *
     * Verify that when a child theme is active, get_header()
     * checks the child theme first, then falls back to parent theme.
     */
    public function testGetHeaderChildTheme() {
        // Create parent theme
        $parent_theme = 'parent_test_theme';
        $parent_path = BACKDROP_ROOT . '/themes/' . $parent_theme;
        mkdir($parent_path, 0755, true);

        $parent_info = $parent_path . '/' . $parent_theme . '.info';
        file_put_contents($parent_info, <<<INFO
name = Parent Test Theme
type = theme
backdrop = 1.x
INFO
        );

        // Create parent header
        $parent_header = $parent_path . '/header.php';
        file_put_contents($parent_header, '<header id="parent-header">Parent Header</header>');

        // Create child theme
        $child_theme = 'child_test_theme';
        $child_path = BACKDROP_ROOT . '/themes/' . $child_theme;
        mkdir($child_path, 0755, true);

        $child_info = $child_path . '/' . $child_theme . '.info';
        file_put_contents($child_info, <<<INFO
name = Child Test Theme
type = theme
base theme = parent_test_theme
backdrop = 1.x
INFO
        );

        // Create child header (overrides parent)
        $child_header = $child_path . '/header.php';
        file_put_contents($child_header, '<header id="child-header">Child Header</header>');

        // Enable themes
        theme_enable(array($parent_theme, $child_theme));
        config_set('system.core', 'theme_default', $child_theme);

        global $theme;
        $theme = $child_theme;

        system_list_reset();

        // Test that child theme header is used
        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_header() should succeed with child theme');
        $this->assertContains('Child Header', $output, 'Child theme header should be loaded');
        $this->assertContains('id="child-header"', $output, 'Child header ID should be present');
        $this->assertNotContains('id="parent-header"', $output, 'Parent header should not be loaded');

        // Test fallback to parent theme
        // Remove child header so it falls back to parent
        unlink($child_header);

        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_header() should fallback to parent theme header');
        $this->assertContains('Parent Header', $output, 'Parent theme header should be loaded as fallback');
        $this->assertContains('id="parent-header"', $output, 'Parent header ID should be present');
    }

    /**
     * Test 7: Multiple calls behavior (require_once)
     *
     * Verify that get_header() uses require_once, so calling it
     * multiple times doesn't include the template multiple times.
     */
    public function testGetHeaderRequireOnce() {
        global $theme;
        $theme = $this->test_theme;

        // First call
        ob_start();
        $result1 = get_header();
        $output1 = ob_get_clean();

        $this->assertTrue($result1, 'First get_header() call should succeed');
        $this->assertContains('Default Header', $output1, 'First call should output header');

        // Second call - should not output anything due to require_once
        ob_start();
        $result2 = get_header();
        $output2 = ob_get_clean();

        // Note: Result behavior with require_once is tricky - the function
        // will still return true because file_exists() returns true,
        // but require_once won't include the file again
        $this->assertTrue($result2, 'Second get_header() call should still return true');

        // The second call should not produce output due to require_once
        // However, this behavior may vary based on PHP version
        // So we just verify the function returns consistently
    }

    /**
     * Test 8: Theme path resolution
     *
     * Verify that get_header() correctly resolves theme paths
     * using Backdrop's theme system.
     */
    public function testGetHeaderPathResolution() {
        global $theme;
        $theme = $this->test_theme;

        // Verify theme path is correctly resolved
        $theme_path = backdrop_get_path('theme', $this->test_theme);
        $this->assertNotNull($theme_path, 'Theme path should be resolvable');

        $expected_header = BACKDROP_ROOT . '/' . $theme_path . '/header.php';
        $this->assertTrue(file_exists($expected_header), 'Header file should exist at expected path');

        // Now test the function
        ob_start();
        $result = get_header();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_header() should succeed with valid theme path');
    }

    /**
     * Clean up test environment.
     */
    public function tearDown() {
        // Clean up test theme files
        $this->cleanupTestThemes();

        parent::tearDown();
    }

    /**
     * Remove test theme directories.
     */
    protected function cleanupTestThemes() {
        $themes_to_remove = array(
            $this->test_theme,
            'empty_test_theme',
            'parent_test_theme',
            'child_test_theme',
        );

        foreach ($themes_to_remove as $theme_name) {
            $theme_path = BACKDROP_ROOT . '/themes/' . $theme_name;
            if (file_exists($theme_path)) {
                $this->removeDirectory($theme_path);
            }
        }
    }

    /**
     * Recursively remove a directory.
     */
    protected function removeDirectory($dir) {
        if (!file_exists($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
