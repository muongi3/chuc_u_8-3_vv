/**
 * PROJECT: LocChinhKhanh STORE
 * FILE: script.js
 * AUTHOR: Nguyen Duy Khanh
 * Chứa toàn bộ logic JavaScript cho website: xử lý slider, lọc sản phẩm, AJAX giỏ hàng và các hiệu ứng giao diện.
 */

$(document).ready(function () {

    // --- 1. BANNER OWL CAROUSEL (Trang chủ) ---
    if ($('#banner-area .owl-carousel').length > 0 && typeof $.fn.owlCarousel === 'function') {
        $("#banner-area .owl-carousel").owlCarousel({
            dots: true,
            items: 1,
            autoplay: true,
            autoplayTimeout: 5000,
            loop: true
        });
    }

    // --- 2. TOP SALE OWL CAROUSEL (Trang chủ) ---
    if ($('#top-sale .owl-carousel').length > 0 && typeof $.fn.owlCarousel === 'function') {
        $("#top-sale .owl-carousel").owlCarousel({
            loop: true,
            nav: true,
            dots: false,
            responsive: {
                0: { items: 1 },
                600: { items: 3 },
                1000: { items: 5 }
            }
        });
    }


    // --- 3. NEW PHONES OWL CAROUSEL ---
    if ($('#new-phones .owl-carousel').length > 0 && typeof $.fn.owlCarousel === 'function') {
        $("#new-phones .owl-carousel").owlCarousel({
            loop: true,
            nav: false,
            dots: true,
            responsive: {
                0: { items: 1 },
                600: { items: 3 },
                1000: { items: 5 }
            }
        });
    }

    // --- 6. BLOGS OWL CAROUSEL ---
    if ($("#blogs .owl-carousel").length > 0 && typeof $.fn.owlCarousel === 'function') {
        $("#blogs .owl-carousel").owlCarousel({
            loop: true,
            nav: false,
            dots: true,
            responsive: {
                0: { items: 1 },
                600: { items: 3 }
            }
        });
    }

    // --- 7. XÁC NHẬN XÓA (Admin & Cart) ---
    $(document).on("click", ".btn-danger", function (event) {
        if ($(this).hasClass("delete-confirm") || $(this).text().toLowerCase().includes("xóa")) {
            if (!confirm("Bạn có chắc chắn muốn thực hiện hành động này không?")) {
                event.preventDefault();
            }
        }
    });

    // --- 8. PREVIEW HÌNH ẢNH (Admin Manage) ---
    $(document).on("change", 'input[type="file"][accept="image/*"]', function () {
        var imgFile = this.files[0];
        var $previewImg = $(this).parent().find('img');
        
        if (imgFile && $previewImg.length > 0) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $previewImg.attr("src", e.target.result);
            };
            reader.readAsDataURL(imgFile);
        }
    });

    // --- 10. AJAX TĂNG GIẢM SỐ LƯỢNG (Trang Cart.php) ---
    $(document).on("click", ".qty-up", function () {
        handleQtyUpdate($(this).data("id"), 1);
    });

    $(document).on("click", ".qty-down", function () {
        handleQtyUpdate($(this).data("id"), -1);
    });

    function handleQtyUpdate(id, change) {
        let $input = $(`.qty_input[data-id='${id}']`);
        let currentVal = parseInt($input.val());
        let newVal = currentVal + change;

        if (newVal >= 1) {
            $.ajax({
                url: "cart.php",
                type: 'post',
                data: { itemid: id, qty: newVal },
                success: function (result) {
                    if (result.trim() == "success") {
                        location.reload();
                    }
                }
            });
        }
    }
    // --- 11. XỬ LÝ TĂNG GIẢM SỐ LƯỢNG (Trang Product.php) ---
    // Dùng event delegation để đảm bảo luôn bắt được sự kiện
    $(document).on("click", ".btn-qty-up", function () {
        let $display = $("#display-qty");
        let $real = $("#real-qty");
        let newVal = parseInt($display.val()) + 1;
        
        $display.val(newVal);
        $real.val(newVal); // Cập nhật vào input hidden trong form
    });

    $(document).on("click", ".btn-qty-down", function () {
        let $display = $("#display-qty");
        let $real = $("#real-qty");
        let currentVal = parseInt($display.val());
        
        if (currentVal > 1) {
            let newVal = currentVal - 1;
            $display.val(newVal);
            $real.val(newVal);
        }
    });

    // --- 12. CHỌN MÀU SẮC (Đồng bộ giao diện) ---
    $(document).on("click", ".color-circle", function() {
        $(".color-circle").css({
            "outline": "none",
            "border": "2px solid #fff"
        }).removeClass("active");
        
        let color = $(this).css("background-color");
        $(this).css("outline", `2px solid ${color}`).addClass("active");
        
        // Nếu ông có input ẩn để lưu màu, cập nhật nó ở đây:
        // $("#selected-color").val($(this).data("color-name"));
    });

}); // END document.ready

/**
 * --- CÁC HÀM GLOBAL ---
 * (Để bên ngoài để gọi được từ onclick trong HTML)
 */

// Chọn quyền truy cập (Admin/User) khi đăng ký
function setRole(value, btn) {
    var roleInput = document.getElementById("role");
    if (roleInput) {
        roleInput.value = value;
        let buttons = document.querySelectorAll(".role-btn");
        buttons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
    }
}

// Ẩn hiện khung sửa địa chỉ (Trang Checkout.php)
function toggleEditAddress() {
    const displayDiv = document.getElementById('address-display');
    const editDiv = document.getElementById('address-edit');
    
    if (displayDiv && editDiv) {
        if (displayDiv.style.display === 'none') {
            displayDiv.style.display = 'block';
            editDiv.style.display = 'none';
        } else {
            displayDiv.style.display = 'none';
            editDiv.style.display = 'block';
        }
    }
}

// Lưu thay đổi địa chỉ (Tạm thời trên UI)
function saveAddressChange() {
    const newPhone = document.getElementById('edit-phone').value;
    const newAddress = document.getElementById('edit-address').value;

    const displayPhone = document.getElementById('display-phone');
    const displayAddress = document.getElementById('display-address');
    const hiddenAddress = document.getElementById('hidden-address');

    if (displayPhone) displayPhone.innerText = newPhone;
    if (displayAddress) displayAddress.innerText = newAddress;
    if (hiddenAddress) hiddenAddress.value = newAddress;

    toggleEditAddress();
}

// --- 13. CHỌN RAM / ROM + ĐỔI GIÁ ---
if ($('.variant-option').length > 0) {

    const $options = $('.variant-option');
    const $price = $('.price-card h2'); // Đảm bảo class này khớp với thẻ h2 hiển thị giá của bạn
    const $input = $('#selected-variant');

    $options.on('click', function () {

        // 1. Xóa class active cũ và thêm vào cái mới
        $options.removeClass('active');
        $(this).addClass('active');

        // 2. Lấy giá từ data-price
        let price = $(this).data('price');
        
        // 3. ĐỔI GIÁ SANG ĐỊNH DẠNG VNĐ (Thay thế phần cũ của bạn)
        if ($price.length) {
            // Sử dụng Intl.NumberFormat để tự động thêm dấu chấm phân cách
            let formattedPrice = new Intl.NumberFormat('vi-VN').format(price) + 'đ';
            $price.text(formattedPrice);
        }

        // 4. Lưu variant vào hidden input
        if ($input.length) {
            $input.val($(this).data('variant'));
        }

    });
}