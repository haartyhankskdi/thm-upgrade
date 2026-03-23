<?php

namespace Ebizmarts\BrippoPayments\Helper;

use Ebizmarts\BrippoPayments\Exception\WebhookException;
use Exception;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Ebizmarts\BrippoPayments\Helper\Data as DataHelper;
use Magento\Framework\Serialize\Serializer\Json;

class Webhook extends AbstractHelper
{
    public const EVENT_CHECKOUT_SESSION_COMPLETED = "checkout.session.completed";
    public const EVENT_PAYMENT_INTENT_CANCELED = "payment_intent.canceled";

    protected $eventsToListen = [
        self::EVENT_CHECKOUT_SESSION_COMPLETED,
        self::EVENT_PAYMENT_INTENT_CANCELED
    ];

    protected $request;
    protected $response;
    protected $orderFactory;
    protected $eventManager;
    protected $json;
    protected $cache;
    protected $logger;
    protected $dataHelper;

    public function __construct(
        Context $context,
        Logger $logger,
        DataHelper $dataHelper,
        CacheInterface $cache,
        HttpRequest $request,
        HttpResponse $response,
        OrderInterfaceFactory $orderFactory,
        EventManager $eventManager,
        Json $json
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->response = $response;
        $this->orderFactory = $orderFactory;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->cache = $cache;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    public function dispatchEvent()
    {
        try {
            if ($this->request->getMethod() == 'GET') {
                throw new WebhookException("Your webhooks endpoint is accessible!", 200);
            }

            $body = $this->request->getContent();
            $event = $this->json->unserialize($body);

            if (empty($event['type'])) {
                throw new WebhookException(__("Unknown event type"));
            }

            if ($this->isValidEvent($event['type'])) {
                $this->logger->log("Processing " . $event['type'] . ".");
            } else {
                $this->logger->log($event['type'] . " discarded.");
                $this->response->setStatusCode(200);
                return;
            }

            $eventType = "brippo_webhook_" . str_replace(".", "_", $event['type']);

            if ($this->cache->load($event['id'])) {
                throw new WebhookException(__("Event with ID %1 has already been processed.", $event['id']), 202);
            }

            $this->logger->log("Received $eventType");

            $this->response->setStatusCode(500);
            $this->eventManager->dispatch($eventType, [
                'webhookData' => $event
            ]);
            $this->response->setStatusCode(200);

            $this->cache($event);
            $this->logger->log("200 OK");
            // phpcs:disable Magento2.Exceptions.ThrowCatch.ThrowCatch
        } catch (WebhookException $e) {
            // phpcs:enable Magento2.Exceptions.ThrowCatch.ThrowCatch
            if (!empty($e->statusCode)) {
                $this->response->setStatusCode($e->statusCode);
            } else {
                $this->response->setStatusCode(202);
            }

            $statusCode = $this->response->getStatusCode();

            $this->error($e->getMessage(), $statusCode, true);

            if (!empty($e->statusCode) && !empty($event) && ($e->statusCode < 400 || $e->statusCode > 499)) {
                $this->cache($event);
            }
        } catch (Exception $e) {
            $statusCode = 500;
            $this->response->setStatusCode($statusCode);

            $this->logger->log($e->getMessage());
            $this->logger->log($e->getTraceAsString());
            $this->error($e->getMessage(), $statusCode);
        }
    }

    /**
     * @param string $eventType
     * @return bool
     */
    protected function isValidEvent($eventType): bool
    {
        return in_array($eventType, $this->eventsToListen);
    }

    public function lock()
    {
        $wait = 10; // seconds to wait for lock
        $sleep = 2; // poll every X seconds
        do {
            $lock = $this->cache->load("brippo_payments_webhooks_lock");
            if ($lock) {
                // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
                sleep($sleep);
                // phpcs:enable Magento2.Functions.DiscouragedFunction.Discouraged
                $wait -= $sleep;
            }
        } while ($lock && $wait > 0);

        $this->cache->save(1, "brippo_payments_webhooks_lock", [], 60);
    }

    public function unlock()
    {
        $this->cache->remove("brippo_payments_webhooks_lock");
    }

    public function getOrderIncrementIdFromEvent($webhookData)
    {
        if (isset($webhookData['data']['object']['metadata'][Stripe::METADATA_KEY_ORDER_ID])) {
            return $webhookData['data']['object']['metadata'][Stripe::METADATA_KEY_ORDER_ID];
        }
        return null;
    }

    public function getQuoteIdFromEvent($webhookData)
    {
        if (isset($webhookData['data']['object']['metadata'][Stripe::METADATA_KEY_QUOTE_ID])) {
            return $webhookData['data']['object']['metadata'][Stripe::METADATA_KEY_QUOTE_ID];
        }

        return null;
    }

    /**
     * @param $event
     * @return void
     * @throws WebhookException
     */
    public function cache($event)
    {
        if (empty($event['id'])) {
            throw new WebhookException("No event ID specified");
        }

        $this->cache->save("processed", $event['id'], ['brippo_payments_webhooks_events_processed'], 24 * 60 * 60);
    }

    protected function error($msg, $status, $displayError = false)
    {
        if ($status && $status > 0) {
            $this->logger->log("$status $msg");
        } else {
            $this->logger->log("No status: $msg");
        }

        if (!$displayError) {
            $msg = "An error has occurred. Please check var/log/brippo_payments.log for more details.";
        }

        $this->response
            ->setHeader('Content-Type', 'text/plain', true)
            ->setHeader('X-Content-Type-Options', 'nosniff', true)
            ->setContent($msg);
    }
}
