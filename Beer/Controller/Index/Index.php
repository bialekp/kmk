<?php

namespace Kmk\Beer\Controller\Index;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Zend_Http_Client;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;
    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ZendClientFactory $httpClientFactory,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->httpClientFactory = $httpClientFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $client = $this->httpClientFactory->create();

        $beers = json_decode($this->retrieveData());


        foreach ($beers as $beer) {

            $this->createProduct($beer);

//            var_dump($beer);die;
//            $params = $this->bodyBuilder(substr($beer->title, 0, 64));
//
//
//            $client->setUri('http://mopar.local/rest/default/V1/products');
//            $client->setMethod(Zend_Http_Client::POST);
//            $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
//            $client->setHeaders('Accept', 'application/json');
//            $client->setParameterPost($params); //json
////            $client->setHeaders("Authorization", "Bearer 1212121212121");
//            $response = $client->request();
//            var_dump($response);
//            echo 'created: ' . $beer->id;
////            $this->productRepository->save($product);
//            echo "saved\n";
        }

        return $result->setData('created');
    }

    private function retrieveData()
    {
        $client = $this->httpClientFactory->create();

        $client->setUri('https://jsonplaceholder.typicode.com/posts');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept', 'application/json');
        $client->setHeaders("Authorization", "Bearer 1212121212121");
//        $client->setParameterPost($params); //json
        $response = $client->request();

        return $response->getBody();
    }

    private function createProduct($productData)
    {
        /** @var ProductInterface $product */
        $product = $this->productFactory->create();
        $product->setSku(substr($productData->title, 0, 64));
        $product->setName($productData->title);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $product->setVisibility(4);
        $product->setPrice(1);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

        try {
            $this->productRepository->save($product);
        } catch (CouldNotSaveException $e) {
            echo 'There was a problem with creating product. Error code: ' . $e->getCode();
        }
    }


    private function createByApi()
    {


    }


    private function bodyBuilder($sku)
    {
        $data = [
            "product" => [
                "sku" => $sku,
                "name" => "Test Product 1",
                "attribute_set_id" => 4,
                "price" => 99,
                "status" => 1,
                "visibility" => 2,
                "type_id" => "simple",
                "weight" => "1",
                "extension_attributes" => [
                    "category_links" => [
                        [
                            "position" => 0,
                            "category_id" => "5",
                        ],
                        [
                            "position" => 1,
                            "category_id" => "7",
                        ],
                    ],
                    "stock_item" => [
                        "qty" => "1000",
                        "is_in_stock" => true,
                    ],
                ],
                "custom_attributes" => [
                    [
                        "attribute_code" => "description",
                        "value" => "Description of product here",
                    ],
                    [
                        "attribute_code" => "short_description",
                        "value" => "short description of product",
                    ],
                ],
            ],
        ];
        $dataString = json_encode($data);

        return $dataString;
    }
}