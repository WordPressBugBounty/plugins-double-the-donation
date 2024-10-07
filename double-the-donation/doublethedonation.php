<?php
/*
Plugin Name: Double the Donation
Plugin URI: https://doublethedonation.com/
Description: Matching gifts plugin for nonprofits, powered by Double the Donation
Author: Double the Donation
Version: 2.0.0
Requires at least: 3.0
Requires PHP: 5.6.20
Author URI: https://doublethedonation.com/about-us/
*/

global $wp_version;

require_once(ABSPATH . "wp-admin/includes/plugin.php");

function doublethedonation_plugin_setup()
{
// defaults for our options
    add_option('doublethedonation_api_host', 'https://doublethedonation.com');
    add_option('doublethedonation_public_key', '');
    add_option('doublethedonation_cache_version', date('r'));
}

// install our plugin
add_action('plugins_loaded', 'doublethedonation_plugin_setup');

function doublethedonation_get_version()
{
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

function doublethedonation_bust_cache()
{
    update_option('doublethedonation_cache_version', date('r'));
}

function doublethedonation_simple_fetch($url)
{
    global $wp_query;
    $response = wp_remote_get($url, array('timeout' => 60));
    return wp_remote_retrieve_body($response);
}

function doublethedonation_shortcode($attrs)
{
    $current_key = get_option('doublethedonation_public_key');

    if ($current_key != 'null') {

        /* If the api key is present, print the following. */
        /* You'll need create some API validation callback.*/

        wp_enqueue_script("doublethedonation_plugin_js", "https://doublethedonation.com/api/js/ddplugin.js", null, null, true);
        wp_enqueue_style("doublethedonation_plugin_css", "https://doublethedonation.com/api/css/ddplugin.css");

        return '<script>var DDCONF = { API_KEY: "' . $current_key . '" };</script>
                <div id="dd-container"></div>';
    } else {
        return "";
    }
}


function doublethedonation_volunteer_hub_shortcode($attrs)
{
    $current_key = get_option('doublethedonation_public_key');

    if ($current_key != 'null') {

        /* If the api key is present, print the following. */
        /* You'll need create some API validation callback.*/

        wp_enqueue_script("doublethedonation_plugin_js", "https://doublethedonation.com/api/js/ddplugin.js", null, null, true);
        wp_enqueue_style("doublethedonation_plugin_css", "https://doublethedonation.com/api/css/ddplugin.css");

        return '<script>var DDCONF = { API_KEY: "' . $current_key . '", VOLUNTEER_GRANT_SPECIFIC: true };</script>
                <div id="dd-container"></div>';
    } else {
        return "";
    }
}

add_shortcode('doublethedonation', 'doublethedonation_shortcode');
add_shortcode('doublethedonation_volunteer', 'doublethedonation_volunteer_hub_shortcode');


/************************************************
 * Code related to the Admin area of the plugin.
 */

add_action('admin_menu', 'doublethedonation_create_menu_page');
add_action('admin_init', 'register_doublethedonation_settings');

function doublethedonation_create_menu_page()
{
    add_menu_page("Double the Donation Admin", "Double the Donation", "manage_options", "doublethedonation", "display_doublethedonation_settings", "data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNDEuNiAyNDMuODciPjx0aXRsZT5kdGQtc3F1YXJlLWxvZ288L3RpdGxlPjxnIGlkPSJnMjIiPjxwYXRoIGlkPSJwYXRoMjQiIGQ9Ik0xMjMuMDcsMGM1Ni40NSwwLDEwNCwzNy42OCwxMTguNTMsODktMTQuMzUtNDQuNTUtNTYuODEtNzYuODUtMTA2Ljk0LTc2Ljg1LTYxLjkzLDAtMTEyLjE1LDQ5LjI4LTExMi4xNSwxMTBTNzIuNzMsMjMyLjI5LDEzNC42NiwyMzIuMjljNDguMzIsMCw4OS41MS0zMCwxMDUuMjgtNzIuMDdhMTIzLDEyMywwLDAsMS0xMTYuODcsODMuNjVDNTUuMTEsMjQzLjg3LDAsMTg5LjI2LDAsMTIxLjkzUzU1LjExLDAsMTIzLjA3LDAiIHN0eWxlPSJmaWxsOiM4ZThlOGUiLz48L2c+PGcgaWQ9ImcyNiI+PHBhdGggaWQ9InBhdGgyOCIgZD0iTTEwMy4wNiwxMDMuM2E0NC43LDQ0LjcsMCwwLDEsMTcuNSwzLjU1Yy03LTMzLjM2LS4xNi00Ny4wOCwyOS4zOC02Mi43My0yNSwyMy41My0xLjg0LDY5LjA3LTIsMTAzLjkzdjFoMGE0NC44NCw0NC44NCwwLDEsMS00NC44My00NS44IiBzdHlsZT0iZmlsbDojNGNiNTczIi8+PC9nPjxnIGlkPSJnMzAiPjxwYXRoIGlkPSJwYXRoMzIiIGQ9Ik0xOTMuODcsNDcuNGMtMTQuNTQsOC4yMy0xNi43NSwyOC41LTE1LjYsNTIuNy45LDE3LjY3LDQuMSw0MC42NywwLDUzLjg4LTcsMTkuNTQtMjQuMTMsMzIuMzgtNDEuNDQsMzguMTEsMTAuNzQtOC44LDIzLjQ5LTI0LjE1LDIzLjM3LTQ4LjA4LS4xNi0yMS41OC0zLjk1LTQ3LjM1LS40Ny02NC43MywyLjYtMTMuMzYsMTQtMjMuNzQsMzQuMTQtMzEuODgiIHN0eWxlPSJmaWxsOiM4ZThlOGUiLz48L2c+PC9zdmc+", '100.1338');
}

function register_doublethedonation_settings()
{
    register_setting('doublethedonation-settings-group', 'doublethedonation_api_host');
    register_setting('doublethedonation-settings-group', 'doublethedonation_public_key');
}


function doublethedonation_option($value, $label, $selected)
{
    $value = htmlspecialchars($value);
    $label = htmlspecialchars($label);
    $selected = ($selected == $value) ? ' selected ' : NULL;
    echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
}

function display_doublethedonation_settings()
{
    if (isset($_GET["doublethedonation_remove_key"]) && !isset($_GET["settings-updated"])) {
        update_option('doublethedonation_public_key', '');
        update_option('doublethedonation_setup_step', '');
    }
    $current_key = get_option('doublethedonation_public_key');
    $status = "Inactive";
    $activated = false;
    $api_host = get_option('doublethedonation_api_host');
    if ($current_key) {
        $get = wp_remote_get("$api_host/api/v1/check_wordpress_key/$current_key");
        $response_code = wp_remote_retrieve_response_code($get);
        if ($response_code == 200) {
            update_option('doublethedonation_public_key', wp_remote_retrieve_body($get));
            $status = "Activated";
            $activated = true;
        }
    }

    ?>

  <style type="text/css">
    .doublethedonation-admin {
      font-size: 16px;
      line-height: 1.5em;
    }

    .doublethedonation-link {
      float: left;
      margin-right: 50px;
    }

    .doublethedonation-status {
      clear: both;
      width: 100%;
      padding: 10px;
      text-transform: uppercase;
      color: white;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
    }

    p.submit {
      text-align: center;
    }

    .doublethedonation-Inactive {
      background-color: #B3000B;
    }

    .doublethedonation-Activated {
      background-color: #6AC228;
    }

    .doublethedonation-input {
      width: 30%;
      height: 50px;
      font-size: 18px;
    }

    .doublethedonation-url {
      font-size: 18px;
    }

    .doublethedonation-admin .button-primary {
      height: 50px;
      width: 150px;
      font-size: 18px;
      text-align: center;
      margin: auto;
    }

    .helptext {
      margin-top: -10px;
      color: #777;
    }
  </style>
  <div class="doublethedonation-admin">
    <h1>Double the Donation Workplace Giving Program Search Tool</h1>

    <div class="doublethedonation-status doublethedonation-<?php echo $status ?>"><?php echo $status ?></div>

    <form method="post" action="options.php">
        <?php settings_fields('doublethedonation-settings-group'); ?>


        <?php if (isset($_GET["advanced"])) { ?>
          <label>API Host:
            <input class="doublethedonation-input"
                   type="text"
                   name="doublethedonation_api_host"
                   value="<?php echo get_option('doublethedonation_api_host'); ?>"/>
          </label>
        <?php } else { ?>
          <input class="doublethedonation-input"
                 type="hidden"
                 name="doublethedonation_api_host"
                 value="<?php echo get_option('doublethedonation_api_host'); ?>"/>
        <?php } ?>

        <?php if (!$activated) { ?>

          <div style="text-align: center;">

              <?php if ($current_key && !$activated) { ?>

                <h2>Hmm... let's try that again</h2>

                <p>You tried this 360MatchPro Public Key: <b><?php echo $current_key; ?></b></p>
                <p>Unfortunately, it didn't work... did you paste in the right key?</p>

              <?php } ?>

            <h2>Enter your 360MatchPro Public Key:</h2>
            <input class="doublethedonation-input" type="text" name="doublethedonation_public_key"/>

              <?php submit_button("Enter", "primary"); ?>

            <h3>Don't have a 360MatchPro Public Key? <a href="https://doublethedonation.com/pricing/" target="_blank">Sign up for
                Double the Donation</a></h3>
            <h3>Need help? <a href="https://doublethedonation.com/wordpress-matching-gifts-plugin/" target="_blank">Click
                here for instructions.</a></h3>
          </div>

        <?php } else { ?>

          <div class="text-align: center">
            <h3>360MatchPro Public Key: <b><?php echo get_option('doublethedonation_public_key'); ?></b>
              <a href="admin.php?page=doublethedonation&doublethedonation_remove_key=true">(Change)</a></h3></div>

        <?php } ?>

    </form>

      <?php if ($activated) { ?>

        <div style="background: #EEEEEE; padding: 10px;">
          <div>Next steps to embed the <b>matching gift program</b> search tool:</div>
          <ol>
            <li>Copy this shortcode to your clipboard: <b>[doublethedonation]</b></li>
            <li>Navigate to the page or blog post you with the plugin to appear on.</li>
            <li>Paste the shortcode where you want the plugin to appear.</li>
            <li>View the published page to confirm the search tool is appearing as you wish.</li>
          </ol>
        </div>

        <div style="background: #EEEEEE; padding: 10px;">
          <div>Next steps to embed the <b>company-sponsored volunteer program</b> search tool:</div>
          <ol>
            <li>Copy this shortcode to your clipboard: <b>[doublethedonation_volunteer]</b></li>
            <li>Navigate to the page or blog post you with the plugin to appear on.</li>
            <li>Paste the shortcode where you want the plugin to appear.</li>
            <li>View the published page to confirm the search tool is appearing as you wish.</li>
          </ol>
        </div>

        <div>For more detailed instructions and additional configuration settings,
          <a target="_blank"
             href="https://support.doublethedonation.com/knowledge/wordpress-double-the-donation-integration-guide">view our integration guide.</a></div>

      <?php } else { ?>

      <?php } ?>

  </div>
<?php } ?>
