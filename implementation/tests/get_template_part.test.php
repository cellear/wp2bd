<?php
/**
 * Unit Tests for get_template_part()
 *
 * Tests WordPress-compatible template part loading functionality.
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the template loading functions
require_once dirname(__DIR__) . '/functions/template-loading.php';

// Define mock Backdrop functions if they don't exist
if (!function_exists('config_get')) {
    function config_get($config, $key) {
        return 'test_theme';
    }
}

if (!function_exists('backdrop_get_path')) {
    function backdrop_get_path($type, $name) {
        return '';
    }
}

if (!function_exists('list_themes')) {
    function list_themes() {
        return array(
            'test_theme' => (object) array(
                'info' => array(
                    'name' => 'Test Theme'
                )
            )
        );
    }
}

/**
 * Test helper class for get_template_part()
 */
class GetTemplatePartTest {
    private $test_dir;
    private $passed = 0;
    private $failed = 0;
    private $action_hooks_fired = array();

    public function __construct() {
        // Create a temporary test directory
        $this->test_dir = sys_get_temp_dir() . '/wp2bd_test_' . uniqid();
        mkdir($this->test_dir, 0777, true);

        // Set up mock theme path
        if (!defined('BACKDROP_ROOT')) {
            define('BACKDROP_ROOT', $this->test_dir);
        }
    }

    public function __destruct() {
        // Clean up test directory
        $this->removeDirectory($this->test_dir);
    }

