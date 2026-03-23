<?php

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();

// Set area code
$appState = $objectManager->get(\Magento\Framework\App\State::class);
$appState->setAreaCode('frontend');

// Required Magento classes
$subscriberFactory = $objectManager->get(\Magento\Newsletter\Model\SubscriberFactory::class);
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
$customerRepository = $objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);

// CSV path
$csvPath = __DIR__ . '/../var/import/subscribers.csv';
if (!file_exists($csvPath)) { 
    exit("CSV file not found at: $csvPath\n");
}

$handle = fopen($csvPath, 'r');
$header = fgetcsv($handle);
$rowCount = 1;

while (($row = fgetcsv($handle)) !== false) {
    $rowCount++;
    $data = array_combine($header, $row);

    $email = strtolower(trim($data['Email']));
    $status = strtolower(trim($data['Status'] ?? 'subscribed'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Row $rowCount: Invalid email - $email\n";
        continue;
    }

    try {
        $storeId = $storeManager->getStore()->getId();
        $subscriber = $subscriberFactory->create()->loadByEmail($email);
        $isCustomer = false;

        // Check if email belongs to a registered customer
        try {
            $customer = $customerRepository->get($email);
            $subscriber->setCustomerId($customer->getId());
            $isCustomer = true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Guest email — no action needed here
        }

        if (!$subscriber->getId()) {
            // New subscriber
            if ($isCustomer) {
                $subscriber->setSubscriberEmail($email);
                $subscriber->setStoreId($storeId);
                $subscriber->setStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);
                $subscriber->save();
            } else {
                // Guest subscription using correct Magento method
                $subscriber->subscribe($email);
            }
        } else {
            // Already subscribed — update status
            $newStatus = $status === 'unsubscribed'
                ? \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED
                : \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;

            $subscriber->setStatus($newStatus);
            $subscriber->save();
        }

        echo "Row $rowCount: Subscribed - $email (" . ($isCustomer ? 'Customer' : 'Guest') . ")\n";

    } catch (\Exception $e) {
        echo "Row $rowCount: Error with $email - " . $e->getMessage() . "\n";
    }
}

fclose($handle);
echo "Import complete.\n";
