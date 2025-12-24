<?php
/**
 * AdminSupplierController
 * Xử lý các chức năng quản lý nhà cung cấp cho admin
 * - Danh sách nhà cung cấp
 * - CRUD nhà cung cấp
 * - Quản lý hợp đồng
 * - Quản lý sản phẩm catalog
 * - Quản lý đơn đặt hàng (Purchase Orders)
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';

class AdminSupplierController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Quản lý nhà cung cấp
     */
    public function manageSuppliers()
    {
        $this->requireAdmin();
        
        $supplierModel = $this->model('Supplier');
        $keyword = $_GET['search'] ?? '';
        
        if ($keyword) {
            $suppliers = $supplierModel->search($keyword);
        } else {
            $suppliers = $supplierModel->getAll();
        }
        
        // Đếm số sản phẩm cho mỗi nhà cung cấp
        foreach ($suppliers as &$supplier) {
            $supplier['product_count'] = $supplierModel->countProducts($supplier['id']);
        }
        
        $data = [
            'suppliers' => $suppliers,
            'keyword' => $keyword
        ];
        
        $this->view('admin/suppliers/index', $data);
    }

    /**
     * Thêm nhà cung cấp mới
     */
    public function suppliersStore()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $supplierModel = $this->model('Supplier');
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'address' => sanitize($_POST['address'] ?? '')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên nhà cung cấp không được để trống')
                  ->minLength('name', 3, 'Tên nhà cung cấp phải có ít nhất 3 ký tự');

        if (!empty($data['email'])) {
            $validator->email('email', 'Email không hợp lệ');
            
            // Kiểm tra email đã tồn tại
            if ($supplierModel->emailExists($data['email'])) {
                Session::setFlash('error', 'Email này đã được sử dụng!');
                $this->redirect('/admin/suppliers');
                return;
            }
        }

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/suppliers');
            return;
        }

        try {
            $result = $supplierModel->create($data);

            if ($result['success']) {
                Session::setFlash('success', 'Thêm nhà cung cấp thành công!');
            } else {
                Session::setFlash('error', 'Thêm nhà cung cấp thất bại! Vui lòng thử lại.');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Cập nhật nhà cung cấp
     */
    public function suppliersUpdate()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $supplierModel = $this->model('Supplier');
        $supplierId = intval($_POST['supplier_id'] ?? 0);
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'address' => sanitize($_POST['address'] ?? '')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên nhà cung cấp không được để trống')
                  ->minLength('name', 3, 'Tên nhà cung cấp phải có ít nhất 3 ký tự');

        if (!empty($data['email'])) {
            $validator->email('email', 'Email không hợp lệ');
            
            // Kiểm tra email đã tồn tại (trừ chính nó)
            if ($supplierModel->emailExists($data['email'], $supplierId)) {
                Session::setFlash('error', 'Email này đã được sử dụng bởi nhà cung cấp khác!');
                $this->redirect('/admin/suppliers');
                return;
            }
        }

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/suppliers');
            return;
        }

        try {
            $result = $supplierModel->update($supplierId, $data);

            if ($result['success']) {
                Session::setFlash('success', 'Cập nhật nhà cung cấp thành công!');
            } else {
                Session::setFlash('error', 'Cập nhật nhà cung cấp thất bại! Vui lòng thử lại.');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Xóa nhà cung cấp (soft delete)
     */
    public function suppliersDelete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $supplierModel = $this->model('Supplier');
        $supplierId = intval($_POST['supplier_id'] ?? 0);

        // Kiểm tra xem nhà cung cấp có sản phẩm không
        $productCount = $supplierModel->countProducts($supplierId);
        if ($productCount > 0) {
            Session::setFlash('error', "Không thể xóa! Nhà cung cấp này đang có {$productCount} sản phẩm.");
            $this->redirect('/admin/suppliers');
            return;
        }

        try {
            $result = $supplierModel->delete($supplierId);

            if ($result['success']) {
                Session::setFlash('success', 'Xóa nhà cung cấp thành công!');
            } else {
                Session::setFlash('error', 'Xóa nhà cung cấp thất bại! Vui lòng thử lại.');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * API: Lấy danh sách hợp đồng
     */
    public function suppliersContractsJson()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $contractModel = $this->model('SupplierContract');
        $filters = [
            'supplier_id' => $_GET['supplier_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'search' => $_GET['search'] ?? null
        ];
        
        $contracts = $contractModel->getAll(array_filter($filters));
        echo json_encode(['success' => true, 'data' => $contracts]);
        exit;
    }

    /**
     * API: Lấy chi tiết hợp đồng kèm sản phẩm
     */
    public function suppliersContractDetailJson()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $contractId = $_GET['id'] ?? null;
        if (!$contractId) {
            echo json_encode(['success' => false, 'message' => 'Contract ID không hợp lệ!']);
            exit;
        }
        
        $contractModel = $this->model('SupplierContract');
        $contractProductModel = $this->model('ContractProduct');
        
        $contract = $contractModel->getByIdWithProducts($contractId);
        if (!$contract) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hợp đồng!']);
            exit;
        }
        
        // Lấy danh sách sản phẩm trong hợp đồng
        $products = $contractProductModel->getByContractId($contractId);
        
        echo json_encode([
            'success' => true, 
            'data' => [
                'contract' => $contract,
                'products' => $products
            ]
        ]);
        exit;
    }

    /**
     * API: Lấy sản phẩm trong hợp đồng
     */
    public function contractProductsJson()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $contractProductModel = $this->model('ContractProduct');
        $contractId = $_GET['contract_id'] ?? null;
        
        // Nếu có contract_id thì lấy theo hợp đồng cụ thể, không thì lấy tất cả
        if ($contractId) {
            $products = $contractProductModel->getByContractId($contractId);
        } else {
            $products = $contractProductModel->getAllWithDetails();
        }
        
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    /**
     * API: Thêm sản phẩm vào hợp đồng
     */
    public function contractProductStore()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');
        $contractProductModel = $this->model('ContractProduct');
        $contractModel = $this->model('SupplierContract');
        
        $data = [
            'contract_id' => $_POST['contract_id'] ?? null,
            'product_id' => $_POST['product_id'] ?? null,
            'committed_quantity' => $_POST['committed_quantity'] ?? 0,
            'import_price' => $_POST['import_price'] ?? 0,
            'unit' => $_POST['unit'] ?? 'cái',
            'notes' => $_POST['notes'] ?? null,
            'allow_over_import' => isset($_POST['allow_over_import']) ? (int)$_POST['allow_over_import'] : 0
        ];

        // Validation
        if (empty($data['contract_id']) || empty($data['product_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng chọn hợp đồng và sản phẩm!']);
            exit;
        }

        if ($data['committed_quantity'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng cam kết phải lớn hơn 0!']);
            exit;
        }

        if ($data['import_price'] <= 0) {
            echo json_encode(['success' => false, 'message' => 'Giá nhập phải lớn hơn 0!']);
            exit;
        }

        // Check contract limit (hạn mức)
        $contract = $contractModel->getById($data['contract_id']);
        if (!$contract) {
            echo json_encode(['success' => false, 'message' => 'Hợp đồng không tồn tại!']);
            exit;
        }
        
        // If contract has a limit, validate
        if ($contract['contract_value'] > 0) {
            $stats = $contractModel->getStats($data['contract_id']);
            $currentTotalValue = $stats['total_value'] ?? 0;
            $newProductValue = $data['committed_quantity'] * $data['import_price'];
            $newTotalValue = $currentTotalValue + $newProductValue;
            
            if ($newTotalValue > $contract['contract_value']) {
                $remaining = $contract['contract_value'] - $currentTotalValue;
                echo json_encode([
                    'success' => false, 
                    'message' => 'Vượt quá hạn mức hợp đồng! Hạn mức còn lại: ' . number_format($remaining) . '₫. Giá trị sản phẩm thêm: ' . number_format($newProductValue) . '₫'
                ]);
                exit;
            }
        }

        $result = $contractProductModel->create($data);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Thêm sản phẩm vào hợp đồng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Thêm sản phẩm thất bại!']);
        }
        exit;
    }

    /**
     * API: Cập nhật sản phẩm trong hợp đồng
     */
    public function contractProductUpdate()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');
        $contractProductModel = $this->model('ContractProduct');
        
        $id = $_POST['contract_product_id'] ?? $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ!']);
            exit;
        }

        $data = [
            'committed_quantity' => $_POST['committed_quantity'] ?? 0,
            'delivered_quantity' => $_POST['delivered_quantity'] ?? 0,
            'import_price' => $_POST['import_price'] ?? 0,
            'unit' => $_POST['unit'] ?? 'cái',
            'notes' => $_POST['notes'] ?? null
        ];

        $result = $contractProductModel->update($id, $data);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Cập nhật thất bại!']);
        }
        exit;
    }

    /**
     * API: Nhập kho từ hợp đồng - cập nhật tồn kho và tiến độ
     */
    public function suppliersImportStock()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');
        
        $contractProductId = $_POST['contract_product_id'] ?? null;
        $productId = $_POST['product_id'] ?? null;
        $contractId = $_POST['contract_id'] ?? null;
        $quantity = intval($_POST['quantity'] ?? 0);
        $increaseCommitment = isset($_POST['increase_commitment']) && $_POST['increase_commitment'] === 'true';
        $newCommitted = intval($_POST['new_committed'] ?? 0);
        
        if (!$contractProductId || !$productId || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
            exit;
        }
        
        $contractProductModel = $this->model('ContractProduct');
        $productModel = $this->model('Product');
        
        // Lấy thông tin contract product
        $contractProduct = $contractProductModel->getById($contractProductId);
        if (!$contractProduct) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong hợp đồng!']);
            exit;
        }
        
        // Kiểm tra số lượng còn lại
        $remaining = $contractProduct['committed_quantity'] - $contractProduct['delivered_quantity'];
        $allowOverImport = isset($contractProduct['allow_over_import']) ? (int)$contractProduct['allow_over_import'] : 0;
        
        // Nếu nhập vượt cam kết và không có flag tăng cam kết
        if ($quantity > $remaining && !$increaseCommitment) {
            // Kiểm tra sản phẩm có được phép nhập vượt mức không
            if (!$allowOverImport) {
                echo json_encode(['success' => false, 'message' => "Sản phẩm này không được phép nhập vượt mức cam kết! Còn lại: {$remaining}"]);
                exit;
            }
        }
        
        // Kiểm tra hạn mức hợp đồng nếu tăng cam kết
        if ($increaseCommitment && $contractId) {
            $contractModel = $this->model('SupplierContract');
            $contract = $contractModel->getById($contractId);
            
            if ($contract && $contract['contract_value'] > 0) {
                // Tính tổng giá trị đã cam kết
                $usedValue = $contractProductModel->getTotalCommittedValue($contractId);
                $importPrice = floatval($contractProduct['import_price']);
                $additionalCommit = $newCommitted - $contractProduct['committed_quantity'];
                $additionalCost = $additionalCommit * $importPrice;
                
                $remainingBudget = $contract['contract_value'] - $usedValue;
                
                if ($additionalCost > $remainingBudget) {
                    $formatted = number_format($remainingBudget, 0, ',', '.');
                    echo json_encode(['success' => false, 'message' => "Vượt hạn mức hợp đồng! Ngân sách còn lại: {$formatted}₫"]);
                    exit;
                }
            }
        }
        
        try {
            // Bắt đầu transaction
            $db = DB::getInstance();
            $db->beginTransaction();
            
            // Nếu cần tăng cam kết
            if ($increaseCommitment && $newCommitted > $contractProduct['committed_quantity']) {
                $updateCommitResult = $contractProductModel->updateCommittedQuantity($contractProductId, $newCommitted);
                if (!$updateCommitResult) {
                    $db->rollBack();
                    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật số lượng cam kết!']);
                    exit;
                }
            }
            
            // 1. Cập nhật tồn kho sản phẩm (cộng thêm)
            $updateStockResult = $productModel->addStock($productId, $quantity);
            
            // 2. Cập nhật delivered_quantity trong contract_products
            $updateDeliveredResult = $contractProductModel->updateDeliveredQuantity($contractProductId, $quantity);
            
            if ($updateStockResult && $updateDeliveredResult) {
                $db->commit();
                
                $finalCommitted = $increaseCommitment ? $newCommitted : $contractProduct['committed_quantity'];
                $newDelivered = $contractProduct['delivered_quantity'] + $quantity;
                $newProgress = round(($newDelivered / $finalCommitted) * 100);
                
                $message = "Nhập kho thành công! +{$quantity} sản phẩm.";
                if ($increaseCommitment) {
                    $message .= " Cam kết đã tăng lên {$finalCommitted}.";
                }
                $message .= " Tiến độ: {$newProgress}%";
                
                echo json_encode([
                    'success' => true, 
                    'message' => $message
                ]);
            } else {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại!']);
            }
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Xóa sản phẩm khỏi hợp đồng
     */
    public function contractProductDelete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');
        $contractProductModel = $this->model('ContractProduct');
        
        $id = $_POST['contract_product_id'] ?? $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ!']);
            exit;
        }

        $result = $contractProductModel->delete($id);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Xóa thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa thất bại!']);
        }
        exit;
    }

    /**
     * API: Lấy các hợp đồng đang cung cấp một sản phẩm
     */
    public function productSuppliersJson()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $productId = $_GET['product_id'] ?? null;
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Product ID không hợp lệ!']);
            exit;
        }
        
        $contractProductModel = $this->model('ContractProduct');
        $contracts = $contractProductModel->getContractsByProductId($productId);
        $bestPrice = $contractProductModel->getBestImportPrice($productId);
        
        echo json_encode([
            'success' => true, 
            'data' => $contracts,
            'best_price' => $bestPrice
        ]);
        exit;
    }

    /**
     * API: Sinh mã hợp đồng tự động
     */
    public function generateContractCode()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $contractModel = $this->model('SupplierContract');
        $code = $contractModel->generateContractCode();
        
        echo json_encode(['success' => true, 'code' => $code]);
        exit;
    }

    /**
     * Thêm hợp đồng
     */
    public function suppliersContractStore()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }

        header('Content-Type: application/json');
        $contractModel = $this->model('SupplierContract');
        
        $data = [
            'supplier_id' => $_POST['supplier_id'] ?? null,
            'contract_code' => $_POST['contract_code'] ?? null,
            'contract_name' => $_POST['contract_name'] ?? null,
            'contract_type' => $_POST['contract_type'] ?? 'purchase',
            'contract_value' => $_POST['contract_value'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'payment_terms' => $_POST['terms'] ?? null,
            'delivery_terms' => null,
            'status' => $_POST['status'] ?? 'draft',
            'notes' => $_POST['notes'] ?? null,
            'created_by' => Session::getUser()['id']
        ];

        if ($contractModel->contractCodeExists($data['contract_code'])) {
            echo json_encode(['success' => false, 'message' => 'Mã hợp đồng đã tồn tại!']);
            exit;
        }

        $result = $contractModel->create($data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Thêm hợp đồng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Thêm hợp đồng thất bại!']);
        }
        exit;
    }

    /**
     * API: Cập nhật hợp đồng
     */
    public function suppliersContractUpdate() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $contractModel = $this->model('SupplierContract');
        
        $contractId = $_POST['contract_id'] ?? null;
        if (!$contractId) {
            echo json_encode(['success' => false, 'message' => 'Contract ID không hợp lệ!']);
            exit;
        }

        $data = [
            'supplier_id' => $_POST['supplier_id'] ?? null,
            'contract_code' => $_POST['contract_code'] ?? null,
            'contract_name' => $_POST['contract_name'] ?? null,
            'contract_type' => $_POST['contract_type'] ?? 'purchase',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'contract_value' => $_POST['contract_value'] ?? null,
            'payment_terms' => $_POST['terms'] ?? null,
            'delivery_terms' => null,
            'status' => $_POST['status'] ?? 'draft',
            'notes' => $_POST['notes'] ?? null
        ];

        $result = $contractModel->update($contractId, $data);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật hợp đồng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật hợp đồng thất bại!']);
        }
        exit;
    }

    /**
     * API: Xóa hợp đồng
     */
    public function suppliersContractDelete() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $contractModel = $this->model('SupplierContract');
        
        $contractId = $_POST['contract_id'] ?? null;
        if (!$contractId) {
            echo json_encode(['success' => false, 'message' => 'Contract ID không hợp lệ!']);
            exit;
        }

        $result = $contractModel->delete($contractId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Xóa hợp đồng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Xóa hợp đồng thất bại!']);
        }
        exit;
    }

    /**
     * API: Lấy danh sách nhà cung cấp cho dropdown
     */
    public function suppliersListJson() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $supplierModel = $this->model('Supplier');
        $suppliers = $supplierModel->getAll();
        
        echo json_encode(['success' => true, 'data' => $suppliers]);
        exit;
    }

    /**
     * API: Lấy danh sách sản phẩm của shop (để chọn khi thêm vào hợp đồng)
     */
    public function shopProductsJson() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $productModel = $this->model('Product');
        
        $search = $_GET['search'] ?? '';
        $categoryId = $_GET['category_id'] ?? null;
        
        // Lấy sản phẩm với filter đơn giản
        $products = $productModel->getAll([
            'search' => $search,
            'category_id' => $categoryId,
            'status' => 'active'
        ]);
        
        echo json_encode(['success' => true, 'data' => $products]);
        exit;
    }

    /**
     * API: Lấy danh sách đơn nhập hàng
     */
    public function suppliersPurchaseOrdersJson() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['supplier_id'])) {
            $filters['supplier_id'] = $_GET['supplier_id'];
        }
        if (!empty($_GET['contract_id'])) {
            $filters['contract_id'] = $_GET['contract_id'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $purchaseOrders = $purchaseOrderModel->getAll($filters);
        
        echo json_encode(['success' => true, 'data' => $purchaseOrders]);
        exit;
    }

    /**
     * API: Lấy chi tiết đơn nhập hàng
     */
    public function suppliersPurchaseOrderDetail() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $poId = $_GET['id'] ?? null;
        if (!$poId) {
            echo json_encode(['success' => false, 'message' => 'PO ID không hợp lệ!']);
            exit;
        }
        
        $po = $purchaseOrderModel->getByIdWithItems($poId);
        
        echo json_encode(['success' => true, 'data' => $po]);
        exit;
    }

    /**
     * API: Tạo đơn nhập hàng mới
     */
    public function suppliersPurchaseOrderStore() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        // Generate order code
        $orderCode = $purchaseOrderModel->generateOrderCode();
        
        // Create PO
        $data = [
            'order_code' => $orderCode,
            'contract_id' => $_POST['contract_id'] ?? null,
            'supplier_id' => $_POST['supplier_id'] ?? null,
            'order_date' => $_POST['order_date'] ?? date('Y-m-d'),
            'expected_date' => $_POST['expected_date'] ?? null,
            'total_amount' => 0,
            'status' => $_POST['status'] ?? 'draft',
            'notes' => $_POST['notes'] ?? null,
            'created_by' => Session::getUser()['id'] ?? null
        ];
        
        $result = $purchaseOrderModel->create($data);
        
        if ($result['success']) {
            $poId = $result['id'];
            
            // Add items if provided
            if (!empty($_POST['items'])) {
                $items = json_decode($_POST['items'], true);
                foreach ($items as $item) {
                    $purchaseOrderModel->addItem([
                        'purchase_order_id' => $poId,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'notes' => $item['notes'] ?? null
                    ]);
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Tạo đơn nhập hàng thành công!', 'po_id' => $poId, 'order_code' => $orderCode]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Tạo đơn nhập hàng thất bại!']);
        }
        exit;
    }

    /**
     * API: Nhận hàng - cập nhật tồn kho
     */
    public function suppliersPurchaseOrderReceive() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $poId = $_POST['po_id'] ?? null;
        if (!$poId) {
            echo json_encode(['success' => false, 'message' => 'PO ID không hợp lệ!']);
            exit;
        }
        
        // Parse received items
        $receivedItems = [];
        if (!empty($_POST['items'])) {
            $receivedItems = json_decode($_POST['items'], true);
        }
        
        if (empty($receivedItems)) {
            echo json_encode(['success' => false, 'message' => 'Không có sản phẩm nào để nhận!']);
            exit;
        }
        
        $result = $purchaseOrderModel->receiveItems($poId, $receivedItems);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Nhận hàng thành công! Tồn kho đã được cập nhật.']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Nhận hàng thất bại!']);
        }
        exit;
    }

    /**
     * API: Cập nhật trạng thái đơn nhập hàng
     */
    public function suppliersPurchaseOrderUpdateStatus() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $poId = $_POST['po_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$poId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
            exit;
        }
        
        $userId = Session::getUser()['id'] ?? null;
        $result = $purchaseOrderModel->updateStatus($poId, $status, $userId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái thất bại!']);
        }
        exit;
    }

    /**
     * API: Xóa đơn nhập hàng
     */
    public function suppliersPurchaseOrderDelete() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $poId = $_POST['po_id'] ?? null;
        if (!$poId) {
            echo json_encode(['success' => false, 'message' => 'PO ID không hợp lệ!']);
            exit;
        }
        
        $result = $purchaseOrderModel->delete($poId);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Xóa đơn nhập hàng thành công!']);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Xóa đơn nhập hàng thất bại!']);
        }
        exit;
    }

    /**
     * API: Sinh mã đơn nhập hàng tự động
     */
    public function suppliersGeneratePOCode() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $purchaseOrderModel = $this->model('PurchaseOrder');
        
        $poCode = $purchaseOrderModel->generateOrderCode();
        
        echo json_encode(['success' => true, 'po_code' => $poCode]);
        exit;
    }
}
