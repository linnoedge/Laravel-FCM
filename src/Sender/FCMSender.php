<?php

namespace LaravelFCM\Sender;

use GuzzleHttp\Exception\ClientException;
use LaravelFCM\Message\Topics;
use LaravelFCM\Request\Request;
use LaravelFCM\Message\Options;
use LaravelFCM\Message\PayloadData;
use LaravelFCM\Response\Exceptions\ServerResponseException;
use LaravelFCM\Response\GroupResponse;
use LaravelFCM\Response\TopicResponse;
use LaravelFCM\Response\DownstreamResponse;
use LaravelFCM\Message\PayloadNotification;

/**
 * Class FCMSender.
 */
class FCMSender extends HTTPSender
{
    /**
     * send a downstream message to.
     *
     * - a unique device with is registration Token
     * - or to multiples devices with an array of registrationIds
     *
     * @param string|array             $to
     * @param Options|null             $options
     * @param PayloadNotification|null $notification
     * @param PayloadData|null         $data
     *
     * @return DownstreamResponse|null
     */
    public function sendTo($to, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null)
    {
        $response = null;

        if (is_array($to) && !empty($to)) {
            foreach ($to as $token) {
                $request = new Request($token, $options, $notification, $data);

                $responseGuzzle = $this->post($request);

                $responsePartial = new DownstreamResponse($responseGuzzle, $token);
                if (!$response) {
                    $response = $responsePartial;
                } else {
                    $response->merge($responsePartial);
                }
            }
        } else {
            $request = new Request($to, $options, $notification, $data);
            $responseGuzzle = $this->post($request);

            $response = new DownstreamResponse($responseGuzzle, $to);
        }

        return $response;
    }

    /**
     * Send a message to a group of devices identified with them notification key.
     *
     * @param                          $notificationKey
     * @param Options|null             $options
     * @param PayloadNotification|null $notification
     * @param PayloadData|null         $data
     *
     * @return GroupResponse
     */
    public function sendToGroup($notificationKey, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null)
    {
        $request = new Request($notificationKey, $options, $notification, $data);

        $responseGuzzle = $this->post($request);

        return new GroupResponse($responseGuzzle, $notificationKey);
    }

    /**
     * Send message devices registered at a or more topics.
     *
     * @param Topics                   $topics
     * @param Options|null             $options
     * @param PayloadNotification|null $notification
     * @param PayloadData|null         $data
     *
     * @return TopicResponse
     */
    public function sendToTopic(Topics $topics, Options $options = null, PayloadNotification $notification = null, PayloadData $data = null)
    {
        $request = new Request(null, $options, $notification, $data, $topics);

        $responseGuzzle = $this->post($request);
        return new TopicResponse($responseGuzzle, $topics);
    }

    /**
     * @internal
     *
     * @param \LaravelFCM\Request\Request $request
     *
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    protected function post($request)
    {
        try {
            $response = $this->client->request('post',$this->url, $request->build());
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }
}
