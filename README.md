# API Data Integrator
This custom WordPress plugin integrates external data into your site via a REST API endpoint, utilizing built-in caching to optimize performance and minimize redundant API requests.

## About Plugin :bulb:
API Data Integrator is a custom WordPress plugin designed to integrate data from an API endpoint into your website. The fetched data is displayed in a user-friendly table format on the front end using a shortcode.

To optimize performance and prevent redundant API requests, the plugin features a built-in caching mechanism, where an API request is made only once, with the data subsequently retrieved from the cache on each page load. This approach minimizes server requests while ensuring that the cache automatically expires after one hour, allowing users to see fresh data hourly. This functionality ensures a seamless experience for users.

Upon activation, the plugin registers an options page titled 'API Data' in the WordPress Admin panel. Here, data fetched from the API is presented in a structured table format. The options page also includes a refresh button, allowing users to manually refresh the data to view real-time information instead of cached data.

Additionally, test cases were created to verify that the API caching mechanism functions as expected and that the data is displayed correctly in the table format via WordPress CLI. These tests ensure the reliability and accuracy of the plugin's performance.

## How to Use :clipboard:
1. Install and activate the plugin.
2. Upon activation, the plugin adds an 'API Data' options page in the WordPress Admin, displaying API data in a structured table with a refresh button to clear cached data for real-time updates.
3. Display data on the front end using the shortcode `[adi_api_data]`.
