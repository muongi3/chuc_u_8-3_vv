<section id="banner_adds">
    <div class="container py-5">
        <div class="row g-4 justify-content-center">
            
            <div class="col-md-6">
                <div class="banner-ad-wrapper overflow-hidden rounded-4 shadow-sm hover-zoom">
                    <img src="./assets/discount.jpg" alt="iPhone Discount" class="img-fluid">
                </div>
            </div>

            <div class="col-md-6">
                <div class="banner-ad-wrapper overflow-hidden rounded-4 shadow-sm hover-zoom">
                    <img src="./assets/freeship.jpg" alt="Apple Free Shipping" class="img-fluid">
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    /* Khung banner: Cao 250px là đẹp, che hết phần trắng thừa */
    .banner-ad-wrapper {
        height: 150px; 
        background-color: #fbfbfd; 
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 20px !important; /* Bo góc kiểu Apple mượt hơn */
        transition: 0.4s ease;
    }

    .banner-ad-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Quan trọng: Phủ kín khung, không để lòi trắng */
        object-position: center; 
        transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1);
    }

    /* Hiệu ứng zoom cực mượt */
    .hover-zoom:hover {
        box-shadow: 0 15px 30px rgba(0,0,0,0.12) !important;
    }

    .hover-zoom:hover img {
        transform: scale(1.05);
    }

    /* Responsive cho điện thoại (Oppo của ông) */
    @media (max-width: 768px) {
        .banner-ad-wrapper {
            height: 180px; /* Thu nhỏ chiều cao trên mobile cho cân đối */
            border-radius: 15px !important;
        }
    }
</style>