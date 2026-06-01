console.log("MAIN JS LOADED");

document.addEventListener("DOMContentLoaded", () => {

    if (window.cartHandlerLoaded) return;
    window.cartHandlerLoaded = true;

    document.addEventListener("click", async function (e) {

        const btn = e.target.closest(".add-to-cart, .main-add-to-cart");

        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        // منع الضغط المتكرر
        if (btn.dataset.loading === "true") return;

        btn.dataset.loading = "true";
        btn.disabled = true;

        try {

            const productId = btn.dataset.id;

            const qtyInput =
                document.getElementById("quantityInput");

            const qty = qtyInput
                ? parseInt(qtyInput.value) || 1
                : 1;

            const response = await fetch(
                "ajax_add_to_cart.php",
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                        "application/x-www-form-urlencoded"
                    },

                    body:
                        `id=${encodeURIComponent(productId)}&qty=${qty}`
                }
            );

            const data = await response.json();

            console.log(data);

            if (data.status === "success") {

                const cart =
                    document.getElementById("cart-count");

                if (cart) {
                    cart.textContent = data.count;
                }

                btn.innerHTML = "✓ تمت الإضافة";

                setTimeout(() => {
                    btn.innerHTML = "إضافة إلى الحقيبة";
                }, 1200);
            }

        } catch (error) {

            console.log("ERROR:", error);

        } finally {

            btn.disabled = false;
            btn.dataset.loading = "false";
        }

    });

});