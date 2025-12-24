<?php
/**
 * Web Routes
 * Định nghĩa các routes mapping và special routes
 */

return [
    // Special routes (không theo chuẩn controller/method)
    'special' => [
        'about' => ['Page', 'about'],
        'return-policy' => ['Page', 'returnPolicy'],
        'privacy-policy' => ['Page', 'privacyPolicy'],
        'terms-of-service' => ['Page', 'termsOfService'],
        'buying-guide' => ['Page', 'buyingGuide'],
    ],
    
    // Route mapping (route key => Controller name)
    'map' => [
        'products' => 'Product',
        'product' => 'Product',
        'news' => 'News',
        'cart' => 'Cart',
        'orders' => 'Order',
        'user' => 'User',
        'home' => 'Home',
        'admin' => 'Admin',
        'promotions' => 'Promotion',
        'coupons' => 'Coupon',
        'sliders' => 'Slider',
        'pages' => 'Page',
        'feedback' => 'Feedback',
        'review' => 'Review',
        'reviews' => 'Review',
        'tracking' => 'Tracking',
        'wishlist' => 'Wishlist',
        'address' => 'Address',
        'notifications' => 'Notification',
        'reports' => 'Report',
        'chatbot' => 'Chatbot',
        'review' => 'Review',
        'reviews' => 'Review',
        'tracking' => 'Tracking',
        'wishlist' => 'Wishlist',
        'address' => 'Address',
        'notifications' => 'Notification',
        'reports' => 'Report',
    ]
];
