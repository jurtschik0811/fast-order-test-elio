<?php declare(strict_types=1);

namespace Js\FastOrder\Controller;

use Js\FastOrder\Page\FastOrderArticlePageLoader;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\Framework\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class FastOrderController extends StoreFrontController
{
    /**
     * @var FastOrderArticlePageLoader
     */
    private $fastOrderArticlePageLoader;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var EntityRepositoryInterface
     */
    private $fastOrderOrdersRepository;

    /**
     * FastOrderController constructor.
     * @param FastOrderArticlePageLoader $fastOrderArticlePageLoader
     * @param LineItemFactoryRegistry $lineItemFactory
     * @param CartService $cartService
     * @param EntityRepositoryInterface $fastOrderOrdersRepository
     */
    public function __construct(
        FastOrderArticlePageLoader $fastOrderArticlePageLoader,
        LineItemFactoryRegistry $lineItemFactory,
        CartService $cartService,
        EntityRepositoryInterface $fastOrderOrdersRepository
    ) {
        $this->fastOrderArticlePageLoader = $fastOrderArticlePageLoader;
        $this->lineItemFactory = $lineItemFactory;
        $this->cartService = $cartService;
        $this->fastOrderOrdersRepository = $fastOrderOrdersRepository;
    }

    /**
     * @Route("/fastorder/articles", name="jsfastorder.articles", methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */
    public function fastOrderArticles(SalesChannelContext $context, Request $request): Response
    {
        $page = $this->fastOrderArticlePageLoader->load($request, $context);
        return $this->renderStorefront(
            '@JsFastOrder/storefront/page/fastorder/article-search-list.html.twig',
            ['page' => $page]
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/fastorder", name="jsfastorder.fastorder", methods={"GET"})
     */
    public function fastOrder(Request $request) :Response
    {
        return $this->renderStorefront('@JsFastOrder/storefront/page/fastorder/index.html.twig');
    }

    /**
     * @param Request $request
     * @param Cart $cart
     * @param SalesChannelContext $context
     * @param Context $reposetoryContext
     * @return RedirectResponse
     * @Route("/fastorder/order", name="jsfastorder.order", methods={"POST"})
     */
    public function fastOrderOrder(Request $request, Cart $cart, SalesChannelContext $context, Context $reposetoryContext): RedirectResponse
    {
        $articlesRequest = $request->request;
        $articles = [];
        $orders = [];
        $quantityTotal = 0;
        $priceTotal = 0;

        // iterate all given articles
        foreach ($articlesRequest as $fieldName => $value) {
            $sequentialNumber = substr($fieldName, -1);
            // add current article to cart service
            if (strpos($fieldName, "fast-order-product-id-") !== false
                && !empty($articlesRequest->get("fast-order-product-id-$sequentialNumber"))
                && !empty($articlesRequest->get("fast-order-quantity-$sequentialNumber"))
            ) {
                // create new article line item
                $article = new LineItem(
                    $request->request->get("fast-order-product-id-$sequentialNumber"),
                    LineItem::PRODUCT_LINE_ITEM_TYPE,
                    $articlesRequest->get("fast-order-product-id-$sequentialNumber"),
                    (int)$articlesRequest->get("fast-order-quantity-$sequentialNumber")
                );

                $price = (double)$articlesRequest->get("fast-order-product-price-$sequentialNumber");
                $article->setStackable(true);
                $article->setRemovable(true);
                $quantityTotal += $article->getQuantity();
                $priceTotal += $price;

                array_push($orders, [
                    'product_id' => $article->getId(),
                    'quantity' => $article->getQuantity(),
                    'price' => $articlesRequest->get("fast-order-product-price-$sequentialNumber")
                ]);

                $articles[] = $article;
            }
        }

        $this->cartService->add($cart, $articles, $context);

        if (count($orders) > 0) {
            $this->fastOrderOrdersRepository->create([
                [
                    'sessionID' => $request->getSession()->get(PlatformRequest::HEADER_CONTEXT_TOKEN),
                    'orders' => $orders,
                    'quantityTotal' => $quantityTotal,
                    'priceTotal' => (string)$priceTotal
                ]
            ], $reposetoryContext);
        }

        return $this->redirectToRoute('frontend.checkout.cart.page');
    }
}
