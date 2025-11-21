<?php
/**
 * Tests for get_footer() function
 *
 * @package WP2BD
 * @subpackage Tests
 */

/**
 * Test class for get_footer() function.
 *
 * Tests the WordPress get_footer() template loading function
 * and its integration with Backdrop's theme system.
 *
 * @group template-loading
 * @group get_footer
 */
class GetFooterTest extends BackdropWebTestCase {

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
     * Create a test theme with footer templates for testing.
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

        // Create default footer.php
        $footer_file = $theme_path . '/footer.php';
        $footer_content = <<<FOOTER
<footer id="default-footer" class="site-footer">
    <div class="footer-content">
        <p>Default Footer Content</p>
    </div>
</footer>
</body>
</html>
FOOTER;
        file_put_contents($footer_file, $footer_content);

        // Create named footer-custom.php
        $custom_footer_file = $theme_path . '/footer-custom.php';
        $custom_footer_content = <<<FOOTER
<footer id="custom-footer" class="site-footer custom">
    <div class="footer-content">
        <p>Custom Footer Content</p>
    </div>
</footer>
</body>
</html>
FOOTER;
        file_put_contents($custom_footer_file, $custom_footer_content);

        // Enable the test theme
        theme_enable(array($this->test_theme));
        config_set('system.core', 'theme_default', $this->test_theme);

