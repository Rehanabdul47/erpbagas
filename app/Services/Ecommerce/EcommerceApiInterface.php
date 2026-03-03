<?php

namespace App\Services\Ecommerce;

/**
 * Interface EcommerceApiInterface
 * 
 * Defines standard contract for connecting internal systems to external E-commerce platform APIs.
 */
interface EcommerceApiInterface
{
    /**
     * Retrieve a list of orders based on filters.
     * 
     * @param array $params Filtering parameters (like status, start_date, end_date)
     * @return array Standardized array of order headers
     */
    public function getOrderList(array $params = []): array;

    /**
     * Retrieve specific granular detail for a specific order.
     * 
     * @param string $orderId Platform-specific order ID
     * @return array Standardized array of order item details 
     */
    public function getOrderDetail(string $orderId): array;
}
