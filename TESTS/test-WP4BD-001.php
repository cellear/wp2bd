<?php
require_once 'backdrop-1.30/modules/wp_content/wp4bd_debug.inc';
wp4bd_debug_init();
wp4bd_debug_stage_start('Test Stage');
wp4bd_debug_log('Test Stage', 'test_key', 'test_value');
wp4bd_debug_stage_end('Test Stage');
print wp4bd_debug_render();
