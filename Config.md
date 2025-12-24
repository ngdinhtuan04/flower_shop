-   Trong file config/config.php: sửa đường dãn theo cấu trúc của ngrok của mình
-   Trong file vnpay_php/config sửa:

*   $vnp_TmnCode
*   $vnp_HashSecret
    sửa nhưu trong email bên vnpay cung cấp
*   $vnp_Returnurl
    sửa theo đường dẫn ngrok

-   Chạy web thì chỉ cần config sau đó:
    bật ngrok lệnh: ngrok http 80
    bật xmapp
    mở config/config.php bấm ctrl + click vào link là được
