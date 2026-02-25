<?php
require_once(__DIR__ . '/../../vendor/autoload.php');

function run()
{
    
    try {
        $message = [
            'html' => '<h1>Davichos</h1>',
            'text' => '',
            'subject' => 'Felicidades ganador',
            'from_email' => 'contacto@dinsoluciones.com',
            'from_name' => 'Contacto',
            'to' => [
                [
                    'name' => 'pancho villa',
                    'email' => 'gmcrdavid@gmail.com',
                    'type' => 'to'
                ]
            ],
            'headers' => [],
            'important' => true,
            'track_opens' => true,
            'track_clicks' => true,
            'auto_text' => false,
            'auto_html' => true,
            'inline_css' => true,
            'url_strip_qs' => false,
            'preserve_recipients' => true,
            'view_content_link' => false,
            // 'bcc_address' => '',
            // 'tracking_domain' => '',
            // 'signing_domain' => '',
            // 'return_path_domain' => '',
            // 'merge' => false,
            // 'merge_language' => 'mailchimp',
            // 'global_merge_vars' => [],
            // 'merge_vars' => [],
            // 'tags' => [],
            // 'subaccount' => '',
            // 'google_analytics_domains' => [],
            // 'google_analytics_campaign' => '',
            // 'metadata' => ['website' => ''],
            // 'recipient_metadata' => [],
            // 'attachments' => [],
            // 'images' => []

        ];

        $mailchimp = new MailchimpTransactional\ApiClient();
        $mailchimp->setApiKey('elhl7MaQ9LgLA9ow781sDQ');

        $response = $mailchimp->messages->send([
            "message" => $message,
            'async' => false,
            'ip_pool' => '',
            'send_at' => ''
        ]);
        print_r($response);

    } catch (Error $e) {
        echo 'Error: ', $e->getMessage(), "\n";
    }
}
run();