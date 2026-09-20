document.addEventListener("DOMContentLoaded", () => {
    // 1. Khởi tạo Icon từ thư viện Lucide (nếu có trên trang)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    loadProducts();

    // 2. Chức năng chuyển Tab (Áp dụng cho Hồ sơ, Chi tiết SP)
    const tabBtns = document.querySelectorAll('.pd-tab-btn');
    const tabContents = document.querySelectorAll('.pd-tab-content');

    if (tabBtns.length > 0) {
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Xóa active hiện tại
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                // Thêm active mới
                btn.classList.add('active');
                const targetId = btn.getAttribute('data-target');
                const targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    // 3. Xử lý click chuyển trang (SPA) cho file app_store.html
    const navLinks = document.querySelectorAll('[data-navigate]');
    navLinks.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-navigate');
            navigateSPA(target);
        });
    });
});

// Hàm chuyển form Đăng nhập / Đăng ký
function switchForm(target) {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (loginForm && registerForm) {
        if (target === 'register') {
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
        } else {
            registerForm.classList.remove('active');
            loginForm.classList.add('active');
        }
    }
}

// Hàm Ẩn/Hiện mật khẩu
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        if (input.type === "password") {
            input.type = "text";
            input.nextElementSibling.innerHTML = '<i data-lucide="eye-off"></i>';
        } else {
            input.type = "password";
            input.nextElementSibling.innerHTML = '<i data-lucide="eye"></i>';
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

// Hàm điều hướng SPA (Dùng riêng cho file gom nhóm như app_store)
function navigateSPA(pageId) {
    const pages = document.querySelectorAll('.page-view');
    pages.forEach(page => page.classList.remove('active'));

    const navs = document.querySelectorAll('.nav-menu a');
    navs.forEach(nav => nav.classList.remove('active'));

    const targetPage = document.getElementById('page-' + pageId);
    if (targetPage) targetPage.classList.add('active');

    const targetNav = document.querySelector(`.nav-menu a[data-navigate="${pageId}"]`);
    if (targetNav) targetNav.classList.add('active');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

//xem chi tiết sản phẩm
function prod_detail(IDproduct) {
    window.location.href = "product_detail.html?id=" + IDproduct;
}
// Sửa ngày 8/9 15:29 chỉnh sửa từ web tĩnh thành web động, áp dụng database cho phần đăng nhập 

// ======================================================
// THESECOND V1 - AUTHENTICATION
// Backend session based authentication
// ======================================================

const API_BASE = "/TheSecondV1/api";


// ======================================================
// LOGIN
// ======================================================

async function handleLogin(event) {

    event.preventDefault();

    const email =
        document.getElementById("login-email")
            .value
            .trim()
            .toLowerCase();

    const password =
        document.getElementById("login-pw")
            .value;

    if (!email || !password) {
        alert("Vui lòng nhập đầy đủ thông tin.");
        return false;
    }

    try {

        const response = await fetch(
            `${API_BASE}/auth/login_user.php`,
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                credentials: "include",

                body: JSON.stringify({
                    email,
                    password
                })
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            alert(
                data.message ||
                "Đăng nhập thất bại."
            );

            return false;
        }

        window.location.href =
            "profile.php";

    } catch (error) {

        console.error(
            "Login error:",
            error
        );

        alert(
            "Không thể kết nối đến máy chủ."
        );
    }

    return false;
}


// ======================================================
// REGISTER
// ======================================================

async function handleRegister(event) {

    event.preventDefault();

    const fullName =
        document.getElementById("reg-name")
            .value
            .trim();

    const email =
        document.getElementById("reg-email")
            .value
            .trim()
            .toLowerCase();

    const password =
        document.getElementById("reg-pw")
            .value;

    if (!fullName || !email || !password) {
        alert(
            "Vui lòng nhập đầy đủ thông tin."
        );

        return false;
    }
    if (password.length < 6) {
        alert(
            "Mật khẩu phải có ít nhất 6 ký tự."
        );

        return false;
    }
    try {

        const response = await fetch(
            `${API_BASE}/auth/register.php`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "include",
                body: JSON.stringify({
                    full_name: fullName,
                    email: email,
                    password: password
                })
            }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {

            alert(
                data.message ||
                "Đăng ký thất bại."
            );

            return false;
        }

        alert(
            "Đăng ký tài khoản thành công."
        );

        switchAuthForm("login");

        document.getElementById(
            "login-email"
        ).value = email;

        document.getElementById(
            "login-pw"
        ).focus();

    } catch (error) {

        console.error(
            "Register error:",
            error
        );

        alert(
            "Không thể kết nối đến máy chủ."
        );
    }

    return false;
}


// ======================================================
// LOGOUT
// ======================================================

async function logout() {

    try {

        await fetch(
            `${API_BASE}/auth/logout.php`,
            {
                method: "POST",
                credentials: "include"
            }
        );

    } catch (error) {

        console.error(
            "Logout error:",
            error
        );

    } finally {

        window.location.href =
            "auth.html";
    }
}
// Sửa ngày 8/9 15:29 chỉnh sửa từ web tĩnh thành web động, áp dụng database cho phần đăng nhập 



// sửa ngày 8/9 16:16 thêm API
// ======================================================
// THESECOND V1 - PRODUCT SYSTEM
// ======================================================

async function loadProducts() {

    const productList =
        document.getElementById("product-list");

    const loading =
        document.getElementById("product-loading");

    const empty =
        document.getElementById("product-empty");

    if (!productList) {
        return;
    }

    try {

        if (loading) {
            loading.style.display = "block";
        }

        if (empty) {
            empty.style.display = "none";
        }

        const response = await fetch(
            "../api/products/list.php",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const data =
            await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.message ||
                "Không thể tải sản phẩm."
            );
        }

        const products =
            data.products || [];

        if (loading) {
            loading.style.display = "none";
        }

        if (products.length === 0) {

            if (empty) {
                empty.style.display = "block";
            }

            return;
        }

        renderProducts(products);

    } catch (error) {

        console.error(
            "Product API error:",
            error
        );

        if (loading) {
            loading.style.display = "none";
        }

        productList.innerHTML = `
            <div class="product-error">
                <i data-lucide="triangle-alert"></i>

                <h3>
                    Không thể tải sản phẩm
                </h3>

                <p>
                    Vui lòng thử lại sau.
                </p>

                <button
                    type="button"
                    onclick="loadProducts()"
                >
                    Thử lại
                </button>
            </div>
        `;

        if (
            typeof lucide !== "undefined"
        ) {
            lucide.createIcons();
        }
    }
}
// sửa ngày 8/9 16:16 thêm API


// sửa ngày 8/9 16:16 thêm loadProducts() để hiển thị sản phẩm từ database
function renderProducts(products) {

    const productList =
        document.getElementById("product-list");

    if (!productList) {
        return;
    }

    productList.innerHTML =
        products.map(product => {

            const image =
                product.image ||
                "../assets/images/product-placeholder.png";

            const price =
                formatCurrency(product.price);

            const originalPrice =
                product.original_price
                    ? formatCurrency(
                        product.original_price
                    )
                    : "";

            const condition =
                formatCondition(
                    product.condition_type
                );

            return `
                <article
                    class="product-card"
                    data-product-id="${product.id}"
                >

                    <button
                        type="button"
                        class="product-image-wrap"
                        onclick="prod_detail(${product.id})"
                        aria-label="Xem ${escapeHTML(product.name)}"
                    >

                        <img
                            src="${escapeHTML(image)}"
                            alt="${escapeHTML(product.name)}"
                            loading="lazy"
                            onerror="this.src='../assets/images/product-placeholder.png'"
                        >

                    </button>

                    <div class="product-card-body">

                        <div class="product-category">
                            ${escapeHTML(
                                product.category_name || ""
                            )}
                        </div>

                        <h3 class="product-name">
                            ${escapeHTML(product.name)}
                        </h3>

                        <div class="product-condition">
                            ${condition}

                            ${
                                product.condition_score
                                    ? `
                                        <span>
                                            ${product.condition_score}/10
                                        </span>
                                    `
                                    : ""
                            }
                        </div>

                        <div class="product-price-row">

                            <strong class="product-price">
                                ${price}
                            </strong>

                            ${
                                originalPrice
                                    ? `
                                        <del class="product-original-price">
                                            ${originalPrice}
                                        </del>
                                    `
                                    : ""
                            }

                        </div>

                        <button
                            type="button"
                            class="product-detail-btn"
                            onclick="prod_detail(${product.id})"
                        >
                            Xem chi tiết
                        </button>

                    </div>

                </article>
            `;

        })
        .join("");

    if (
        typeof lucide !== "undefined"
    ) {
        lucide.createIcons();
    }
}
// sửa ngày 8/9 16:16 thêm loadProducts() để hiển thị sản phẩm từ database



// sửa ngày 8/9 16:18 thêm hàm phụ trợ
function formatCurrency(value) {

    const number =
        Number(value);

    if (Number.isNaN(number)) {
        return "Liên hệ";
    }

    return new Intl.NumberFormat(
        "vi-VN",
        {
            style: "currency",
            currency: "VND",
            maximumFractionDigits: 0
        }
    ).format(number);
}


function formatCondition(condition) {

    const conditions = {
        like_new: "Như mới",
        excellent: "Rất tốt",
        good: "Tốt",
        fair: "Khá",
        poor: "Đã qua sử dụng"
    };

    return conditions[condition]
        || "Đã qua sử dụng";
}


function escapeHTML(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
// sửa ngày 8/9 16:18 thêm hàm vụ phụ trợ
// sửa ngày 8/9 17:04 thêm hàm loadproduct detail 
async function loadProductDetail() {
    const params = new URLSearchParams(window.location.search);
    const productId = params.get("id");

    if (!productId) {
        document.getElementById("product-name").textContent =
            "Không tìm thấy sản phẩm";
        return;
    }

    try {
        const response = await fetch(
            `../api/products/detail.php?id=${encodeURIComponent(productId)}`,
            {
                method: "GET",
                credentials: "include"
            }
        );

        const data = await response.json();

        if (!data.success || !data.product) {
            throw new Error(data.message || "Không tìm thấy sản phẩm");
        }

        renderProductDetail(data.product);

    } catch (error) {
        console.error("Lỗi tải sản phẩm:", error);

        const nameElement = document.getElementById("product-name");

        if (nameElement) {
            nameElement.textContent = "Không thể tải sản phẩm";
        }
    }
}
// sửa ngày 8/9 17:04 thêm hàm loadproduct detail 
// sửa ngày 8/9 17:06 thêm hàm render product detail
function renderProductDetail(product) {

    const nameElement = document.getElementById("product-name");
    const brandElement = document.getElementById("product-brand");
    const priceElement = document.getElementById("product-price");
    const originalPriceElement =
        document.getElementById("product-original-price");

    const conditionElement =
        document.getElementById("product-condition");

    const descriptionElement =
        document.getElementById("product-description");

    const stockElement =
        document.getElementById("product-stock");

    if (nameElement) {
        nameElement.textContent = product.name || "Sản phẩm";
    }

    if (brandElement) {
        brandElement.textContent = product.brand || "Không xác định";
    }

    if (priceElement) {
        priceElement.textContent =
            formatCurrency(product.price);
    }

    if (originalPriceElement) {

        if (product.original_price) {
            originalPriceElement.textContent =
                formatCurrency(product.original_price);
        } else {
            originalPriceElement.textContent = "";
        }
    }

    if (conditionElement) {
        conditionElement.textContent =
            formatCondition(product.condition_type);
    }

    if (descriptionElement) {
        descriptionElement.textContent =
            product.description || "Chưa có mô tả.";
    }

    if (stockElement) {
        stockElement.textContent =
            `Còn ${product.stock} sản phẩm`;
    }

    renderProductImages(product.images || []);

    const addToCartButton =
        document.getElementById("add-to-cart-btn");

    if (addToCartButton) {

        addToCartButton.onclick = () => {
            addToCart(product.id, 1);
        };
    }
}
// sửa ngày 8/9 17:06 thêm hàm render product detail
// sửa ngày 8/9 17:06 thêm hàm render product images
function renderProductImages(images) {

    const container =
        document.getElementById("product-images");

    if (!container) return;

    if (!images.length) {
        container.innerHTML = `
            <img
                src="../assets/images/product-placeholder.png"
                alt="Sản phẩm"
                class="product-main-image"
            >
        `;
        return;
    }

    container.innerHTML = images.map(image => `
        <img
            src="${escapeHTML(image.image_url)}"
            alt="Ảnh sản phẩm"
            class="product-main-image"
        >
    `).join("");
}
// sửa ngày 9/9 0:52 thêm hàm 
async function loadCart() {
    const container = document.getElementById("cart-items");

    if (!container) return;

    try {
        const response = await fetch(
            "../api/cart/get.php",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const data = await response.json();

        if (response.status === 401) {
            container.innerHTML = `
                <div class="cart-empty">
                    <p>Vui lòng đăng nhập để xem giỏ hàng.</p>
                    <a href="auth.html">Đăng nhập</a>
                </div>
            `;

            return;
        }

        if (!data.success) {
            throw new Error(
                data.message || "Không thể tải giỏ hàng."
            );
        }

        renderCart(data.items || []);
        updateCartTotal(data.total || 0);

    } catch (error) {

        console.error("Lỗi tải giỏ hàng:", error);

        // Fallback: reload trang (cart.php render PHP sẵn) thay vì để màn hình lỗi
        if (window.location.pathname.includes("cart")) {
            window.location.reload();
            return;
        }

        container.innerHTML = `
            <div class="cart-empty">
                <p>Không thể tải giỏ hàng.</p>
                <p style="font-size:12px;color:#94a3b8">${error.message || ""}</p>
                <button onclick="loadCart()">Thử lại</button>
            </div>
        `;
    }
}
// sửa ngày 9/9 0:52 thêm hàm 

// sửa ngày 9/9 0:52 thêm hàm render cart
function renderCart(items) {

    const container =
        document.getElementById("cart-items");

    if (!container) return;

    if (!items || items.length === 0) {

        container.innerHTML = `
            <div class="cart-item-card" style="justify-content:center;text-align:center;flex-direction:column;gap:10px;padding:48px 20px;">
                <div style="font-size:40px;">🛒</div>
                <h3 style="margin:0;color:#0f172a;">Giỏ hàng đang trống</h3>
                <p style="margin:0;color:#64748b;font-size:14px;">Hãy khám phá các sản phẩm điện tử đã qua sử dụng.</p>
                <a href="see_all_pd.php" class="btn btn-outline" style="margin-top:8px;">Tiếp tục mua sắm</a>
            </div>
        `;

        updateCartTotal(0);

        return;
    }

    container.innerHTML = items.map(item => {

        // API có thể trả tên file thô (giống cột products.image_url) hoặc URL đầy đủ
        const raw = item.image_url || "";
        const image = raw
            ? (raw.startsWith("http") || raw.startsWith("../")
                ? raw
                : "../api/admin/product/" + raw)
            : null;

        return `
            <div class="cart-item-card"
                 data-cart-item-id="${item.cart_item_id}">

                <div class="cart-item-thumb">
                    ${image
                        ? `<img src="${escapeHTML(image)}" alt="${escapeHTML(item.name)}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">`
                        : "📱"}
                </div>

                <div class="cart-item-info">
                    <div class="cart-item-name">${escapeHTML(item.name)}</div>
                    <div class="cart-item-price">${formatCurrency(item.price)}</div>
                </div>

                <div class="qty-controls">
                    <button type="button" class="qty-btn"
                        onclick="changeCartQuantity(${item.cart_item_id}, ${item.quantity - 1})">−</button>
                    <input type="text" class="qty-input" value="${item.quantity}" readonly>
                    <button type="button" class="qty-btn"
                        onclick="changeCartQuantity(${item.cart_item_id}, ${item.quantity + 1})">+</button>
                </div>

                <div style="font-weight:900;color:#0f172a;min-width:110px;text-align:right;">
                    ${formatCurrency(item.subtotal)}
                </div>

                <button type="button" onclick="removeCartItem(${item.cart_item_id})"
                    title="Xóa" style="background:none;border:none;color:#ef4444;cursor:pointer;padding:6px;">
                    <i data-lucide="trash-2"></i>
                </button>

            </div>
        `;

    }).join("");

    if (typeof lucide !== "undefined") lucide.createIcons();
}
// sửa ngày 9/9 0:52 thêm hàm render cart

// sửa ngày 9/9 0:52 thêm hàm update cart total
function updateCartTotal(total) {

    const subtotal =
        document.getElementById("cart-subtotal");

    const totalElement =
        document.getElementById("cart-total");

    if (subtotal) {
        subtotal.textContent =
            formatCurrency(total);
    }

    if (totalElement) {
        totalElement.textContent =
            formatCurrency(total);
    }
}

async function changeCartQuantity(
    cartItemId,
    quantity
) {

    if (quantity <= 0) {
        await removeCartItem(cartItemId);
        return;
    }

    try {

        const response = await fetch(
            "../api/cart/update.php",
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type":
                        "application/json"
                },

                body: JSON.stringify({
                    cart_item_id: cartItemId,
                    quantity: quantity
                })
            }
        );

        const data = await response.json();

        if (!data.success) {
            alert(
                data.message ||
                "Không thể cập nhật giỏ hàng."
            );

            return;
        }

        if (window.location.pathname.includes("cart")) {
            window.location.reload();
            return;
        }
        await loadCart();

    } catch (error) {

        console.error(
            "Lỗi cập nhật giỏ hàng:",
            error
        );

        alert(
            "Có lỗi xảy ra khi cập nhật giỏ hàng."
        );
    }
}

async function removeCartItem(cartItemId) {

    if (!confirm(
        "Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?"
    )) {
        return;
    }

    try {

        const response = await fetch(
            "../api/cart/delete.php",
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type":
                        "application/json"
                },

                body: JSON.stringify({
                    cart_item_id: cartItemId
                })
            }
        );

        const data = await response.json();

        if (!data.success) {
            alert(
                data.message ||
                "Không thể xóa sản phẩm."
            );

            return;
        }

        if (window.location.pathname.includes("cart")) {
            window.location.reload();
            return;
        }
        await loadCart();

    } catch (error) {

        console.error(
            "Lỗi xóa sản phẩm:",
            error
        );

        alert(
            "Có lỗi xảy ra khi xóa sản phẩm."
        );
    }
}

async function addToCart(productId, quantity = 1) {

    try {

        const response = await fetch(
            "../api/cart/add.php",
            {
                method: "POST",

                credentials: "include",

                headers: {
                    "Content-Type":
                        "application/json"
                },

                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            }
        );

        const data = await response.json();
        if (response.status === 401) {
            alert(
                "Vui lòng đăng nhập trước khi thêm sản phẩm."
            );

            window.location.href =
                "auth.html";
            return;
        }
        if (!data.success) {
            alert(
                data.message ||
                "Không thể thêm sản phẩm.");
            return;
        }
        alert("Đã thêm sản phẩm vào giỏ hàng.");

    } catch (error) {

        console.error(
            "Lỗi thêm vào giỏ hàng:",
            error
        );

        alert(
            "Có lỗi xảy ra khi thêm vào giỏ hàng."
        );
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (
        window.location.pathname.includes("product_detail.html")
    ) {
        loadProductDetail();
    }

});