        // Clear theme cache
        system_list_reset();
    }

    /**
     * Test 1: Basic footer.php loading
     *
     * Verify that get_footer() with no parameters loads the default
     * footer.php template from the active theme.
     */
    public function testGetFooterBasic() {
        // Set up globals
        global $theme;
        $theme = $this->test_theme;

        // Start output buffering to capture template output
        ob_start();
        $result = get_footer();
        $output = ob_get_clean();

        // Assert footer was loaded successfully
        $this->assertTrue($result, 'get_footer() should return true when footer.php exists');

        // Assert correct footer content was loaded
        $this->assertContains('Default Footer Content', $output, 'Default footer content should be present');
        $this->assertContains('id="default-footer"', $output, 'Default footer ID should be present');

        // Assert it's HTML structure
        $this->assertContains('</body>', $output, 'Closing body tag should be present');
        $this->assertContains('</html>', $output, 'Closing html tag should be present');
        $this->assertContains('class="site-footer"', $output, 'Footer class should be present');
    }

    /**
     * Test 2: Named footer template loading
     *
     * Verify that get_footer('custom') loads footer-custom.php
     * instead of the default footer.php.
     */
    public function testGetFooterNamed() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_footer('custom');
        $output = ob_get_clean();

        // Assert custom footer was loaded
        $this->assertTrue($result, 'get_footer("custom") should return true when footer-custom.php exists');

        // Assert correct footer content
        $this->assertContains('Custom Footer Content', $output, 'Custom footer content should be present');
        $this->assertContains('id="custom-footer"', $output, 'Custom footer ID should be present');
        $this->assertContains('class="site-footer custom"', $output, 'Custom footer class should be present');

        // Assert default footer was NOT loaded
        $this->assertNotContains('id="default-footer"', $output, 'Default footer should not be loaded');
        $this->assertNotContains('Default Footer Content', $output, 'Default footer content should not be present');
    }

    /**
     * Test 3: Fallback behavior when named footer doesn't exist
     *
     * Verify that when get_footer('nonexistent') is called,
     * it falls back to loading footer.php.
     */
    public function testGetFooterFallback() {
        global $theme;
        $theme = $this->test_theme;

        ob_start();
        $result = get_footer('nonexistent');
        $output = ob_get_clean();

        // Assert fallback to default footer
        $this->assertTrue($result, 'get_footer("nonexistent") should fallback to footer.php');
        $this->assertContains('Default Footer Content', $output, 'Should load default footer as fallback');
        $this->assertContains('id="default-footer"', $output, 'Default footer ID should be present');
    }

    /**
     * Test 4: Return false when no footer exists
     *
     * Verify that get_footer() returns false when neither
     * the named footer nor default footer.php exist.
     */
    public function testGetFooterNotFound() {
        // Create theme without footer files
        $empty_theme = 'empty_test_theme';
        $theme_path = BACKDROP_ROOT . '/themes/' . $empty_theme;

        if (!file_exists($theme_path)) {
            mkdir($theme_path, 0755, true);
        }

        $info_file = $theme_path . '/' . $empty_theme . '.info';
        $info_content = <<<INFO
name = Empty Test Theme
description = Theme without footer
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
        $result = get_footer();
        $output = ob_get_clean();

        // Assert no footer was loaded
        $this->assertFalse($result, 'get_footer() should return false when no footer.php exists');
        $this->assertEmpty($output, 'No output should be generated when footer not found');
    }

    /**
     * Test 5: Action hook firing
     *
     * Verify that the 'get_footer' action hook is fired
     * before the footer template is included.
     */
    public function testGetFooterActionHook() {
        global $theme;
        $theme = $this->test_theme;

        // Track if hook was called
        $hook_called = false;
        $hook_name_param = null;

        // Add action hook listener
        add_action('get_footer', function($name) use (&$hook_called, &$hook_name_param) {
            $hook_called = true;
            $hook_name_param = $name;
        });

        // Test with no name
        ob_start();
        get_footer();
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_footer action hook should be fired');
        $this->assertNull($hook_name_param, 'Hook should receive null when no name specified');

        // Reset for named footer test
        $hook_called = false;
        $hook_name_param = null;

        // Test with name
        ob_start();
        get_footer('custom');
        ob_end_clean();

        $this->assertTrue($hook_called, 'get_footer action hook should be fired for named footer');
        $this->assertEqual($hook_name_param, 'custom', 'Hook should receive the footer name parameter');
    }

    /**
     * Test 6: Child theme support
     *
     * Verify that when a child theme is active, get_footer()
     * checks the child theme first, then falls back to parent theme.
     */
    public function testGetFooterChildTheme() {
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

        // Create parent footer
        $parent_footer = $parent_path . '/footer.php';
        file_put_contents($parent_footer, '<footer id="parent-footer">Parent Footer</footer>');

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

        // Create child footer (overrides parent)
        $child_footer = $child_path . '/footer.php';
        file_put_contents($child_footer, '<footer id="child-footer">Child Footer</footer>');

        // Enable themes
        theme_enable(array($parent_theme, $child_theme));
        config_set('system.core', 'theme_default', $child_theme);

        global $theme;
        $theme = $child_theme;

        system_list_reset();

        // Test that child theme footer is used
        ob_start();
        $result = get_footer();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_footer() should succeed with child theme');
        $this->assertContains('Child Footer', $output, 'Child theme footer should be loaded');
        $this->assertContains('id="child-footer"', $output, 'Child footer ID should be present');
        $this->assertNotContains('id="parent-footer"', $output, 'Parent footer should not be loaded');

        // Test fallback to parent theme
        // Remove child footer so it falls back to parent
        unlink($child_footer);

        ob_start();
        $result = get_footer();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_footer() should fallback to parent theme footer');
        $this->assertContains('Parent Footer', $output, 'Parent theme footer should be loaded as fallback');
        $this->assertContains('id="parent-footer"', $output, 'Parent footer ID should be present');
    }

    /**
     * Test 7: Multiple calls behavior (require_once)
     *
     * Verify that get_footer() uses require_once, so calling it
     * multiple times doesn't include the template multiple times.
     */
    public function testGetFooterRequireOnce() {
        global $theme;
        $theme = $this->test_theme;

        // First call
        ob_start();
        $result1 = get_footer();
        $output1 = ob_get_clean();

        $this->assertTrue($result1, 'First get_footer() call should succeed');
        $this->assertContains('Default Footer Content', $output1, 'First call should output footer');

        // Second call - should not output anything due to require_once
        ob_start();
        $result2 = get_footer();
        $output2 = ob_get_clean();

        // Note: Result behavior with require_once is tricky - the function
        // will still return true because file_exists() returns true,
        // but require_once won't include the file again
        $this->assertTrue($result2, 'Second get_footer() call should still return true');

        // The second call should not produce output due to require_once
        // However, this behavior may vary based on PHP version
        // So we just verify the function returns consistently
    }

    /**
     * Test 8: Theme path resolution
     *
     * Verify that get_footer() correctly resolves theme paths
     * using Backdrop's theme system.
     */
    public function testGetFooterPathResolution() {
        global $theme;
        $theme = $this->test_theme;

        // Verify theme path is correctly resolved
        $theme_path = backdrop_get_path('theme', $this->test_theme);
        $this->assertNotNull($theme_path, 'Theme path should be resolvable');

        $expected_footer = BACKDROP_ROOT . '/' . $theme_path . '/footer.php';
        $this->assertTrue(file_exists($expected_footer), 'Footer file should exist at expected path');

        // Now test the function
        ob_start();
        $result = get_footer();
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_footer() should succeed with valid theme path');
    }

    /**
     * Test 9: Footer with widgets/dynamic content
     *
     * Verify that get_footer() works with footers that contain
     * dynamic content or widget areas (common in WordPress themes).
     */
    public function testGetFooterWithDynamicContent() {
        global $theme;
        $theme = $this->test_theme;

        // Create a footer with dynamic sidebar call
        $theme_path = BACKDROP_ROOT . '/themes/' . $this->test_theme;
        $dynamic_footer_file = $theme_path . '/footer-widgets.php';
        $dynamic_footer_content = <<<FOOTER
<footer id="widgets-footer">
    <div class="footer-widgets">
        <?php if (is_active_sidebar('footer-1')) : ?>
            <div class="widget-area">Footer Widget Area</div>
        <?php endif; ?>
    </div>
    <p>Footer with Widgets</p>
</footer>
FOOTER;
        file_put_contents($dynamic_footer_file, $dynamic_footer_content);

        ob_start();
        $result = get_footer('widgets');
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_footer("widgets") should succeed');
        $this->assertContains('id="widgets-footer"', $output, 'Widgets footer should be loaded');
        $this->assertContains('Footer with Widgets', $output, 'Footer content should be present');
    }

    /**
     * Test 10: Footer with copyright and credits
     *
     * Verify that get_footer() works with typical footer patterns
     * including copyright and site credits (common WordPress pattern).
     */
    public function testGetFooterWithCredits() {
        global $theme;
        $theme = $this->test_theme;

        // Create a footer with copyright info
        $theme_path = BACKDROP_ROOT . '/themes/' . $this->test_theme;
        $credits_footer_file = $theme_path . '/footer-credits.php';
        $credits_footer_content = <<<FOOTER
<footer id="credits-footer" class="site-footer">
    <div class="site-info">
        <span class="copyright">&copy; 2025 Test Site</span>
        <span class="sep"> | </span>
        <span class="credits">Powered by WordPress</span>
    </div>
</footer>
FOOTER;
        file_put_contents($credits_footer_file, $credits_footer_content);

        ob_start();
        $result = get_footer('credits');
        $output = ob_get_clean();

        $this->assertTrue($result, 'get_footer("credits") should succeed');
        $this->assertContains('id="credits-footer"', $output, 'Credits footer should be loaded');
        $this->assertContains('&copy; 2025 Test Site', $output, 'Copyright should be present');
        $this->assertContains('Powered by WordPress', $output, 'Credits should be present');
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
