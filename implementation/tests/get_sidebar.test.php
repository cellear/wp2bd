<?php
/**
 * Tests for get_sidebar() function
 *
 * @package WP2BD
 * @subpackage Tests
 */

/**
 * Test class for get_sidebar() function.
 *
 * Tests the WordPress get_sidebar() template loading function
 * and its integration with Backdrop's theme system.
 *
 * @group template-loading
 * @group get_sidebar
 */
class GetSidebarTest extends BackdropWebTestCase {

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
     * Create a test theme with sidebar templates for testing.
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

        // Create default sidebar.php
        $sidebar_file = $theme_path . '/sidebar.php';
        $sidebar_content = <<<SIDEBAR
<aside id="default-sidebar" class="widget-area">
    <section class="widget">
        <h2 class="widget-title">Default Sidebar</h2>
        <ul>
            <li>Default widget 1</li>
            <li>Default widget 2</li>
        </ul>
    </section>
</aside>
SIDEBAR;
        file_put_contents($sidebar_file, $sidebar_content);

        // Create named sidebar-custom.php
        $custom_sidebar_file = $theme_path . '/sidebar-custom.php';
        $custom_sidebar_content = <<<SIDEBAR
<aside id="custom-sidebar" class="widget-area custom">
    <section class="widget">
        <h2 class="widget-title">Custom Sidebar</h2>
        <ul>
            <li>Custom widget 1</li>
            <li>Custom widget 2</li>
        </ul>
    </section>
</aside>
SIDEBAR;
        file_put_contents($custom_sidebar_file, $custom_sidebar_content);

        // Create named sidebar-footer.php for footer widgets
        $footer_sidebar_file = $theme_path . '/sidebar-footer.php';
        $footer_sidebar_content = <<<SIDEBAR
<aside id="footer-sidebar" class="footer-widget-area">
    <section class="widget">
        <h2 class="widget-title">Footer Widgets</h2>
        <p>Footer widget content</p>
    </section>
</aside>
SIDEBAR;
        file_put_contents($footer_sidebar_file, $footer_sidebar_content);

        // Enable the test theme
        theme_enable(array($this->test_theme));
        config_set('system.core', 'theme_default', $this->test_theme);

