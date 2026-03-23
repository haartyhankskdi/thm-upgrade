<?php
declare(strict_types=1);
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Search Hyva Compatibility M2 by Amasty
 */

namespace Amasty\XsearchHyvaCompatibility\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * GraphQl queries for search popup
 */
class SearchPopupQueries implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Returns GraphQl query for products
     *
     * @return string
     */
    public function queryProducts(): string
    {
        if (!$this->isSectionEnabled('product')) {
            return '';
        }

        return "products: xsearchProducts(search: \$inputText) {
                code
                items {
                  __typename
                  small_image {
                    label
                    url
                  }

                  id
                  is_salable
                  short_description {
                    html
                  }
                  name
                  rating_summary
                  reviews_count
                  sku
                  url_key
                  url_suffix
                  url_path
                  canonical_url
                  url_rewrites {
                    url
                  }
                  
                  price_range {
                    minimum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                    maximum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                 }
                }
                total_count
              }";
    }

    /**
     * Returns GraphQl query for pages
     *
     * @return string
     */
    public function queryPages(): string
    {
        if (!$this->isSectionEnabled('page')) {
            return '';
        }

        return "page: xsearchPages(search: \$inputText) {
                   items {
                      description
                      name
                      title
                      url
                    },
                    total_count
                }";
    }

    /**
     * Returns GraphQl query for categories
     *
     * @return string
     */
    public function queryCategories(): string
    {
        if (!$this->isSectionEnabled('category')) {
            return '';
        }

        return "category: xsearchCategories(search: \$inputText) {
            code
            items {
              description
              name
              url
            }
            total_count
          }";
    }

    /**
     * Returns GraphQl query for history
     *
     * @return string
     */
    public function queryBrowsingHistory(): string
    {
        if (!$this->isSectionEnabled('browsing_history')) {
            return '';
        }

        return "browsingHistory: xsearchBrowsingHistory {
            code
            items {
              name
              url
            }
            total_count
          }";
    }

    /**
     * Returns GraphQl query for recent searches
     *
     * @return string
     */
    public function queryRecentSearches(): string
    {
        if (!$this->isSectionEnabled('recent_searches')) {
            return '';
        }

        return "recentSearches: xsearchRecentSearches {
                    code
                    items {
                      name
                      num_results
                      url
                    }
                    total_count
                  }";
    }

    /**
     * Returns GraphQl query for recent searches
     *
     * @return string
     */
    public function queryPopularSearches(): string
    {
        if (!$this->isSectionEnabled('popular_searches')) {
            return '';
        }

        return "popularSearches: xsearchPopularSearches {
                    code
                    items {
                      name
                      num_results
                      url
                    }
                    total_count
                  }";
    }

    /**
     * Returns GraphQl query for brands
     *
     * @return string
     */
    public function queryBrands(): string
    {
        if (!$this->isSectionEnabled('brand')) {
            return '';
        }

        return "brand: xsearchBrands(search: \$inputText) {
                code
                items {
                  name
                  title
                  url
                }
                total_count
              }";
    }

    /**
     * Returns GraphQl query for blog posts
     *
     * @return string
     */
    public function queryBlogPosts(): string
    {
        if (!$this->isSectionEnabled('blog')) {
            return '';
        }

        return "blog: xsearchBlogs(search: \$inputText) {
                code
                items {
                  description
                  name
                  title
                  url
                }
                total_count
              }";
    }

    /**
     * Returns GraphQl query for blog posts
     *
     * @return string
     */
    public function queryFAQ(): string
    {
        if (!$this->isSectionEnabled('faq')) {
            return '';
        }

        return "faq: xsearchFaqs(search: \$inputText) {
                code
                items {
                  name
                  title
                  url
                }
                total_count
              }";
    }

    /**
     * Returns GraphQl query for blog posts
     *
     * @return string
     */
    public function queryLanding(): string
    {
        if (!$this->isSectionEnabled('landing_page')) {
            return '';
        }

        return "landingPage: xsearchLandings(search: \$inputText) {
                code
                items {
                  name
                  title
                  url
                }
                total_count
              }";
    }

    /**
     * Returns GraphQl query for related terms
     *
     * @return string
     */
    public function queryRelatedTerms(): string
    {
        return "terms: xsearchRelatedTerms(search: \$inputText) {
                    items {
                      count
                      name
                    }
                  }";
    }

    /**
     * Returns GraphQl query for recently viewed
     *
     * @return string
     */
    public function queryRecentlyViewed(): string
    {
        return "recentlyViewed: xsearchRecentlyViewed {
            items {
                small_image {
                    label
                    url
                }
                id
                is_salable
                short_description {
                    html
                }
                name
                options_container
                rating_summary
                reviews_count
                sku
                url_key
                url_suffix
                url_rewrites {
                  url
                }
                price_range {
                    minimum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                     maximum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                }
            }
        }";
    }

    /**
     * Returns GraphQl query for bestsellers
     *
     * @return string
     */
    public function queryBestsellers(): string
    {
        if (!$this->isSectionEnabled('bestsellers')) {
            return '';
        }

        return "bestsellers: xsearchBestsellerProducts {
            items {
                small_image {
                    label
                    url
                }
                id
                is_salable
                short_description {
                    html
                }
                name
                options_container
                rating_summary
                reviews_count
                sku
                url_key
                url_suffix
                url_rewrites {
                  url
                }
                price_range {
                    minimum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                     maximum_price {
                        regular_price {
                            value
                            currency
                        }
                        final_price {
                            value
                            currency
                        }
                    }
                }
            }
        }";
    }

    /**
     * Configuration query
     *
     * @return string
     */
    public function queryStoreConfig(): string
    {
        return "config: storeConfig {
            amasty_xsearch_product_reviews
            amasty_xsearch_product_add_to_cart
            amasty_xsearch_product_redirect_single_product
            amasty_xsearch_general_full_screen
            amasty_xsearch_product_popup_display
            amasty_xsearch_product_show_sku
            amasty_xsearch_product_desc_length

            secure_base_media_url

            amasty_xsearch_product_title
            amasty_xsearch_popular_searches_title
            amasty_xsearch_browsing_history_title
            amasty_xsearch_recent_searches_title
            amasty_xsearch_recently_viewed_title
            amasty_xsearch_landing_page_title
            amasty_xsearch_bestsellers_position

            amasty_xsearch_faq_title
            amasty_xsearch_brand_title
            amasty_xsearch_blog_title
            amasty_xsearch_category_title
            amasty_xsearch_bestsellers_title
            amasty_xsearch_page_title

            amasty_xsearch_popular_searches_first_click
            amasty_xsearch_browsing_history_first_click
            amasty_xsearch_recent_searches_first_click

            amasty_xsearch_layout_enabled
            amasty_xsearch_layout_border
            amasty_xsearch_layout_hover
            amasty_xsearch_layout_highlight
            amasty_xsearch_layout_background
            amasty_xsearch_layout_text
            amasty_xsearch_layout_hover_text
            amasty_xsearch_layout_search_button
            amasty_xsearch_layout_search_button_text
        }";
    }

    /**
     * Returns GraphQl combined query
     *
     * @return string
     */
    public function getAllQueries(): string
    {
        return 'query xSearchQuery($inputText: String!) {' .
            implode(PHP_EOL, [
                $this->queryProducts(),
                $this->queryPages(),
                $this->queryCategories(),
                $this->queryBrowsingHistory(),
                $this->queryRecentSearches(),
                $this->queryPopularSearches(),
                $this->queryBrands(),
                $this->queryBlogPosts(),
                $this->queryRelatedTerms(),
                $this->queryLanding(),
                $this->queryFAQ()
            ]).
            '}';
    }

    /**
     * Initial query string
     *
     * @return string
     */
    public function getInitialQueries(): string
    {
        $queries = [
            $this->queryRecentlyViewed(),
            $this->queryBestsellers()
        ];

        if ($this->showInFirstClick('popular_searches')) {
            $queries[] = $this->queryPopularSearches();
        }

        if ($this->showInFirstClick('recent_searches')) {
            $queries[] = $this->queryRecentSearches();
        }

        if ($this->showInFirstClick('browsing_history')) {
            $queries[] = $this->queryBrowsingHistory();
        }

        return "query xSearchQuery {\n".
            implode(PHP_EOL, $queries).
            "\n}";
    }

    /**
     * Add to compare list mutation
     *
     * @return string
     */
    public function mutationAddToCompareList(): string
    {
        return 'addProductsToCompareList(input: {
                uid: $uid,
                products: $products
            }) {
                items {
                    product {
                        name
                    }
                }
                item_count
            }';
    }

    /**
     * Add to cart mutation
     *
     * @return string
     */
    public function mutationAddToCart(): string
    {
        return 'addProductsToCart(
                cartId: $cartId,
                cartItems: $cartItems
                ) {
                    cart {
                        items {
                            product {
                                name
                            }
                        }
                        total_quantity
                    }
                    user_errors {
                        code
                        message
                    }
                }';
    }

    /**
     * Add to wishlist mutation
     *
     * @return string
     */
    public function mutationAddProductsToWishlist(): string
    {
        return 'addProductsToWishlist(
                wishlistId: $wishlistId,
                wishlistItems: $wishlistItems
                ) {
                    wishlist {
                        items {
                            id
                            product {
                                name
                            }
                        }
                        items_count
                    }
                    user_errors {
                        code
                        message
                    }
                }';
    }

    /**
     * Check if section is enabled
     *
     * @param string $name
     * @param int|null $store
     * @return mixed
     */
    private function isSectionEnabled($name, $store = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            'amasty_xsearch/' . $name . '/enabled',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if show first click
     *
     * @param string $name
     * @param int|null $store
     * @return mixed
     */
    private function showInFirstClick($name, $store = null): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            'amasty_xsearch/' . $name . '/first_click',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
