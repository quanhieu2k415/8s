document.addEventListener("DOMContentLoaded", function () {
    /* ========================== */
    /* 1. XỬ LÝ SLIDER (BANNER)   */
    /* ========================== */
    const slider = document.querySelector(".slider");
    const images = document.querySelectorAll(".slider img");
    const nextBtn = document.querySelector(".next");
    const prevBtn = document.querySelector(".prev");

    if (slider && images.length > 0) {
        let index = 0;
        let slideInterval; // Biến lưu timer

        const updateSlider = () => {
            slider.style.transform = `translateX(-${index * 100}%)`;
        };

        const startSlideTimer = () => {
            if (slideInterval) clearInterval(slideInterval); // Xóa timer cũ nếu có
            slideInterval = setInterval(() => {
                index++;
                if (index >= images.length) index = 0;
                updateSlider();
            }, 4000);
        };

        if (nextBtn) {
            nextBtn.onclick = () => {
                index++;
                if (index >= images.length) index = 0;
                updateSlider();
                startSlideTimer(); // Reset lại timer sau khi bấm
            };
        }

        if (prevBtn) {
            prevBtn.onclick = () => {
                index--;
                if (index < 0) index = images.length - 1;
                updateSlider();
                startSlideTimer(); // Reset lại timer sau khi bấm
            };
        }

        // Bắt đầu chạy slider
        startSlideTimer();
    }

    /* ========================== */
    /* 2. HIỆN Ô NHẬP QUỐC GIA KHÁC */
    /* ========================== */
    const quocGiaSelect = document.getElementById("quoc_gia");
    const quocGiaKhacBox = document.getElementById("quoc_gia_khac_box");

    if (quocGiaSelect && quocGiaKhacBox) {
        quocGiaSelect.addEventListener("change", function () {
            quocGiaKhacBox.style.display = (this.value === "Khác") ? "block" : "none";
        });
    }

    /* ========================== */
    /* 3. SCROLL MƯỢT ĐẾN FORM    */
    /* ========================== */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    /* ========================== */
    /* 4. VALIDATE DỮ LIỆU NHẬP   */
    /* ========================== */
    
    // --- Họ Tên (Viết hoa chữ cái đầu) ---
    const hoTenInput = document.getElementById("ho_ten");
    if (hoTenInput) {
        hoTenInput.addEventListener("input", function () {
            let start = this.selectionStart;
            let value = this.value;
            // Cho phép ký tự chữ và khoảng trắng
            value = value.replace(/[^\p{L}\s]/gu, "");
            // Xóa khoảng trắng thừa liên tiếp
            value = value.replace(/\s{2,}/g, " ");
            // Viết hoa chữ cái đầu
            value = value.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
            
            this.value = value;
            this.setSelectionRange(start, start);
        });
    }

    // --- Năm Sinh (Chỉ số) ---
    const namSinhInput = document.getElementById("nam_sinh");
    if (namSinhInput) {
        namSinhInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, ""); 
        });

        // Chỉ kiểm tra logic năm sinh khi người dùng đã nhập xong (rời khỏi ô input)
        namSinhInput.addEventListener("blur", function () { 
            let year = parseInt(this.value);
            const currentYear = new Date().getFullYear();
            
            if (!isNaN(year) && this.value.length === 4) {
                if (year > currentYear) this.value = currentYear; 
                if (year < 1900) this.value = 1900; 
            }
        });
    }

    // --- Số Điện Thoại (Chỉ số, max 11) ---
    const sdtInput = document.getElementById("sdt");
    if (sdtInput) {
        sdtInput.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, "").substring(0, 11);
        });
    }

    /* ========================== */
    /* 5. GỬI FORM (AJAX)         */
    /* ========================== */
    const form = document.getElementById('userRegistrationForm');
    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const messageDisplay = document.getElementById('message');
            if (messageDisplay) {
                messageDisplay.textContent = '⏳ Đang gửi thông tin...';
                messageDisplay.style.color = '#0f75bd';
            }

            // Xử lý Logic Quốc Gia
            let selectedQuocGia = document.getElementById('quoc_gia').value;
            const quocGiaKhacInput = document.getElementById('quoc_gia_khac');
            
            if (selectedQuocGia === "Khác") {
                const customVal = quocGiaKhacInput ? quocGiaKhacInput.value.trim() : "";
                if(customVal !== "") {
                    selectedQuocGia = customVal;
                } else {
                    // Nếu chọn Khác mà không nhập gì thì báo lỗi hoặc mặc định là "Khác"
                    if(messageDisplay) {
                        messageDisplay.textContent = '❌ Vui lòng nhập tên quốc gia mong muốn.';
                        messageDisplay.style.color = 'red';
                    }
                    if(quocGiaKhacInput) quocGiaKhacInput.focus();
                    return; // Dừng lại không gửi
                }
            }

            const data = {
                ho_ten: document.getElementById('ho_ten').value.trim(),
                nam_sinh: document.getElementById('nam_sinh').value.trim(),
                dia_chi: document.getElementById('dia_chi').value.trim(),
                chuong_trinh: document.getElementById('chuong_trinh').value,
                quoc_gia: selectedQuocGia, 
                sdt: document.getElementById('sdt').value.trim()
            };

            // Gửi dữ liệu
            fetch('http://localhost/web8s/backend_api/insert.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (messageDisplay) {
                    if (result.status) {
                        messageDisplay.textContent = '✅ Đăng ký thành công! Chúng tôi sẽ liên hệ sớm.';
                        messageDisplay.style.color = 'green';
                        form.reset();
                        // Ẩn ô nhập khác nếu đang hiện
                        if (quocGiaKhacBox) quocGiaKhacBox.style.display = 'none';
                    } else {
                        messageDisplay.textContent = '❌ Lỗi: ' + result.message;
                        messageDisplay.style.color = 'red';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (messageDisplay) {
                    messageDisplay.textContent = '❌ Không kết nối được tới máy chủ.';
                    messageDisplay.style.color = 'red';
                }
            });
        });
    }
});