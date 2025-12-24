<?php
/**
 * PageController - Xử lý các trang tĩnh (About, Policies, etc.)
 */
class PageController extends Controller {
    
    /**
     * Trang về chúng tôi
     */
    public function about() {
        $user = Session::getUser();
        $data = ['user' => $user];
        $this->view('pages/about', $data);
    }
    
    /**
     * Chính sách đổi trả
     */
    public function returnPolicy() {
        $user = Session::getUser();
        $data = ['user' => $user];
        $this->view('pages/return-policy', $data);
    }
    
    /**
     * Chính sách bảo mật
     */
    public function privacyPolicy() {
        $user = Session::getUser();
        $data = ['user' => $user];
        $this->view('pages/privacy-policy', $data);
    }
    
    /**
     * Điều khoản dịch vụ
     */
    public function termsOfService() {
        $user = Session::getUser();
        $data = ['user' => $user];
        $this->view('pages/terms-of-service', $data);
    }
    
    /**
     * Hướng dẫn mua hàng
     */
    public function buyingGuide() {
        $user = Session::getUser();
        $data = ['user' => $user];
        $this->view('pages/buying-guide', $data);
    }
}