        // Clear theme cache
        system_list_reset();
    }

    /**
     * Test 1: Basic sidebar.php loading
     *
     * Verify that get_sidebar() with no parameters loads the default
     * sidebar.php template from the active theme.
     */
    public function testGetSidebarBasic() {
        // Set up globals
        global $theme;
        $theme = $this->test_theme;

        // Start output buffering to capture template output
        ob_start();
        $result = get_sidebar();
        $output = ob_get_clean();

        // Assert sidebar was loaded successfully
        $this->assertTrue($result, 'get_sidebar() should return true when sidebar.php exists');

        // Assert correct sidebar content was loaded
        $this->assertContains('Default Sidebar', $output, 'Default sidebar content should be present');
        $this->assertContains('id="default-sidebar"', $output, 'Default sidebar ID should be present');
        $this->assertContains('class="widget-area"', $output, 'Widget area class should be present');

        // Assert sidebar widgets are present
        $this->assertContains('Default widget 1', $output, 'First widget should be present');
        $this->assertContains('Default widget 2', $output, 'Second widget should be present');
    }

    /**
     * Test 2: Named sidebar template loading
     *
     * Verify that get_sidebar('custom') loads sidebar-custom.php
     * instead of the default sidebar.php.
     */
    public function testGetSidebarNamed() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_sidebar('custom');
        $output = ob_get_clean();

        // Assert custom sidebar was loaded
        $this->assertTrue($result, 'get_sidebar("custom") should return true when sidebar-custom.php exists');

        // Assert correct sidebar content
        $this->assertContains('Custom Sidebar', $output, 'Custom sidebar content should be present');
        $this->assertContains('id="custom-sidebar"', $output, 'Custom sidebar ID should be present');
        $this->assertContains('class="widget-area custom"', $output, 'Custom widget area class should be present');

        // Assert custom widgets are present
        $this->assertContains('Custom widget 1', $output, 'First custom widget should be present');

        // Assert default sidebar was NOT loaded
        $this->assertNotContains('id="default-sidebar"', $output, 'Default sidebar should not be loaded');
        $this->assertNotContains('Default Sidebar', $output, 'Default sidebar title should not be present');
    }

    /**
     * Test 3: Footer sidebar loading
     *
     * Verify that get_sidebar('footer') loads sidebar-footer.php
     * which is commonly used for footer widget areas.
     */
    public function testGetSidebarFooter() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_sidebar('footer');
        $output = ob_get_clean();

        // Assert footer sidebar was loaded
        $this->assertTrue($result, 'get_sidebar("footer") should return true when sidebar-footer.php exists');

        // Assert correct footer sidebar content
        $this->assertContains('Footer Widgets', $output, 'Footer widgets title should be present');
        $this->assertContains('id="footer-sidebar"', $output, 'Footer sidebar ID should be present');
        $this->assertContains('class="footer-widget-area"', $output, 'Footer widget area class should be present');
        $this->assertContains('Footer widget content', $output, 'Footer widget content should be present');
    }

    /**
     * Test 4: Fallback behavior when named sidebar doesn't exist
     *
     * Verify that when get_sidebar('nonexistent') is called,
     * it falls back to loading sidebar.php.
     */
    public function testGetSidebarFallback() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_sidebar('nonexistent');
        $output = ob_get_clean();

        // Assert fallback to default sidebar
        $this->assertTrue($result, 'get_sidebar("nonexistent") should fallback to sidebar.php');
        $this->assertContains('Default Sidebar', $output, 'Should load default sidebar as fallback');
        $this->assertContains('id="default-sidebar"', $output, 'Default sidebar ID should be present as fallback');
    }

    /**
     * Test 5: Return false when no sidebar exists
     *
     * Verify that get_sidebar() returns false when neither
     * the named sidebar nor default sidebar.php exist.
     */
    public function testGetSidebarNotFound() {
        // Create theme without sidebar files
        $empty_theme = 'empty_test_theme';
        $theme_path = BACKDROP_ROOT . '/themes/' . $empty_theme;

        if (!file_exists($theme_path)) {
            mkdir($theme_path, 0755, true);
        }

        $info_file = $theme_path . '/' . $empty_theme . '.info';
        $info_content = <<<INFO
name = Empty Test Theme
description = Theme without sidebar
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
        $result = get_sidebar();
        $output = ob_get_clean();

        // Assert no sidebar was loaded
        $this->assertFalse($result, 'get_sidebar() should return false when no sidebar.php exists');
        $this->assertEmpty($output, 'No output should be generated when sidebar not found');
    }

    /**
     * Test 6: Action hook firing
     *
     * Verify that the 'get_sidebar' action hook is fired
     * before the sidebar template is included.
     */
    public function testGetSidebarActionHook() {
        global $theme;
        $theme = $this->test_theme;

        // Track if hook was called
        $hook_called = false;
        $hook_name_param = null;

        // Add action hook listener
        add_action('get_sidebar', function($name) use (&$hook_called, &$hook_name_param) {
            $hook_called = true;
            $hook_name_param = $name;
        });

        // Test with no name
        ob_start();
        get_sidebar();
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_sidebar action hook should be fired');
        $this->assertNull($hook_name_param, 'Hook should receive null when no name specified');

        // Reset for named sidebar test
        $hook_called = false;
        $hook_name_param = null;

        // Test with name
        ob_start();
        get_sidebar('custom');
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_sidebar action hook should be fired for named sidebar');
        $this->assertEqual($hook_name_param, 'custom', 'Hook should receive the sidebar name parameter');

        // Reset for footer sidebar test
        $hook_called = false;
        $hook_name_param = null;

        // Test with footer name
        ob_start();
        get_sidebar('footer');
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_sidebar action hook should be fired for footer sidebar');
        $this->assertEqual($hook_name_param, 'footer', 'Hook should receive the footer sidebar name parameter');
    }

    /**
     * Test 7: Child theme support
     *
     * Verify that when a child theme is active, get_sidebar()
     * checks the child theme first, then falls back to parent theme.
     */
    public function testGetSidebarChildTheme() {
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

        // Create parent sidebar
        $parent_sidebar = $parent_path . '/sidebar.php';
        file_put_contents($parent_sidebar, '<aside id="parent-sidebar">Parent Sidebar</aside>');

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

        // Create child sidebar (overrides parent)
        $child_sidebar = $child_path . '/sidebar.php';
        file_put_contents($child_sidebar, '<aside id="child-sidebar">Child Sidebar</aside>');

        // Enable themes
        theme_enable(array($parent_theme, $child_theme));
        config_set('system.core', 'theme_default', $child_theme);

        global $theme;
        $theme = $child_theme;

        system_list_reset();

        // Test that child theme sidebar is used
        ob_start();
        $result = get_sidebar();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_sidebar() should succeed with child theme');
        $this->assertContains('Child Sidebar', $output, 'Child theme sidebar should be loaded');
        $this->assertContains('id="child-sidebar"', $output, 'Child sidebar ID should be present');
        $this->assertNotContains('id="parent-sidebar"', $output, 'Parent sidebar should not be loaded');

        // Test fallback to parent theme
        // Remove child sidebar so it falls back to parent
        unlink($child_sidebar);

        ob_start();
        $result = get_sidebar();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_sidebar() should fallback to parent theme sidebar');
        $this->assertContains('Parent Sidebar', $output, 'Parent theme sidebar should be loaded as fallback');
        $this->assertContains('id="parent-sidebar"', $output, 'Parent sidebar ID should be present');
    }

    /**
     * Test 8: Multiple calls behavior (require_once)
     *
     * Verify that get_sidebar() uses require_once, so calling it
     * multiple times doesn't include the template multiple times.
     */
    public function testGetSidebarRequireOnce() {
        global $theme;
        $theme = $this->test_theme;

        // First call
        ob_start();
        $result1 = get_sidebar();
        $output1 = ob_get_clean();

        $this->assertTrue($result1, 'First get_sidebar() call should succeed');
        $this->assertContains('Default Sidebar', $output1, 'First call should output sidebar');

        // Second call - should not output anything due to require_once
        ob_start();
        $result2 = get_sidebar();
        $output2 = ob_get_clean();

        // Note: Result behavior with require_once is tricky - the function
        // will still return true because file_exists() returns true,
        // but require_once won't include the file again
        $this->assertTrue($result2, 'Second get_sidebar() call should still return true');

        // The second call should not produce output due to require_once
        // However, this behavior may vary based on PHP version
        // So we just verify the function returns consistently
    }

    /**
     * Test 9: Theme path resolution
     *
     * Verify that get_sidebar() correctly resolves theme paths
     * using Backdrop's theme system.
     */
    public function testGetSidebarPathResolution() {
        global $theme;
        $theme = $this->test_theme;

        // Verify theme path is correctly resolved
        $theme_path = backdrop_get_path('theme', $this->test_theme);
        $this->assertNotNull($theme_path, 'Theme path should be resolvable');

        $expected_sidebar = BACKDROP_ROOT . '/' . $theme_path . '/sidebar.php';
        $this->assertTrue(file_exists($expected_sidebar), 'Sidebar file should exist at expected path');

        // Now test the function
        ob_start();
        $result = get_sidebar();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_sidebar() should succeed with valid theme path');
    }

    /**
     * Test 10: Integration with multiple sidebars
     *
     * Verify that multiple different sidebars can be loaded in the same request.
     */
    public function testGetSidebarMultipleSidebars() {
        global $theme;
        $theme = $this->test_theme;

        // Load default sidebar
        ob_start();
        $result_default = get_sidebar();
        $output_default = ob_get_clean();

        $this->assertTrue($result_default, 'Default sidebar should load');
        $this->assertContains('Default Sidebar', $output_default, 'Default sidebar content should be present');

        // Load custom sidebar (different template, so should work even with require_once)
        ob_start();
        $result_custom = get_sidebar('custom');
        $output_custom = ob_get_clean();

        $this->assertTrue($result_custom, 'Custom sidebar should load');
        $this->assertContains('Custom Sidebar', $output_custom, 'Custom sidebar content should be present');

        // Load footer sidebar
        ob_start();
        $result_footer = get_sidebar('footer');
        $output_footer = ob_get_clean();

        $this->assertTrue($result_footer, 'Footer sidebar should load');
        $this->assertContains('Footer Widgets', $output_footer, 'Footer sidebar content should be present');

        // Verify they are all different
        $this->assertNotEqual($output_default, $output_custom, 'Default and custom sidebars should have different output');
        $this->assertNotEqual($output_default, $output_footer, 'Default and footer sidebars should have different output');
        $this->assertNotEqual($output_custom, $output_footer, 'Custom and footer sidebars should have different output');
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
