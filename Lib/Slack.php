<?php
declare(strict_types=1);

namespace CakeSlack;

use Cake\Http\Client;
use Cake\Core\Configure;

class Slack
{
    /**
     * @var \Cake\Http\Client
     */
    protected static $_client = null;

    /**
     * @var array
     */
    protected static $_settings = null;

    protected static function _getClient() {
        if (static::$_client === null) {
            static::$_client = new Client();
        }

        return static::$_client;
    }

    public static function settings($key) {
        if (static::$_settings === null) {
            $settings = [
                'channel' => '#general',
                'username' => 'cakephp',
                'icon_emoji' => ':ghost:',
            ];

            $tmp = Configure::read('Slack');
            if (is_array($tmp)) {
                static::$_settings = $tmp + $settings;
            }
        }
        return static::$_settings[$key];
    }

    /**
     * Send message to slack
     *
     * @see https://api.slack.com/docs/message-attachments
     *
     * @param $message string|array
     * @return bool
     */
    public static function send($message, array $settings = []): bool
    {
        $settings = $settings + static::settings('channel');
        $client = static::_getClient();
        if (is_array($message)) {
            $payload = [
                'channel' => $settings['channel'],
                'username' => $settings['username'],
                'icon_emoji' => $settings['icon_emoji'],
                'attachments' => $message,
            ];
        } else {
            $payload = [
                'channel' => $settings['channel'],
                'username' => $settings['username'],
                'icon_emoji' => $settings['icon_emoji'],
                'text' => $message,
            ];
        }

        $token = static::settings('token');
        if (empty($token)) {
            return true;
        }
        $uri = "https://hooks.slack.com/services/{$token}";
        $request = [
            'header' => [
                'Content-Type' => 'application/json',
            ]
        ];

        $response = $client->post($uri, json_encode($payload), $request);
        if ($response->code !== 200 || $response->body !== 'ok') {
            return false;
        }

        return true;
    }
}
