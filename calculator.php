<?php
add_action('wp_enqueue_scripts', function() {
    if (is_singular()) {
        global $post;
        $form_id = 1; //Add your form ID

        $has_form = false;

        if (has_shortcode($post->post_content, 'fluentform') &&
            (strpos($post->post_content, 'id="' . $form_id . '"') !== false || strpos($post->post_content, 'id=' . $form_id) !== false)) {
            $has_form = true;
        }

        if (!$has_form && strpos($post->post_content, 'fluentform_' . $form_id) !== false) {
            $has_form = true;
        }

        if ($has_form) {
            wp_enqueue_script('jquery');

            // Dummy script handle with minimal valid source
            $dummy_js_url = 'data:text/javascript;base64,LyoqIFNlZSBmZWFybGVzcyBzY3JpcHQgdG8gYmUgZW5xdWV1ZWQgcHJvcGVybHkgKi8=';
            wp_register_script('ff-distance-script', $dummy_js_url, ['jquery'], null, true);
            wp_enqueue_script('ff-distance-script');

            // Localize BEFORE the inline script
            wp_localize_script('ff-distance-script', 'fluent_form_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('fluent_form_nonce'),
                'form_id'  => $form_id
            ]);

            $form_selector = "fluentform_{$form_id}";
            $zip_field_id = "ff_{$form_id}_address_zip_"; // make sure this matches the postcode field
            $distance_field_id = "ff_{$form_id}_travel_distance"; //update with the name attribute of the field you want to paste the distance value 

            $script = <<<JS
                jQuery(document).ready(function($) {
                    if ($('#$form_selector').length === 0) return;

                    const \$submitBtn = $('#$form_selector .ff-btn-submit');
                    const \$distanceField = $('#$distance_field_id');
                    \$submitBtn.prop('disabled', true);

                    $('#$zip_field_id').on('blur', function() {
                        const postcode = $(this).val().trim();
                        if (!postcode) {
                            \$submitBtn.prop('disabled', true);
                            return;
                        }

                        $.ajax({
                            url: fluent_form_ajax.ajax_url,
                            method: 'POST',
                            data: {
                                action: 'calculate_distance',
                                postcode: postcode,
                                security: fluent_form_ajax.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    const distanceValue = response.data.distance;
                                    \$distanceField.val(distanceValue);

                                    const inputEvent = new Event('input', { bubbles: true });
                                    const changeEvent = new Event('change', { bubbles: true });
                                    \$distanceField[0].dispatchEvent(inputEvent);
                                    \$distanceField[0].dispatchEvent(changeEvent);

                                    \$submitBtn.prop('disabled', false);
                                } else {
                                    alert('Distance calculation failed: ' + response.data.message);
                                    \$submitBtn.prop('disabled', true);
                                }
                            },
                            error: function() {
                                alert('AJAX error calculating distance');
                                \$submitBtn.prop('disabled', true);
                            }
                        });
                    });
                });
            JS;

            wp_add_inline_script('ff-distance-script', $script);
        }
    }
});

add_action('wp_ajax_calculate_distance', 'handle_calculate_distance');
add_action('wp_ajax_nopriv_calculate_distance', 'handle_calculate_distance');

function handle_calculate_distance() {
    if (!check_ajax_referer('fluent_form_nonce', 'security', false)) {
        error_log('AJAX nonce check failed for fluent form distance calulator');
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $postcode = isset($_POST['postcode']) ? sanitize_text_field($_POST['postcode']) : '';
    $hq_postcode = 'G1EL4'; // Replace with your real HQ postcode

    if (empty($postcode)) {
        error_log('AJAX postcode empty.');
        wp_send_json_error(['message' => 'Postcode is empty']);
    }

    $distance = get_distance_between_postcodes($postcode, $hq_postcode);

    if ($distance === false) {
        error_log('Distance calculation failed for ' . $postcode . ' to ' . $hq_postcode);
        wp_send_json_error(['message' => 'Failed to calculate distance']);
    }

    error_log("Distance calculated: {$distance} km");
    wp_send_json_success(['distance' => $distance]);
}

function get_distance_between_postcodes($origin, $destination) {
    $api_key = 'GOOGLE_API_KEY'; // Replace with your actual key
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=" 
        . urlencode($origin) . "&destinations=" . urlencode($destination) . "&key=" . $api_key;

    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        error_log('Google API request failed: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    error_log('Google API response body: ' . $body);

    $data = json_decode($body, true);

    if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
        $meters = $data['rows'][0]['elements'][0]['distance']['value'];
        return round($meters / 1000, 2);
    }

    error_log('Google API distance value missing in response.');
    return false;
}

