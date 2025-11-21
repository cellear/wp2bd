<?php
/**
 * WP2BD Post Metadata Functions Test Suite
 *
 * Comprehensive unit tests for post metadata functions:
 * - get_post_format()
 * - get_the_date()
 * - get_the_time()
 * - get_the_author()
 * - get_the_author_meta()
 *
 * @package WP2BD
 * @subpackage Tests
 */

// Include the post metadata functions
require_once dirname(__FILE__) . '/../functions/post-metadata.php';
require_once dirname(__FILE__) . '/../classes/WP_Post.php';

/**
 * Test Suite for Post Metadata Functions
 */
class PostMetadataTest {

    private $tests_run = 0;
    private $tests_passed = 0;
    private $tests_failed = 0;

    /**
     * Run all tests
     */
    public function run() {
        echo "\n=== WP2BD Post Metadata Functions Test Suite ===\n\n";

        // get_post_format() tests
        $this->testGetPostFormatDefault();
        $this->testGetPostFormatFromProperty();
        $this->testGetPostFormatNoPost();
        $this->testGetPostFormatNullParameter();
        $this->testGetPostFormatInvalidPost();

        // get_the_date() tests
        $this->testGetTheDateBasic();
        $this->testGetTheDateCustomFormat();
        $this->testGetTheDateFromBackdropNode();
        $this->testGetTheDateNoPost();
        $this->testGetTheDateInvalidPost();
        $this->testGetTheDateEmptyDate();
        $this->testGetTheDateVariousFormats();

        // get_the_time() tests
        $this->testGetTheTimeBasic();
        $this->testGetTheTimeCustomFormat();
        $this->testGetTheTimeFromBackdropNode();
        $this->testGetTheTimeNoPost();
        $this->testGetTheTimeInvalidPost();
        $this->testGetTheTimeEmptyTime();
        $this->testGetTheTimeVariousFormats();

        // get_the_author() tests
        $this->testGetTheAuthorBasic();
        $this->testGetTheAuthorNoPost();
        $this->testGetTheAuthorNoAuthor();
        $this->testGetTheAuthorBackdropNode();
        $this->testGetTheAuthorWithAuthordata();

        // get_the_author_meta() tests
        $this->testGetTheAuthorMetaDisplayName();
        $this->testGetTheAuthorMetaEmail();
        $this->testGetTheAuthorMetaUserLogin();
        $this->testGetTheAuthorMetaID();
        $this->testGetTheAuthorMetaFirstName();
        $this->testGetTheAuthorMetaLastName();
        $this->testGetTheAuthorMetaDescription();
        $this->testGetTheAuthorMetaURL();
        $this->testGetTheAuthorMetaNickname();
        $this->testGetTheAuthorMetaNoUser();
        $this->testGetTheAuthorMetaInvalidUser();
        $this->testGetTheAuthorMetaDefaultField();
        $this->testGetTheAuthorMetaCustomField();
        $this->testGetTheAuthorMetaBackdropFields();

        // Helper function tests
        $this->testExtractFieldValueString();
        $this->testExtractFieldValueArray();
        $this->testSanitizeFormatSlug();

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

    /* ===== get_post_format() Tests ===== */

    public function testGetPostFormatDefault() {
        echo "Test 1: get_post_format() returns 'standard' by default\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 1;

        $result = get_post_format();
        $this->assert($result === 'standard', 'Default format is "standard"');

        echo "\n";
    }

    public function testGetPostFormatFromProperty() {
        echo "Test 2: get_post_format() returns format from post property\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 2;
        $wp_post->post_format = 'aside';

        $result = get_post_format();
        $this->assert($result === 'aside', 'Format is "aside" from property');

        echo "\n";
    }

    public function testGetPostFormatNoPost() {
        echo "Test 3: get_post_format() returns false with no post\n";

        global $wp_post;
        $wp_post = null;

        $result = get_post_format();
        $this->assert($result === false, 'Returns false when no post');

        echo "\n";
    }

    public function testGetPostFormatNullParameter() {
        echo "Test 4: get_post_format(null) uses global post\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 3;
        $wp_post->post_format = 'gallery';

        $result = get_post_format(null);
        $this->assert($result === 'gallery', 'Uses global post with null parameter');

        echo "\n";
    }

    public function testGetPostFormatInvalidPost() {
        echo "Test 5: get_post_format() with invalid post\n";

        $result = get_post_format('invalid');
        $this->assert($result === false, 'Returns false for invalid post');

        echo "\n";
    }

    /* ===== get_the_date() Tests ===== */

    public function testGetTheDateBasic() {
        echo "Test 6: get_the_date() returns formatted date\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 10;
        $wp_post->post_date = '2024-03-15 14:30:00';

        $result = get_the_date('Y-m-d');
        $this->assert($result === '2024-03-15', 'Returns formatted date Y-m-d');

        echo "\n";
    }

    public function testGetTheDateCustomFormat() {
        echo "Test 7: get_the_date() with custom format\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 11;
        $wp_post->post_date = '2024-03-15 14:30:00';

        $result = get_the_date('F j, Y');
        $this->assert($result === 'March 15, 2024', 'Returns "March 15, 2024"');

        echo "\n";
    }

    public function testGetTheDateFromBackdropNode() {
        echo "Test 8: get_the_date() from Backdrop node with timestamp\n";

        global $wp_post;
        $node = new stdClass();
        $node->nid = 12;
        $node->created = strtotime('2024-01-10 10:00:00');
        $wp_post = $node;

        $result = get_the_date('Y-m-d');
        $this->assert($result === '2024-01-10', 'Converts Backdrop timestamp correctly');

        echo "\n";
    }

    public function testGetTheDateNoPost() {
        echo "Test 9: get_the_date() with no post\n";

        global $wp_post;
        $wp_post = null;

        $result = get_the_date();
        $this->assert($result === '', 'Returns empty string when no post');

        echo "\n";
    }

    public function testGetTheDateInvalidPost() {
        echo "Test 10: get_the_date() with invalid post\n";

        $result = get_the_date('Y-m-d', 'invalid');
        $this->assert($result === '', 'Returns empty string for invalid post');

        echo "\n";
    }

    public function testGetTheDateEmptyDate() {
        echo "Test 11: get_the_date() with empty date\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 13;
        $wp_post->post_date = '';

        $result = get_the_date('Y-m-d');
        $this->assert($result === '', 'Returns empty string for empty date');

        echo "\n";
    }

    public function testGetTheDateVariousFormats() {
        echo "Test 12: get_the_date() with various formats\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 14;
        $wp_post->post_date = '2024-06-20 15:45:30';

        $this->assert(get_the_date('d/m/Y') === '20/06/2024', 'Format d/m/Y works');
        $this->assert(get_the_date('l') === 'Thursday', 'Format l (day name) works');
        $this->assert(get_the_date('M j, Y') === 'Jun 20, 2024', 'Format M j, Y works');

        echo "\n";
    }

    /* ===== get_the_time() Tests ===== */

    public function testGetTheTimeBasic() {
        echo "Test 13: get_the_time() returns formatted time\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 20;
        $wp_post->post_date = '2024-03-15 14:30:00';

        $result = get_the_time('H:i:s');
        $this->assert($result === '14:30:00', 'Returns formatted time H:i:s');

        echo "\n";
    }

    public function testGetTheTimeCustomFormat() {
        echo "Test 14: get_the_time() with custom format\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 21;
        $wp_post->post_date = '2024-03-15 14:30:00';

        $result = get_the_time('g:i a');
        $this->assert($result === '2:30 pm', 'Returns "2:30 pm"');

        echo "\n";
    }

    public function testGetTheTimeFromBackdropNode() {
        echo "Test 15: get_the_time() from Backdrop node\n";

        global $wp_post;
        $node = new stdClass();
        $node->nid = 22;
        $node->created = strtotime('2024-01-10 09:15:30');
        $wp_post = $node;

        $result = get_the_time('H:i');
        $this->assert($result === '09:15', 'Converts Backdrop timestamp correctly');

        echo "\n";
    }

    public function testGetTheTimeNoPost() {
        echo "Test 16: get_the_time() with no post\n";

        global $wp_post;
        $wp_post = null;

        $result = get_the_time();
        $this->assert($result === '', 'Returns empty string when no post');

        echo "\n";
    }

    public function testGetTheTimeInvalidPost() {
        echo "Test 17: get_the_time() with invalid post\n";

        $result = get_the_time('H:i', array());
        $this->assert($result === '', 'Returns empty string for invalid post');

        echo "\n";
    }

    public function testGetTheTimeEmptyTime() {
        echo "Test 18: get_the_time() with empty time\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 23;
        $wp_post->post_date = '';

        $result = get_the_time('H:i');
        $this->assert($result === '', 'Returns empty string for empty time');

        echo "\n";
    }

    public function testGetTheTimeVariousFormats() {
        echo "Test 19: get_the_time() with various formats\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 24;
        $wp_post->post_date = '2024-06-20 15:45:30';

        $this->assert(get_the_time('H:i') === '15:45', 'Format H:i works');
        $this->assert(get_the_time('g:i A') === '3:45 PM', 'Format g:i A works');
        $this->assert(get_the_time('h:i:s') === '03:45:30', 'Format h:i:s works');

        echo "\n";
    }

    /* ===== get_the_author() Tests ===== */

    public function testGetTheAuthorBasic() {
        echo "Test 20: get_the_author() returns author name\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 30;
        $wp_post->post_author = 5;

        // Mock user_load to return a test user
        if (!function_exists('user_load')) {
            function user_load($uid) {
                $user = new stdClass();
                $user->uid = $uid;
                $user->name = 'testuser';
                return $user;
            }
        }

        $result = get_the_author();
        $this->assert($result === 'testuser', 'Returns author username');

        echo "\n";
    }

    public function testGetTheAuthorNoPost() {
        echo "Test 21: get_the_author() with no post\n";

        global $wp_post;
        $wp_post = null;

        $result = get_the_author();
        $this->assert($result === '', 'Returns empty string when no post');

        echo "\n";
    }

    public function testGetTheAuthorNoAuthor() {
        echo "Test 22: get_the_author() with no author\n";

        global $wp_post;
        $wp_post = new WP_Post();
        $wp_post->ID = 31;
        $wp_post->post_author = 0;

        $result = get_the_author();
        $this->assert($result === '', 'Returns empty string for no author');

        echo "\n";
    }

    public function testGetTheAuthorBackdropNode() {
        echo "Test 23: get_the_author() from Backdrop node\n";

        global $wp_post;
        $node = new stdClass();
        $node->nid = 32;
        $node->uid = 5;
        $wp_post = $node;

        $result = get_the_author();
        $this->assert($result === 'testuser', 'Gets author from Backdrop uid');

        echo "\n";
    }

    public function testGetTheAuthorWithAuthordata() {
        echo "Test 24: get_the_author() uses global authordata\n";

        global $wp_post, $authordata;
        $wp_post = new WP_Post();
        $wp_post->ID = 33;
        $wp_post->post_author = 5;

        $authordata = new stdClass();
        $authordata->ID = 5;
        $authordata->display_name = 'Test Author';

        $result = get_the_author();
        $this->assert($result === 'Test Author', 'Uses display_name from authordata');

        echo "\n";
    }

    /* ===== get_the_author_meta() Tests ===== */

    public function testGetTheAuthorMetaDisplayName() {
        echo "Test 25: get_the_author_meta('display_name')\n";

        $result = get_the_author_meta('display_name', 5);
        $this->assert($result === 'testuser', 'Returns display name');

        echo "\n";
    }

    public function testGetTheAuthorMetaEmail() {
        echo "Test 26: get_the_author_meta('user_email')\n";

        // Need to extend user_load mock for this test
        $result = get_the_author_meta('user_email', 5);
        $this->assert($result === '', 'Returns email (empty in mock)');

        echo "\n";
    }

    public function testGetTheAuthorMetaUserLogin() {
        echo "Test 27: get_the_author_meta('user_login')\n";

        $result = get_the_author_meta('user_login', 5);
        $this->assert($result === 'testuser', 'Returns user login');

        echo "\n";
    }

    public function testGetTheAuthorMetaID() {
        echo "Test 28: get_the_author_meta('ID')\n";

        $result = get_the_author_meta('ID', 5);
        $this->assert($result === '5', 'Returns user ID as string');

        echo "\n";
    }

    public function testGetTheAuthorMetaFirstName() {
        echo "Test 29: get_the_author_meta('first_name')\n";

        $result = get_the_author_meta('first_name', 5);
        $this->assert($result === '', 'Returns first name (empty in mock)');

        echo "\n";
    }

    public function testGetTheAuthorMetaLastName() {
        echo "Test 30: get_the_author_meta('last_name')\n";

        $result = get_the_author_meta('last_name', 5);
        $this->assert($result === '', 'Returns last name (empty in mock)');

        echo "\n";
    }

    public function testGetTheAuthorMetaDescription() {
        echo "Test 31: get_the_author_meta('description')\n";

        $result = get_the_author_meta('description', 5);
        $this->assert($result === '', 'Returns description (empty in mock)');

        echo "\n";
    }

    public function testGetTheAuthorMetaURL() {
        echo "Test 32: get_the_author_meta('user_url')\n";

        $result = get_the_author_meta('user_url', 5);
        $this->assert($result === '', 'Returns URL (empty in mock)');

        echo "\n";
    }

    public function testGetTheAuthorMetaNickname() {
        echo "Test 33: get_the_author_meta('nickname')\n";

        $result = get_the_author_meta('nickname', 5);
        $this->assert($result === 'testuser', 'Returns nickname (fallback to username)');

        echo "\n";
    }

    public function testGetTheAuthorMetaNoUser() {
        echo "Test 34: get_the_author_meta() with no user\n";

        global $wp_post, $authordata;
        $wp_post = null;
        $authordata = null;

        $result = get_the_author_meta('display_name');
        $this->assert($result === '', 'Returns empty string when no user');

        echo "\n";
    }

    public function testGetTheAuthorMetaInvalidUser() {
        echo "Test 35: get_the_author_meta() with invalid user ID\n";

        $result = get_the_author_meta('display_name', 0);
        $this->assert($result === '', 'Returns empty string for invalid user');

        echo "\n";
    }

    public function testGetTheAuthorMetaDefaultField() {
        echo "Test 36: get_the_author_meta() with empty field defaults to display_name\n";

        $result = get_the_author_meta('', 5);
        $this->assert($result === 'testuser', 'Defaults to display_name');

        echo "\n";
    }

    public function testGetTheAuthorMetaCustomField() {
        echo "Test 37: get_the_author_meta() with custom field\n";

        $result = get_the_author_meta('custom_field', 5);
        $this->assert($result === '', 'Returns empty for non-existent field');

        echo "\n";
    }

    public function testGetTheAuthorMetaBackdropFields() {
        echo "Test 38: get_the_author_meta() recognizes Backdrop fields\n";

        // The mock user_load already returns Backdrop-style user with 'name' field
        $result = get_the_author_meta('user_login', 5);
        $this->assert($result === 'testuser', 'Extracts from Backdrop name field');

        echo "\n";
    }

    /* ===== Helper Function Tests ===== */

    public function testExtractFieldValueString() {
        echo "Test 39: _wp2bd_extract_field_value() with string\n";

        $result = _wp2bd_extract_field_value('simple string');
        $this->assert($result === 'simple string', 'Returns string as-is');

        echo "\n";
    }

    public function testExtractFieldValueArray() {
        echo "Test 40: _wp2bd_extract_field_value() with Backdrop field array\n";

        $field = array('und' => array(0 => array('value' => 'extracted value')));
        $result = _wp2bd_extract_field_value($field);
        $this->assert($result === 'extracted value', 'Extracts from Backdrop field structure');

        echo "\n";
    }

    public function testSanitizeFormatSlug() {
        echo "Test 41: sanitize_format_slug() sanitizes format names\n";

        $this->assert(sanitize_format_slug('Aside') === 'aside', 'Converts "Aside" to "aside"');
        $this->assert(sanitize_format_slug('Gallery Post') === 'gallery', 'Converts "Gallery Post" to "gallery"');
        $this->assert(sanitize_format_slug('Invalid Format') === 'standard', 'Returns "standard" for invalid format');

        echo "\n";
    }
}

// Run tests if this file is executed directly
if (php_sapi_name() === 'cli') {
    $test = new PostMetadataTest();
    $success = $test->run();
    exit($success ? 0 : 1);
}
