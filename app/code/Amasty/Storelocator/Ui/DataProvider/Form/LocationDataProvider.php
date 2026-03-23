<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Ui\DataProvider\Form;

use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection as GalleryCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class LocationDataProvider extends AbstractDataProvider
{
    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var GalleryCollection
     */
    private $galleryCollection;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        ImageProcessor $imageProcessor,
        AttributeCollection $attributeCollection,
        GalleryCollection $galleryCollection,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collection;
        $this->imageProcessor = $imageProcessor;
        $this->attributeCollection = $attributeCollection;
        $this->galleryCollection = $galleryCollection;
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        $this->collection->setFlag(Collection::IS_NEED_TO_COLLECT_AMASTY_LOCATION_DATA, true);
        $data = parent::getData();

        /**
         * It is need for support of several fieldsets.
         * For details @see \Magento\Ui\Component\Form::getDataSourceData
         */
        if ($data['totalRecords'] > 0) {
            $locationId = (int)$data['items'][0]['id'];
            $locationModel = $this->collection->getItemById($locationId);
            if ($stateId = (int)$locationModel->getState()) {
                $locationModel->setData('state', '');
            }
            $locationModel->setData('state_id', $stateId);
            $locationData = $locationModel->getData();
            foreach ($locationData['attributes'] as $attribute) {
                $locationData['store_attribute'][$attribute['attribute_id']] = $attribute['value'];
            }

            if ($locationModel->getMarkerImg()) {
                $locationData['marker_img'] = [
                    [
                        'name' => $locationModel->getMarkerImg(),
                        'url' => $locationModel->getMarkerImageUrl()
                    ]
                ];
            }
            $galleryImages = $this->galleryCollection->getImagesByLocation($locationId);
            if (!empty($galleryImages)) {
                $locationData['gallery_image'] = [];

                foreach ($galleryImages as $image) {
                    $imgName = $image->getData('image_name');
                    $imgUrlParams = [ImageProcessor::AMLOCATOR_GALLERY_MEDIA_PATH, $locationData['id'], $imgName];
                    $imgUrl = $this->imageProcessor->getImageUrl($imgUrlParams);
                    $imgSize = $this->imageProcessor->getImageSize($imgUrlParams);

                    $locationData['gallery_image'][] = ['name' => $imgName, 'url' => $imgUrl, 'size' => $imgSize];

                    if ($image->getData('is_base')) {
                        $locationData['base_img'] = $imgName;
                    }
                }
            }
            $data[$locationId] = $locationData;
        }

        return $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getMeta(): array
    {
        $this->meta = parent::getMeta();

        $attributes = $this->attributeCollection->preparedAttributes(true);

        foreach ($attributes as $attributeData) {
            $this->createElement(
                $attributeData
            );
        }

        $locationId = (int)$this->request->getParam('id');
        $this->meta['map']['children']['marker_img']['arguments']['data']['config']['uploaderConfig']['url'] =
            'amasty_storelocator/file/upload/type/marker_img/id/' . $locationId;

        $this->meta['image_gallery']['children']['gallery']['arguments']['data']['config']['uploaderConfig']['url'] =
            'amasty_storelocator/file/upload/type/gallery_image/id/' . $locationId;

        return $this->meta;
    }

    /**
     * Create form element
     *
     * @param array $attributeData
     */
    private function createElement(array $attributeData): void
    {
        $configuration = &$this->meta['store_attribute']['children']
                          [$attributeData['attribute_id']]['arguments']['data']['config'];

        $configuration['options'] = [];
        $configuration['label'] = $attributeData['label'];
        $configuration['componentType'] = $attributeData['frontend_input'];
        $configuration['dataScope'] = 'store_attribute.' . $attributeData['attribute_id'];
        switch ($attributeData['frontend_input']) {
            case 'boolean':
                $configuration['componentType'] = 'select';
                break;
            case 'text':
                $configuration['componentType'] = 'field';
                $configuration['formElement'] = 'input';
                break;
            case 'select':
                $configuration['options'][] = ['value' => '' , 'label' => __('Please select')];
                break;
        }

        $configuration['options'] = array_merge($configuration['options'], $attributeData['options']);
    }
}
