<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <title>VNPAY RESPONSE</title>
        <!-- Bootstrap core CSS -->
        <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
        <!-- Custom styles for this template -->
        <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">         
        <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    </head>
    <body>
        <?php
        require_once("./config.php");
        $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        ?>
        <!--Begin display -->
        <div class="container">
            <div class="header clearfix">
                <h3 class="text-muted">VNPAY RESPONSE</h3>
            </div>
            <div class="table-responsive">
                <div class="form-group">
                    <label >Mã đơn hàng:</label>

                    <label><?php echo $_GET['vnp_TxnRef'] ?></label>
                </div>    
                <div class="form-group">
                    <label >Số tiền:</label>
                    <label><?php echo number_format($_GET['vnp_Amount'] / 100, 0, ',', '.') ?>đ</label>
                </div>  
                <div class="form-group">
                    <label >Nội dung thanh toán:</label>
                    <label><?php echo $_GET['vnp_OrderInfo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã phản hồi (vnp_ResponseCode):</label>
                    <label><?php echo $_GET['vnp_ResponseCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã GD Tại VNPAY:</label>
                    <label><?php echo $_GET['vnp_TransactionNo'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Mã Ngân hàng:</label>
                    <label><?php echo $_GET['vnp_BankCode'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Thời gian thanh toán:</label>
                    <label><?php echo $_GET['vnp_PayDate'] ?></label>
                </div> 
                <div class="form-group">
                    <label >Kết quả:</label>
                    <label>
                        <?php
                        require_once(__DIR__ . '/../app/core/DB.php');
                        require_once(__DIR__ . '/../app/models/Order.php');

                        $orderModel = new Order();
                        $orderCode = $_GET['vnp_TxnRef'];

                        if ($secureHash == $vnp_SecureHash) {
                            if ($_GET['vnp_ResponseCode'] == '00') {
                                // Lấy đơn hàng theo mã
                                $order = $orderModel->getOrderByCode($orderCode);
                                if ($order) {
                                    // Cập nhật trạng thái thanh toán
                                    $orderModel->updatePaymentStatus($order['id'], 'paid', 'Thanh toán qua VNPAY');
                                    echo "<span style='color:blue'>Giao dịch thành công</span>";
                                    echo "<br><br><a href='" . (defined('BASE_URL') ? BASE_URL : 'http://localhost/flower_shop/public') . "/orders/success/" . $order['id'] . "' class='btn btn-primary'>Xem đơn hàng</a>";
                                } else {
                                    echo "<span style='color:red'>Không tìm thấy đơn hàng</span>";
                                }
                            } else {
                                echo "<span style='color:red'>Giao dịch không thành công (Mã lỗi: " . $_GET['vnp_ResponseCode'] . ")</span>";
                                echo "<br><br><a href='" . (defined('BASE_URL') ? BASE_URL : 'http://localhost/flower_shop/public') . "/cart' class='btn btn-default'>Quay lại giỏ hàng</a>";
                            }
                        } else {
                            echo "<span style='color:red'>Chữ ký không hợp lệ</span>";
                        }
                        ?>

                    </label>
                </div> 
            </div>
            <p>
                &nbsp;
            </p>
            <footer class="footer">
                   <p>&copy; VNPAY <?php echo date('Y')?></p>
            </footer>
        </div>  
    </body>
</html>