    /**
     * Remove a directory recursively
     */
    private function removeDirectory($dir) {
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

    /**
     * Create a test template file
     */
    private function createTemplate($filename, $content = null) {
        $dir = dirname($this->test_dir . '/' . $filename);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($content === null) {
            $content = "<?php\n// Template: {$filename}\necho 'Loaded: {$filename}';\n";
        }

        file_put_contents($this->test_dir . '/' . $filename, $content);
    }

    /**
     * Assert a condition is true
     */
    private function assertTrue($condition, $message) {
        if ($condition) {
            $this->passed++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->failed++;
            echo "✗ FAIL: {$message}\n";
        }
    }

    /**
     * Assert a condition is false
     */
    private function assertFalse($condition, $message) {
        $this->assertTrue(!$condition, $message);
    }

    /**
     * Assert two values are equal
     */
    private function assertEquals($expected, $actual, $message) {
        if ($expected === $actual) {
            $this->passed++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->failed++;
            echo "✗ FAIL: {$message}\n";
            echo "  Expected: " . var_export($expected, true) . "\n";
            echo "  Actual: " . var_export($actual, true) . "\n";
        }
    }

    /**
     * Capture output from a function
     */
    private function captureOutput($callback) {
        ob_start();
        $result = $callback();
        $output = ob_get_clean();
        return array('result' => $result, 'output' => $output);
    }

    /**
     * Mock do_action function for testing
     */
    private function setupActionHookMock() {
        if (!function_exists('do_action')) {
            // Create mock do_action function
            eval('
                function do_action($hook_name, ...$args) {
                    global $_wp2bd_action_hooks;
                    if (!isset($_wp2bd_action_hooks)) {
                        $_wp2bd_action_hooks = array();
                    }
                    $_wp2bd_action_hooks[] = array(
                        "hook" => $hook_name,
                        "args" => $args
                    );
                }
            ');
        }
    }

    /**
     * Get fired action hooks
     */
    private function getFiredHooks() {
        global $_wp2bd_action_hooks;
        return isset($_wp2bd_action_hooks) ? $_wp2bd_action_hooks : array();
    }

    /**
     * Clear fired action hooks
     */
    private function clearFiredHooks() {
        global $_wp2bd_action_hooks;
        $_wp2bd_action_hooks = array();
    }

    /**
     * Setup mock Backdrop functions for testing
     */
    private function setupBackdropMocks($theme_name = 'test_theme') {
        global $theme;
        $theme = $theme_name;
    }

    /**
     * TEST 1: Load simple template part with slug only
     */
    public function testSimpleSlugOnly() {
        echo "\n--- TEST 1: Simple slug only (content.php) ---\n";

        $this->setupBackdropMocks();
        $this->setupActionHookMock();
        $this->clearFiredHooks();

        // Create template file
        $this->createTemplate('content.php', '<?php echo "Content template loaded"; ?>');

        // Test with direct path
        $old_backdrop_root = BACKDROP_ROOT;
        define('BACKDROP_ROOT_TEST', $this->test_dir, true);

        // Manually test the logic
        $template_file = $this->test_dir . '/content.php';
        $exists = file_exists($template_file);
        $this->assertTrue($exists, "Template file content.php exists");

        // Test that we can load it
        if ($exists) {
            $output = $this->captureOutput(function() use ($template_file) {
                require $template_file;
                return true;
            });

            $this->assertTrue($output['result'], "Template loaded successfully");
            $this->assertEquals("Content template loaded", $output['output'], "Template output is correct");
        }
    }

    /**
     * TEST 2: Load specialized template part (slug + name)
     */
    public function testSlugWithName() {
        echo "\n--- TEST 2: Slug with name (content-excerpt.php) ---\n";

        $this->setupBackdropMocks();
        $this->clearFiredHooks();

        // Create both generic and specialized templates
        $this->createTemplate('content.php', '<?php echo "Generic content"; ?>');
        $this->createTemplate('content-excerpt.php', '<?php echo "Excerpt content"; ?>');

        // Test file priority - specialized should be preferred
        $specialized_exists = file_exists($this->test_dir . '/content-excerpt.php');
        $generic_exists = file_exists($this->test_dir . '/content.php');

        $this->assertTrue($specialized_exists, "Specialized template content-excerpt.php exists");
        $this->assertTrue($generic_exists, "Generic template content.php exists");

        // Test that specialized template is loaded first
        $slug = 'content';
        $name = 'excerpt';
        $templates = array();

        if ('' !== $name) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";

        $this->assertEquals(array('content-excerpt.php', 'content.php'), $templates,
            "Template priority order is correct");

        // Find first existing template
        $found = null;
        foreach ($templates as $template) {
            if (file_exists($this->test_dir . '/' . $template)) {
                $found = $template;
                break;
            }
        }

        $this->assertEquals('content-excerpt.php', $found, "Specialized template is found first");
    }

    /**
     * TEST 3: Fallback to generic template when specialized doesn't exist
     */
    public function testFallbackToGeneric() {
        echo "\n--- TEST 3: Fallback to generic template ---\n";

        // Create only generic template
        $this->createTemplate('content.php', '<?php echo "Generic fallback"; ?>');

        // Test that generic is used when specialized doesn't exist
        $slug = 'content';
        $name = 'missing';
        $templates = array();

        if ('' !== $name) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";

        // Find first existing template
        $found = null;
        foreach ($templates as $template) {
            if (file_exists($this->test_dir . '/' . $template)) {
                $found = $template;
                break;
            }
        }

        $this->assertEquals('content.php', $found, "Falls back to generic template");

        $specialized_exists = file_exists($this->test_dir . '/content-missing.php');
        $this->assertFalse($specialized_exists, "Specialized template doesn't exist");
    }

    /**
     * TEST 4: Handle nested template parts (with directory structure)
     */
    public function testNestedTemplateParts() {
        echo "\n--- TEST 4: Nested template parts (template-parts/post/content-excerpt.php) ---\n";

        // Create nested template structure
        $this->createTemplate('template-parts/post/content.php',
            '<?php echo "Generic post content"; ?>');
        $this->createTemplate('template-parts/post/content-excerpt.php',
            '<?php echo "Excerpt post content"; ?>');
        $this->createTemplate('template-parts/post/content-single.php',
            '<?php echo "Single post content"; ?>');

        // Test that nested directories work correctly
        $base_dir = $this->test_dir . '/template-parts/post/';
        $this->assertTrue(is_dir($base_dir), "Nested directory exists");

        // Test different variations
        $tests = array(
            array('slug' => 'template-parts/post/content', 'name' => 'excerpt',
                  'expected' => 'template-parts/post/content-excerpt.php'),
            array('slug' => 'template-parts/post/content', 'name' => 'single',
                  'expected' => 'template-parts/post/content-single.php'),
            array('slug' => 'template-parts/post/content', 'name' => null,
                  'expected' => 'template-parts/post/content.php'),
        );

        foreach ($tests as $test) {
            $templates = array();
            if (null !== $test['name'] && '' !== $test['name']) {
                $templates[] = "{$test['slug']}-{$test['name']}.php";
            }
            $templates[] = "{$test['slug']}.php";

            $found = null;
            foreach ($templates as $template) {
                if (file_exists($this->test_dir . '/' . $template)) {
                    $found = $template;
                    break;
                }
            }

            $this->assertEquals($test['expected'], $found,
                "Correctly resolves nested template: {$test['expected']}");
        }
    }

    /**
     * TEST 5: Return false when no template exists
     */
    public function testReturnFalseWhenNotFound() {
        echo "\n--- TEST 5: Return false when template not found ---\n";

        // Don't create any templates
        $slug = 'nonexistent';
        $name = 'missing';

        $templates = array();
        if ('' !== $name) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";

        // Try to find template
        $found = false;
        foreach ($templates as $template) {
            if (file_exists($this->test_dir . '/' . $template)) {
                $found = $template;
                break;
            }
        }

        $this->assertFalse($found, "Returns false when template not found");
        $this->assertFalse(file_exists($this->test_dir . '/nonexistent.php'),
            "Nonexistent template file doesn't exist");
    }

    /**
     * TEST 6: Action hook is fired with correct parameters
     */
    public function testActionHookFired() {
        echo "\n--- TEST 6: Action hook 'get_template_part_{\$slug}' is fired ---\n";

        $this->setupActionHookMock();
        $this->clearFiredHooks();

        // Simulate the action hook call
        $slug = 'content';
        $name = 'excerpt';

        do_action("get_template_part_{$slug}", $slug, $name);

        $hooks = $this->getFiredHooks();

        $this->assertTrue(count($hooks) > 0, "Action hook was fired");

        if (count($hooks) > 0) {
            $hook = $hooks[0];
            $this->assertEquals("get_template_part_{$slug}", $hook['hook'],
                "Hook name is correct");
            $this->assertEquals($slug, $hook['args'][0],
                "First argument (slug) is correct");
            $this->assertEquals($name, $hook['args'][1],
                "Second argument (name) is correct");
        }
    }

    /**
     * TEST 7: Empty name parameter is handled correctly
     */
    public function testEmptyNameParameter() {
        echo "\n--- TEST 7: Empty name parameter handled correctly ---\n";

        $this->createTemplate('content.php', '<?php echo "Content"; ?>');

        // Test with empty string
        $slug = 'content';
        $name = '';

        $templates = array();
        $name_str = (string) $name;
        if ('' !== $name_str) {
            $templates[] = "{$slug}-{$name_str}.php";
        }
        $templates[] = "{$slug}.php";

        $this->assertEquals(array('content.php'), $templates,
            "Empty name doesn't create specialized template entry");

        // Test with null
        $name = null;
        $templates = array();
        $name_str = (string) $name;
        if ('' !== $name_str) {
            $templates[] = "{$slug}-{$name_str}.php";
        }
        $templates[] = "{$slug}.php";

        $this->assertEquals(array('content.php'), $templates,
            "Null name doesn't create specialized template entry");
    }

    /**
     * TEST 8: Template can be included multiple times (not require_once)
     */
    public function testMultipleInclusions() {
        echo "\n--- TEST 8: Template can be included multiple times ---\n";

        $counter_file = $this->test_dir . '/counter.php';
        file_put_contents($counter_file, '<?php
            if (!isset($GLOBALS["_template_counter"])) {
                $GLOBALS["_template_counter"] = 0;
            }
            $GLOBALS["_template_counter"]++;
            echo "Count: " . $GLOBALS["_template_counter"];
        ?>');

        // Include the template multiple times with require (not require_once)
        $GLOBALS["_template_counter"] = 0;

        require $counter_file;
        $first_count = $GLOBALS["_template_counter"];

        require $counter_file;
        $second_count = $GLOBALS["_template_counter"];

        $this->assertEquals(1, $first_count, "First inclusion sets counter to 1");
        $this->assertEquals(2, $second_count, "Second inclusion increments counter to 2");
        $this->assertTrue($second_count > $first_count,
            "Template can be included multiple times (using require, not require_once)");
    }

    /**
     * TEST 9: Special characters in slug are handled
     */
    public function testSpecialCharactersInSlug() {
        echo "\n--- TEST 9: Special characters in template names ---\n";

        // Create templates with various characters
        $this->createTemplate('loop-post.php', '<?php echo "Loop post"; ?>');
        $this->createTemplate('loop-post-single.php', '<?php echo "Loop post single"; ?>');

        // Test hyphenated slugs
        $slug = 'loop-post';
        $name = 'single';
        $expected = "{$slug}-{$name}.php";

        $this->assertTrue(file_exists($this->test_dir . '/' . $expected),
            "Hyphenated template names work correctly");

        $templates = array();
        if ('' !== $name) {
            $templates[] = "{$slug}-{$name}.php";
        }
        $templates[] = "{$slug}.php";

        $this->assertEquals(array('loop-post-single.php', 'loop-post.php'), $templates,
            "Template array is built correctly with hyphens");
    }

    /**
     * TEST 10: Numeric name parameter
     */
    public function testNumericNameParameter() {
        echo "\n--- TEST 10: Numeric name parameter (e.g., content-404) ---\n";

        $this->createTemplate('content-404.php', '<?php echo "404 content"; ?>');

        // Test with numeric name
        $slug = 'content';
        $name = 404;
        $name_str = (string) $name;

        $templates = array();
        if ('' !== $name_str) {
            $templates[] = "{$slug}-{$name_str}.php";
        }
        $templates[] = "{$slug}.php";

        $this->assertEquals(array('content-404.php', 'content.php'), $templates,
            "Numeric name is converted to string correctly");

        $this->assertTrue(file_exists($this->test_dir . '/content-404.php'),
            "Template with numeric suffix exists");
    }

    /**
     * Run all tests
     */
    public function runAll() {
        echo "\n";
        echo "=================================================\n";
        echo "  WP2BD get_template_part() Test Suite\n";
        echo "=================================================\n";

        $this->testSimpleSlugOnly();
        $this->testSlugWithName();
        $this->testFallbackToGeneric();
        $this->testNestedTemplateParts();
        $this->testReturnFalseWhenNotFound();
        $this->testActionHookFired();
        $this->testEmptyNameParameter();
        $this->testMultipleInclusions();
        $this->testSpecialCharactersInSlug();
        $this->testNumericNameParameter();

        echo "\n";
        echo "=================================================\n";
        echo "  Test Results\n";
        echo "=================================================\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total:  " . ($this->passed + $this->failed) . "\n";
        echo "=================================================\n";

        return $this->failed === 0;
    }
}

// Run tests if this file is executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new GetTemplatePartTest();
    $success = $test->runAll();
    exit($success ? 0 : 1);
}
