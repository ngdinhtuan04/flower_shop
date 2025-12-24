<?php
/**
 * Admin Routes
 * Định nghĩa các routes cho admin panel
 * 
 * Các controller đã được tách từ AdminController.php:
 * - AdminAuth: Xác thực (login, logout, dashboard, OTP, phê duyệt admin)
 * - AdminUser: Quản lý người dùng
 * - AdminCategory: Quản lý danh mục
 * - AdminProduct: Quản lý sản phẩm
 * - AdminNews: Quản lý tin tức và bình luận
 * - AdminOrder: Quản lý đơn hàng
 * - AdminFeedback: Quản lý góp ý
 * - AdminRevenue: Báo cáo doanh thu
 * - AdminSupplier: Quản lý nhà cung cấp
 */

return [
    // ============== AUTH (AdminAuthController) ==============
    'login' => [
        'controller' => 'AdminAuth',
        'index' => 'login'
    ],
    'logout' => [
        'controller' => 'AdminAuth',
        'index' => 'logout'
    ],
    'dashboard' => [
        'controller' => 'AdminAuth',
        'index' => 'dashboard'
    ],
    'pending-admins' => [
        'controller' => 'AdminAuth',
        'index' => 'pendingAdmins'
    ],
    'approve-admin' => [
        'controller' => 'AdminAuth',
        'index' => 'approveAdmin'
    ],
    'reject-admin' => [
        'controller' => 'AdminAuth',
        'index' => 'rejectAdmin'
    ],
    'register-admin' => [
        'controller' => 'AdminAuth',
        'index' => 'registerAdmin'
    ],
    'verify-otp-admin' => [
        'controller' => 'AdminAuth',
        'index' => 'verifyOTPAdmin'
    ],
    'resend-otp-admin' => [
        'controller' => 'AdminAuth',
        'index' => 'resendOTPAdmin'
    ],
    
    // ============== USERS (AdminUserController) ==============
    'users' => [
        'controller' => 'AdminUser',
        'index' => 'manageUsers',
        'update-status' => 'updateUserStatus',
        'update' => 'updateUser',
        'delete' => 'deleteUser'
    ],
    'update-user-status' => [
        'controller' => 'AdminUser',
        'index' => 'updateUserStatus'
    ],
    'update-user' => [
        'controller' => 'AdminUser',
        'index' => 'updateUser'
    ],
    'delete-user' => [
        'controller' => 'AdminUser',
        'index' => 'deleteUser'
    ],
    
    // ============== CATEGORIES (AdminCategoryController) ==============
    'categories' => [
        'controller' => 'AdminCategory',
        'index' => 'manageCategories',
        'store' => 'categoriesStore',
        'update' => 'categoriesUpdate',
        'delete' => 'categoriesDelete'
    ],
    'categories-store' => [
        'controller' => 'AdminCategory',
        'index' => 'categoriesStore'
    ],
    'categories-update' => [
        'controller' => 'AdminCategory',
        'index' => 'categoriesUpdate'
    ],
    'categories-delete' => [
        'controller' => 'AdminCategory',
        'index' => 'categoriesDelete'
    ],
    
    // ============== PRODUCTS (AdminProductController) ==============
    'products' => [
        'controller' => 'AdminProduct',
        'index' => 'manageProducts',
        'store' => 'productsStore',
        'update' => 'productsUpdate',
        'update-stock' => 'productsUpdateStock',
        'delete' => 'productsDelete',
        'list-json' => 'productsListJson'
    ],
    'products-store' => [
        'controller' => 'AdminProduct',
        'index' => 'productsStore'
    ],
    'products-update' => [
        'controller' => 'AdminProduct',
        'index' => 'productsUpdate'
    ],
    'products-update-stock' => [
        'controller' => 'AdminProduct',
        'index' => 'productsUpdateStock'
    ],
    'products-delete' => [
        'controller' => 'AdminProduct',
        'index' => 'productsDelete'
    ],
    
    // ============== NEWS & COMMENTS (AdminNewsController) ==============
    'news' => [
        'controller' => 'AdminNews',
        'index' => 'manageNews',
        'store' => 'newsStore',
        'update' => 'newsUpdate',
        'delete' => 'newsDelete',
        'upload-image' => 'newsUploadImage'
    ],
    'news-store' => [
        'controller' => 'AdminNews',
        'index' => 'newsStore'
    ],
    'news-update' => [
        'controller' => 'AdminNews',
        'index' => 'newsUpdate'
    ],
    'news-delete' => [
        'controller' => 'AdminNews',
        'index' => 'newsDelete'
    ],
    'news-upload-image' => [
        'controller' => 'AdminNews',
        'index' => 'newsUploadImage'
    ],
    'comments' => [
        'controller' => 'AdminNews',
        'index' => 'manageComments'
    ],
    'comment-update-status' => [
        'controller' => 'AdminNews',
        'index' => 'commentUpdateStatus'
    ],
    'comment-hide' => [
        'controller' => 'AdminNews',
        'index' => 'commentHide'
    ],
    'comment-mark-spam' => [
        'controller' => 'AdminNews',
        'index' => 'commentMarkSpam'
    ],
    'comment-delete' => [
        'controller' => 'AdminNews',
        'index' => 'commentDelete'
    ],
    
    // ============== ORDERS (AdminOrderController) ==============
    'orders' => [
        'controller' => 'AdminOrder',
        'index' => 'orders',
        'detail' => 'orderDetail',
        'detail-json' => 'orderDetailJson',
        'update-status' => 'updateOrderStatus',
        'update-payment-status' => 'updatePaymentStatus'
    ],
    
    // ============== FEEDBACK (AdminFeedbackController) ==============
    'feedback' => [
        'controller' => 'AdminFeedback',
        'index' => 'feedback',
        'detail' => 'feedbackDetail',
        'reply' => 'feedbackReply',
        'update-status' => 'updateFeedbackStatus'
    ],
    
    // ============== REVIEWS (ReviewAdminController - existing) ==============
    'reviews' => [
        'controller' => 'ReviewAdmin',
        'index' => 'index',
        'approve' => 'approve',
        'reject' => 'reject',
        'hide' => 'hide',
        'show' => 'show',
        'delete' => 'delete'
    ],
    
    // ============== REVENUE (AdminRevenueController) ==============
    'revenue' => [
        'controller' => 'AdminRevenue',
        'index' => 'revenue',
        'export' => 'exportRevenue',
        'customers' => 'customers',
        'customer-detail' => 'customerDetail',
        'export-customers' => 'exportCustomers',
        'inventory' => 'inventory',
        'export-inventory' => 'exportInventory'
    ],
    
    // ============== SLIDERS (SliderController - existing) ==============
    'sliders' => [
        'controller' => 'Slider',
        'index' => 'index',
        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',
        'toggle-active' => 'toggleActive',
        'update-order' => 'updateOrder'
    ],
    
    // ============== SUPPLIERS (AdminSupplierController) ==============
    'suppliers' => [
        'controller' => 'AdminSupplier',
        'index' => 'manageSuppliers',
        'store' => 'suppliersStore',
        'update' => 'suppliersUpdate',
        'delete' => 'suppliersDelete',
        
        // Hợp đồng
        'contracts-json' => 'suppliersContractsJson',
        'contract-detail-json' => 'suppliersContractDetailJson',
        'contract-store' => 'suppliersContractStore',
        'contract-update' => 'suppliersContractUpdate',
        'contract-delete' => 'suppliersContractDelete',
        'generate-contract-code' => 'generateContractCode',
        
        // Sản phẩm trong hợp đồng
        'contract-products-json' => 'contractProductsJson',
        'contract-product-store' => 'contractProductStore',
        'contract-product-update' => 'contractProductUpdate',
        'contract-product-delete' => 'contractProductDelete',
        'product-suppliers-json' => 'productSuppliersJson',
        'import-stock' => 'suppliersImportStock',
        
        // Danh sách
        'list-json' => 'suppliersListJson',
        'shop-products-json' => 'shopProductsJson',
        
        // Đơn nhập hàng
        'purchase-orders-json' => 'suppliersPurchaseOrdersJson',
        'purchase-order-detail' => 'suppliersPurchaseOrderDetail',
        'purchase-order-store' => 'suppliersPurchaseOrderStore',
        'purchase-order-receive' => 'suppliersPurchaseOrderReceive',
        'purchase-order-update-status' => 'suppliersPurchaseOrderUpdateStatus',
        'purchase-order-delete' => 'suppliersPurchaseOrderDelete',
        'generate-po-code' => 'suppliersGeneratePOCode'
    ]
];
