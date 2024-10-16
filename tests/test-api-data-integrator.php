<?php

# Include Main Plugin File
require_once plugin_dir_path(__FILE__) . '/../api-data-integrator.php';

class Test_APIDataIntegrator extends WP_UnitTestCase {

    private $plugin;

    /**
     * Set up the test environment.
     */
    public function setUp(): void {
        parent::setUp();
        $this->plugin = new APIDataIntegrator();
        delete_transient($this->plugin->cache_key);
    }

    /**
     * Clean up test environment after each test.
     */
    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test API request caching functionality.
     */
    public function test_api_request_cache() {
        $request_count = 0;

        add_filter('pre_http_request', function ($response, $url) use (&$request_count) {
            $request_count++;
            return [
                'body' => $response['body'],
                'response' => ['code' => 200],
            ];
        }, 10, 2);

        $data = $this->plugin->adi_get_data();
        $this->assertEquals(1, $request_count, "API request should be made once.");

        $cached_data = $this->plugin->adi_get_data();
        $this->assertEquals(1, $request_count, "API request is being made and cache mechanism is not working");
    }

    /**
     * Tests that the table shortcode renders corrects data by verifying the API response and its data structure.
     */
    function test_table_shortcode_renders_correctly() {
        $response = wp_remote_get($this->plugin->api_url);
        $this->assertEquals(200, wp_remote_retrieve_response_code($response));

        $data = wp_remote_retrieve_body($response);
        $data_json = json_decode($data);

        $this->assertIsObject($data_json);

        $this->assertObjectHasProperty('title', $data_json);
        $this->assertObjectHasProperty('data', $data_json);

        $this->assertIsObject($data_json->data);

        $this->assertObjectHasProperty('headers', $data_json->data);
        $this->assertObjectHasProperty('rows', $data_json->data);

        $this->assertIsArray($data_json->data->headers);
        $this->assertIsObject($data_json->data->rows);

        foreach ($data_json->data->rows as $row) {
            $this->assertIsObject($row);
            $this->assertObjectHasProperty('id', $row);
            $this->assertObjectHasProperty('fname', $row);
            $this->assertObjectHasProperty('lname', $row);
            $this->assertObjectHasProperty('email', $row);
            $this->assertObjectHasProperty('date', $row);
        }

        $expected_table_header = [
            "ID",
            "First Name",
            "Last Name",
            "Email",
            "Date"
        ];

        $this->assertEquals($expected_table_header, $data_json->data->headers);
    }
}
