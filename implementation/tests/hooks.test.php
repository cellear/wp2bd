<?php
/**
 * WP2BD Hook System Test Suite
 *
 * Comprehensive unit tests for WordPress hook system functions:
 * - add_action() and do_action()
 * - add_filter() and apply_filters()
 * - remove_action() and remove_filter()
 * - wp_head() and wp_footer()
 * - Priority ordering and multiple callbacks
 * - Nested hook calls
 * - accepted_args parameter
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the hook system
require_once dirname(__FILE__) . '/../functions/hooks.php';

/**
 * Test Suite for Hook System
 */
class HookSystemTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    // Test tracking variables
    public $action_fired = false;
    public $action_count = 0;
    public $action_args = array();
    public $filter_called = false;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD Hook System Test Suite ===\n\n";

        // Core action tests
        $this->testAddAction();
        $this->testDoAction();
        $this->testDoActionWithArguments();
        $this->testRemoveAction();
        $this->testActionPriorityOrdering();
        $this->testMultipleActionsAtSamePriority();
        $this->testActionAcceptedArgs();

        // Core filter tests
        $this->testAddFilter();
        $this->testApplyFilters();
        $this->testApplyFiltersWithArguments();
        $this->testRemoveFilter();
        $this->testFilterPriorityOrdering();
        $this->testMultipleFiltersAtSamePriority();
        $this->testFilterAcceptedArgs();

        // Advanced tests
        $this->testNestedDoAction();
        $this->testNestedApplyFilters();
        $this->testHasFilter();
        $this->testHasAction();
        $this->testDidAction();
        $this->testCurrentFilter();
        $this->testDoingFilter();

        // Template hook tests
        $this->testWpHead();
        $this->testWpFooter();

        // Edge cases
        $this->testActionWithNoCallbacks();
        $this->testFilterWithNoCallbacks();
        $this->testInvalidCallback();
        $this->testRemoveNonExistentAction();
        $this->testClosureCallbacks();
        $this->testObjectMethodCallbacks();
        $this->testStaticMethodCallbacks();

        // Summary
        echo "\n=== Test Summary ===\n";
        echo "Tests Run: {$this->tests_run}\n";
        echo "Passed: {$this->tests_passed}\n";
        echo "Failed: {$this->tests_failed}\n";

        return $this->tests_failed === 0;
    }

    /**
     * Assert helper
     */
    private function assert($condition, $message) {
        $this->tests_run++;
        if ($condition) {
            $this->tests_passed++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->tests_failed++;
            echo "✗ FAIL: {$message}\n";
        }
    }

    /**
     * Reset test state
     */
    private function reset() {
        global $wp_filter, $wp_actions, $wp_current_filter;
        $wp_filter = array();
        $wp_actions = array();
        $wp_current_filter = array();
        $this->action_fired = false;
        $this->action_count = 0;
        $this->action_args = array();
        $this->filter_called = false;
    }

    /**
     * Test 1: add_action() registers a callback
     */
    public function testAddAction() {
        echo "Test 1: add_action() registers callback\n";
        $this->reset();

        $result = add_action('test_hook', array($this, 'simpleCallback'));

        global $wp_filter;
        $this->assert($result === true, 'add_action returns true');
        $this->assert(isset($wp_filter['test_hook']), 'Hook is registered in $wp_filter');
        $this->assert(isset($wp_filter['test_hook'][10]), 'Default priority 10 is used');

        echo "\n";
    }

    /**
     * Test 2: do_action() executes registered callback
     */
    public function testDoAction() {
        echo "Test 2: do_action() executes callback\n";
        $this->reset();

        add_action('test_hook', array($this, 'simpleCallback'));
        do_action('test_hook');

        $this->assert($this->action_fired === true, 'Callback was executed');

        echo "\n";
    }

    /**
     * Test 3: do_action() passes arguments to callback
     */
    public function testDoActionWithArguments() {
        echo "Test 3: do_action() with arguments\n";
        $this->reset();

        add_action('test_hook', array($this, 'argumentCallback'), 10, 3);
        do_action('test_hook', 'arg1', 'arg2', 'arg3');

        $this->assert(count($this->action_args) === 3, 'All 3 arguments received');
        $this->assert($this->action_args[0] === 'arg1', 'First argument correct');
        $this->assert($this->action_args[1] === 'arg2', 'Second argument correct');
        $this->assert($this->action_args[2] === 'arg3', 'Third argument correct');

        echo "\n";
    }

    /**
     * Test 4: remove_action() removes a callback
     */
    public function testRemoveAction() {
        echo "Test 4: remove_action() removes callback\n";
        $this->reset();

        add_action('test_hook', array($this, 'simpleCallback'));
        $result = remove_action('test_hook', array($this, 'simpleCallback'));

        global $wp_filter;
        $this->assert($result === true, 'remove_action returns true');
        $this->assert(!isset($wp_filter['test_hook']), 'Hook is removed from $wp_filter');

        // Verify action doesn't fire
        do_action('test_hook');
        $this->assert($this->action_fired === false, 'Callback was not executed after removal');

        echo "\n";
    }

    /**
     * Test 5: Actions execute in priority order
     */
    public function testActionPriorityOrdering() {
        echo "Test 5: Action priority ordering\n";
        $this->reset();

        $order = array();

        add_action('test_hook', function() use (&$order) { $order[] = 'high'; }, 5);
        add_action('test_hook', function() use (&$order) { $order[] = 'medium'; }, 10);
        add_action('test_hook', function() use (&$order) { $order[] = 'low'; }, 20);

        do_action('test_hook');

        $this->assert(count($order) === 3, 'All 3 callbacks executed');
        $this->assert($order[0] === 'high', 'Priority 5 executed first');
        $this->assert($order[1] === 'medium', 'Priority 10 executed second');
        $this->assert($order[2] === 'low', 'Priority 20 executed third');

        echo "\n";
    }

    /**
     * Test 6: Multiple callbacks at same priority execute in order added
     */
    public function testMultipleActionsAtSamePriority() {
        echo "Test 6: Multiple actions at same priority\n";
        $this->reset();

        $order = array();

        add_action('test_hook', function() use (&$order) { $order[] = 'first'; });
        add_action('test_hook', function() use (&$order) { $order[] = 'second'; });
        add_action('test_hook', function() use (&$order) { $order[] = 'third'; });

        do_action('test_hook');

        $this->assert(count($order) === 3, 'All 3 callbacks executed');
        $this->assert($order[0] === 'first', 'First callback executed first');
        $this->assert($order[1] === 'second', 'Second callback executed second');
        $this->assert($order[2] === 'third', 'Third callback executed third');

        echo "\n";
    }

    /**
     * Test 7: accepted_args parameter limits arguments passed
     */
    public function testActionAcceptedArgs() {
        echo "Test 7: Action accepted_args parameter\n";
        $this->reset();

        // Register with accepted_args = 2 (should only receive 2 args)
        add_action('test_hook', array($this, 'argumentCallback'), 10, 2);
        do_action('test_hook', 'arg1', 'arg2', 'arg3', 'arg4');

        $this->assert(count($this->action_args) === 2, 'Only 2 arguments received (accepted_args=2)');
        $this->assert($this->action_args[0] === 'arg1', 'First argument correct');
        $this->assert($this->action_args[1] === 'arg2', 'Second argument correct');

        echo "\n";
    }

    /**
     * Test 8: add_filter() registers a callback
     */
    public function testAddFilter() {
        echo "Test 8: add_filter() registers callback\n";
        $this->reset();

        $result = add_filter('test_filter', array($this, 'simpleFilterCallback'));

        global $wp_filter;
        $this->assert($result === true, 'add_filter returns true');
        $this->assert(isset($wp_filter['test_filter']), 'Filter is registered');
        $this->assert(isset($wp_filter['test_filter'][10]), 'Default priority 10 is used');

        echo "\n";
    }

    /**
     * Test 9: apply_filters() modifies value
     */
    public function testApplyFilters() {
        echo "Test 9: apply_filters() modifies value\n";
        $this->reset();

        add_filter('test_filter', function($value) {
            return strtoupper($value);
        });

        $result = apply_filters('test_filter', 'hello');

        $this->assert($result === 'HELLO', 'Filter modified the value');

        echo "\n";
    }

    /**
     * Test 10: apply_filters() passes additional arguments
     */
    public function testApplyFiltersWithArguments() {
        echo "Test 10: apply_filters() with additional arguments\n";
        $this->reset();

        add_filter('test_filter', function($value, $arg1, $arg2) {
            return $value . '-' . $arg1 . '-' . $arg2;
        }, 10, 3);

        $result = apply_filters('test_filter', 'base', 'extra1', 'extra2');

        $this->assert($result === 'base-extra1-extra2', 'Filter received all arguments');

        echo "\n";
    }

    /**
     * Test 11: remove_filter() removes a callback
     */
    public function testRemoveFilter() {
        echo "Test 11: remove_filter() removes callback\n";
        $this->reset();

        $callback = function($value) { return 'modified'; };
        add_filter('test_filter', $callback);
        $result = remove_filter('test_filter', $callback);

        global $wp_filter;
        $this->assert($result === true, 'remove_filter returns true');
        $this->assert(!isset($wp_filter['test_filter']), 'Filter is removed');

        // Verify filter doesn't apply
        $value = apply_filters('test_filter', 'original');
        $this->assert($value === 'original', 'Value not modified after filter removed');

        echo "\n";
    }

    /**
     * Test 12: Filters execute in priority order
     */
    public function testFilterPriorityOrdering() {
        echo "Test 12: Filter priority ordering\n";
        $this->reset();

        add_filter('test_filter', function($value) { return $value . '-high'; }, 5);
        add_filter('test_filter', function($value) { return $value . '-medium'; }, 10);
        add_filter('test_filter', function($value) { return $value . '-low'; }, 20);

        $result = apply_filters('test_filter', 'start');

        $this->assert($result === 'start-high-medium-low', 'Filters executed in priority order');

        echo "\n";
    }

    /**
     * Test 13: Multiple filters at same priority chain correctly
     */
    public function testMultipleFiltersAtSamePriority() {
        echo "Test 13: Multiple filters at same priority\n";
        $this->reset();

        add_filter('test_filter', function($value) { return $value . '-1'; });
        add_filter('test_filter', function($value) { return $value . '-2'; });
        add_filter('test_filter', function($value) { return $value . '-3'; });

        $result = apply_filters('test_filter', 'start');

        $this->assert($result === 'start-1-2-3', 'Filters chained in order added');

        echo "\n";
    }

    /**
     * Test 14: Filter accepted_args parameter
     */
    public function testFilterAcceptedArgs() {
        echo "Test 14: Filter accepted_args parameter\n";
        $this->reset();

        $received_args = array();

        add_filter('test_filter', function($value, $arg1 = null, $arg2 = null) use (&$received_args) {
            $received_args = array($value, $arg1, $arg2);
            return $value;
        }, 10, 2);

        apply_filters('test_filter', 'val', 'arg1', 'arg2', 'arg3');

        $this->assert(count(array_filter($received_args, function($v) { return $v !== null; })) === 2,
                     'Only 2 arguments received (accepted_args=2)');

        echo "\n";
    }

    /**
     * Test 15: Nested do_action() calls work correctly
     */
    public function testNestedDoAction() {
        echo "Test 15: Nested do_action() calls\n";
        $this->reset();

        $execution_order = array();

        add_action('outer_hook', function() use (&$execution_order) {
            $execution_order[] = 'outer-start';
            do_action('inner_hook');
            $execution_order[] = 'outer-end';
        });

        add_action('inner_hook', function() use (&$execution_order) {
            $execution_order[] = 'inner';
        });

        do_action('outer_hook');

        $this->assert(count($execution_order) === 3, 'All callbacks executed');
        $this->assert($execution_order[0] === 'outer-start', 'Outer hook started');
        $this->assert($execution_order[1] === 'inner', 'Inner hook executed');
        $this->assert($execution_order[2] === 'outer-end', 'Outer hook completed');

        echo "\n";
    }

    /**
     * Test 16: Nested apply_filters() calls work correctly
     */
    public function testNestedApplyFilters() {
        echo "Test 16: Nested apply_filters() calls\n";
        $this->reset();

        add_filter('outer_filter', function($value) {
            $inner_value = apply_filters('inner_filter', 'inner');
            return $value . '-' . $inner_value;
        });

        add_filter('inner_filter', function($value) {
            return $value . '-modified';
        });

        $result = apply_filters('outer_filter', 'outer');

        $this->assert($result === 'outer-inner-modified', 'Nested filters work correctly');

        echo "\n";
    }

    /**
     * Test 17: has_filter() checks if filter exists
     */
    public function testHasFilter() {
        echo "Test 17: has_filter() checks filter existence\n";
        $this->reset();

        $callback = function($value) { return $value; };

        $this->assert(has_filter('test_filter') === false, 'Returns false when no filter exists');

        add_filter('test_filter', $callback, 15);

        $this->assert(has_filter('test_filter') === true, 'Returns true when filter exists');
        $this->assert(has_filter('test_filter', $callback) === 15, 'Returns priority when checking specific callback');

        echo "\n";
    }

    /**
     * Test 18: has_action() checks if action exists
     */
    public function testHasAction() {
        echo "Test 18: has_action() checks action existence\n";
        $this->reset();

        $callback = function() {};

        $this->assert(has_action('test_action') === false, 'Returns false when no action exists');

        add_action('test_action', $callback, 20);

        $this->assert(has_action('test_action') === true, 'Returns true when action exists');
        $this->assert(has_action('test_action', $callback) === 20, 'Returns priority when checking specific callback');

        echo "\n";
    }

    /**
     * Test 19: did_action() counts action executions
     */
    public function testDidAction() {
        echo "Test 19: did_action() counts executions\n";
        $this->reset();

        $this->assert(did_action('test_action') === 0, 'Returns 0 before action fires');

        do_action('test_action');
        $this->assert(did_action('test_action') === 1, 'Returns 1 after first fire');

        do_action('test_action');
        $this->assert(did_action('test_action') === 2, 'Returns 2 after second fire');

        echo "\n";
    }

    /**
     * Test 20: current_filter() returns current filter name
     */
    public function testCurrentFilter() {
        echo "Test 20: current_filter() returns filter name\n";
        $this->reset();

        $current = null;

        add_filter('test_filter', function($value) use (&$current) {
            $current = current_filter();
            return $value;
        });

        apply_filters('test_filter', 'value');

        $this->assert($current === 'test_filter', 'Returns correct filter name during execution');
        $this->assert(current_filter() === false, 'Returns false when no filter is running');

        echo "\n";
    }

    /**
     * Test 21: doing_filter() checks if filter is running
     */
    public function testDoingFilter() {
        echo "Test 21: doing_filter() checks filter status\n";
        $this->reset();

        $is_running = false;

        add_filter('test_filter', function($value) use (&$is_running) {
            $is_running = doing_filter('test_filter');
            return $value;
        });

        $this->assert(doing_filter() === false, 'Returns false before any filter runs');

        apply_filters('test_filter', 'value');

        $this->assert($is_running === true, 'Returns true during filter execution');
        $this->assert(doing_filter() === false, 'Returns false after filter completes');

        echo "\n";
    }

    /**
     * Test 22: wp_head() fires wp_head action
     */
    public function testWpHead() {
        echo "Test 22: wp_head() fires action\n";
        $this->reset();

        $fired = false;

        add_action('wp_head', function() use (&$fired) {
            $fired = true;
        });

        wp_head();

        $this->assert($fired === true, 'wp_head action was fired');
        $this->assert(did_action('wp_head') === 1, 'wp_head fired exactly once');

        echo "\n";
    }

    /**
     * Test 23: wp_footer() fires wp_footer action
     */
    public function testWpFooter() {
        echo "Test 23: wp_footer() fires action\n";
        $this->reset();

        $fired = false;

        add_action('wp_footer', function() use (&$fired) {
            $fired = true;
        });

        wp_footer();

        $this->assert($fired === true, 'wp_footer action was fired');
        $this->assert(did_action('wp_footer') === 1, 'wp_footer fired exactly once');

        echo "\n";
    }

    /**
     * Test 24: do_action with no callbacks does nothing
     */
    public function testActionWithNoCallbacks() {
        echo "Test 24: do_action() with no callbacks\n";
        $this->reset();

        // Should not error
        do_action('nonexistent_hook');

        $this->assert(did_action('nonexistent_hook') === 1, 'Action count incremented even with no callbacks');

        echo "\n";
    }

    /**
     * Test 25: apply_filters with no callbacks returns original value
     */
    public function testFilterWithNoCallbacks() {
        echo "Test 25: apply_filters() with no callbacks\n";
        $this->reset();

        $result = apply_filters('nonexistent_filter', 'original');

        $this->assert($result === 'original', 'Returns original value when no filters registered');

        echo "\n";
    }

    /**
     * Test 26: Invalid callbacks are rejected
     */
    public function testInvalidCallback() {
        echo "Test 26: Invalid callbacks rejected\n";
        $this->reset();

        $result = add_filter('test_filter', 'nonexistent_function_xyz');

        global $wp_filter;
        $this->assert($result === false, 'add_filter returns false for invalid callback');
        $this->assert(!isset($wp_filter['test_filter']), 'Invalid callback not registered');

        echo "\n";
    }

    /**
     * Test 27: Removing non-existent action returns false
     */
    public function testRemoveNonExistentAction() {
        echo "Test 27: Remove non-existent action\n";
        $this->reset();

        $result = remove_action('nonexistent_hook', 'some_callback');

        $this->assert($result === false, 'Returns false when removing non-existent action');

        echo "\n";
    }

    /**
     * Test 28: Closure callbacks work correctly
     */
    public function testClosureCallbacks() {
        echo "Test 28: Closure callbacks\n";
        $this->reset();

        $executed = false;
        $closure = function() use (&$executed) {
            $executed = true;
        };

        add_action('test_hook', $closure);
        do_action('test_hook');

        $this->assert($executed === true, 'Closure callback executed');

        // Test removal
        $this->reset();
        $executed = false;
        add_action('test_hook', $closure);
        remove_action('test_hook', $closure);
        do_action('test_hook');

        $this->assert($executed === false, 'Closure callback removed correctly');

        echo "\n";
    }

    /**
     * Test 29: Object method callbacks work correctly
     */
    public function testObjectMethodCallbacks() {
        echo "Test 29: Object method callbacks\n";
        $this->reset();

        add_action('test_hook', array($this, 'simpleCallback'));
        do_action('test_hook');

        $this->assert($this->action_fired === true, 'Object method callback executed');

        echo "\n";
    }

    /**
     * Test 30: Static method callbacks work correctly
     */
    public function testStaticMethodCallbacks() {
        echo "Test 30: Static method callbacks\n";
        $this->reset();

        add_action('test_hook', array('TestStaticClass', 'staticMethod'));
        do_action('test_hook');

        $this->assert(TestStaticClass::$called === true, 'Static method callback executed');

        echo "\n";
    }

    // Callback methods for testing

    public function simpleCallback() {
        $this->action_fired = true;
        $this->action_count++;
    }

    public function argumentCallback(...$args) {
        $this->action_args = $args;
        $this->action_fired = true;
    }

    public function simpleFilterCallback($value) {
        $this->filter_called = true;
        return $value;
    }
}

/**
 * Test class for static method callbacks
 */
class TestStaticClass {
    public static $called = false;

    public static function staticMethod() {
        self::$called = true;
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new HookSystemTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
