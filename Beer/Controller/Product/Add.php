<?php

namespace Kmk\Beer\Controller\Product;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Zend_Http_Client;

class Add extends Action
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

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ZendClientFactory $httpClientFactory
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ZendClientFactory $httpClientFactory,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->httpClientFactory = $httpClientFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $beers = json_decode($this->retrieveData());

        foreach ($beers as $beer) {
            $this->createProduct($beer);
        }

        return $result->setData('created');
    }

    /**
     * @return mixed
     */
    private function retrieveData()
    {
        $client = $this->httpClientFactory->create();

        $client->setUri('https://jsonplaceholder.typicode.com/posts');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders(Zend_Http_Client::CONTENT_TYPE, 'application/json');
        $client->setHeaders('Accept', 'application/json');
        $client->setHeaders("Authorization", "Bearer 1212121212121");
        $response = $client->request();

        return $response->getBody();
    }

    /**
     * @param $productData
     */
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
        } catch (Exception $e) {
            echo 'There was a problem with creating product. Error code: ' . $e->getCode();
        }
    }
}
