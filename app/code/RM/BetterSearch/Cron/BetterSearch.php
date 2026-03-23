<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace RM\BetterSearch\Cron;

/*
    All Use for file manupulation
*/
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Pricing\Helper\Data as Currency;

/*
For Product, blog, config
*/
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreRepository;
use Magento\Catalog\Model\CategoryFactory;

class BetterSearch
{

    protected $logger;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */

    const XML_FOLDER_NAME = "better_search";

    // const XML_SHOW_BLOG = 1;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var StoreRepository
     */
    protected $_storeRepository;

    /**
     * @var Currency
     */
    protected $priceFormater;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    protected $_productRepositoryFactory;

    public function __construct(
        Currency $priceFormater,
        Status $productStatus,
        Visibility $productVisibility,
        Image $imageHelper,
        StoreRepository $storeRepository,
        CollectionFactory $productCollectionFactory,
        File $fileDriver,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        CategoryFactory $categoryFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->priceFormater = $priceFormater;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->imageHelper = $imageHelper;
        $this->_storeRepository = $storeRepository;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->fileDriver = $fileDriver;
        $this->_categoryFactory = $categoryFactory;
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {

        /* Steps:
            1. get List of store
            2. get product collection filter by Store [ addStoreFilter() ]
            3. Check if file exits
            4. Create/Modife js file
            Note:
            Js File eg: default.js
        */

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(__METHOD__);

        try {
            // Get factory create
            $productImg = $this->_productRepositoryFactory->create();

            //get All store
            $stores = $this->_storeRepository->getList();
            foreach ($stores as $store) {
                $storeId = $store["store_id"];
                $storeName = $store["name"];
                $jsFile = $store['code'].'.js';
                // Get Product collection
                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('*');
                $collection->addStoreFilter($storeId);
                $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
                $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

                $fileDataArray = array();

                foreach ($collection as $_product) {
                    // "product_base_image" use for tumbnail image
                    // $image_url = $this->imageHelper->init($_product, 'product_thumbnail_image')->getUrl();
                    $pro_price = !empty($_product->getPrice())?$_product->getPrice():0;
                    // $logger->info("PRO-P = ".$pro_price);
                    $formatPrice = $this->priceFormater->currency(number_format((float)$pro_price,2),true,false);
                    if($productImg->getById($_product->getId())->getData('thumbnail')){
                        $image_url = 'catalog/product'.$productImg->getById($_product->getId())->getData('thumbnail');
                    }else{
                        $image_url = '';
                    }
                    if(!empty($_product->getCategoryIds())){
                        $fileDataArray[] = array(
                            'pn' => $_product->getName() ,
                            'img' => $image_url,
                            'pu' => $_product->getProductUrl(),
                            'cids' => $_product->getCategoryIds(),
                            'cname' => $this->getCatArray($_product->getCategoryIds()),
                            'fp' => $formatPrice,
                        );
                    }else{
                        $fileDataArray[] = array(
                            'pn' => $_product->getName() ,
                            'img' => $image_url,
                            'pu' => $_product->getProductUrl(),
                            'cids' => [],
                            'cname' => [],
                            'fp' => $formatPrice,
                        );
                    }
                }

                // Wipe out last comma
                $fileData = ';var bs_product_search = '.json_encode($fileDataArray);

                // Chech File is there or not.
                $varDirectory = $this->filesystem->getDirectoryWrite(
                    DirectoryList::MEDIA
                );

                $mediaPath = $this->directoryList->getPath('media');
                $filePath = $mediaPath . '/'.self::XML_FOLDER_NAME.'/' . $jsFile;

                if(!$this->checkFileExists($jsFile)){
                    $this->createJsFile($jsFile);
                    // echo $filePath." is created \r\n";
                    $this->writeJsFile($varDirectory, $filePath, $fileData);
                    // echo $storeName." is search ready \r\n";
                    $this->logger->addInfo("Store-Name ".$storeName);
                }else{
                    $this->writeJsFile($varDirectory, $filePath, $fileData);
                    // echo $storeName." is search ready \r\n";
                    $this->logger->addInfo("Store-Name ".$storeName);
                }
            }
        } catch (\Exception $e) {
            $logger->info($e);
        }

        

        $this->logger->addInfo("Cronjob BetterSearch is executed.");
    }

    /**
     * create custom folder and write text file
     *
     * @return bool
     */
    public function createJsFile($fileName = 'demo.txt')
    {
        $varDirectory = $this->filesystem->getDirectoryWrite(
            DirectoryList::MEDIA
        );
        $mediaPath = $this->directoryList->getPath('media');
        $path = $mediaPath . '/'.self::XML_FOLDER_NAME.'/' . $fileName;

        // Write Content
        $this->writeJsFile($varDirectory, $path);
    }

    /**
     * Write content to text file
     *
     * @param WriteInterface $writeDirectory
     * @param $filePath
     * @return bool
     * @throws FileSystemException
     */

    public function writeJsFile(WriteInterface $writeDirectory, string $filePath, $fileData = ';var bs_product_search = [];')
    {
        $stream = $writeDirectory->openFile($filePath, 'w+');
        $stream->lock();
        $stream->write($fileData);
        $stream->unlock();
        $stream->close();

        return true;
    }

    /**
     * @param $catArray
     * @return Array
    */
    public function getCatArray($catArray){
        $catA = array();
        $category = $this->_categoryFactory->create();
        if (is_array($catArray)) {
            for ($i=0; $i < count($catArray); $i++) {
            $collection = $category->load($catArray[$i]);
            $catA[] = $collection->getName();
            }
        }
        return $catA;
    }

    /**
     * Check file is exist or not at specific location
     */
    public function checkFileExists($fileName = 'demo')
    {
        $mediaPath = $this->directoryList->getPath('media');
        $path = $mediaPath . '/'.self::XML_FOLDER_NAME.'/' . $fileName;
        if ($this->fileDriver->isExists($path)) {
            return true;
        } else {
            return false;
        }
    }
}
