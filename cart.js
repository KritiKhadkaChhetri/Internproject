const VAT_RATE = 0.13; // 13% VAT. Change to match your country, e.g. 0.10 for 10%

const itemsEl = document.getElementById("cartItems");
const emptyEl = document.getElementById("cartEmpty");
const summaryEl = document.getElementById("cartSummary");
const pillEl = document.getElementById("discountPill");

function renderCart() {
  const cart = getCart();
  const hasItems = cart.length > 0;

  itemsEl.innerHTML = "";
  itemsEl.hidden = !hasItems;
  emptyEl.hidden = hasItems;
  summaryEl.hidden = !hasItems;

  let subtotal = 0;      // total at original (old) prices
  let discount = 0;      // total saved on sale items
  let discountedItems = 0;

  cart.forEach((item) => {
    const was = item.oldPrice && item.oldPrice > item.price ? item.oldPrice : item.price;
    const saved = (was - item.price) * item.qty;

    subtotal += was * item.qty;
    if (saved > 0) {
      discount += saved;
      discountedItems += 1;
    }

    const row = document.createElement("article");
    row.className = "cart-row";
    row.innerHTML = `
      <img class="cart-img" src="${item.image}" alt="${item.name}" />
      <div class="cart-info">
        <h3 class="cart-name">${item.name}</h3>
        <p class="cart-meta">
          ${formatPrice(item.price)}
          ${item.oldPrice ? `<span class="price-old">${formatPrice(item.oldPrice)}</span>` : ""}
        </p>
      </div>
      <input class="qty-input" type="number" min="1" step="1"
             value="${item.qty}" data-id="${item.id}" aria-label="Quantity for ${item.name}" />
      <button type="button" class="trash-btn" data-id="${item.id}" aria-label="Remove ${item.name}">
        <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
          <path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/>
        </svg>
      </button>
    `;
    itemsEl.appendChild(row);
  });

  const taxable = subtotal - discount;
  const vat = taxable * VAT_RATE;
  const total = taxable + vat;

  document.getElementById("sumSubtotal").textContent = formatPrice(subtotal);
  document.getElementById("sumVat").textContent = formatPrice(vat);
  document.getElementById("sumDiscount").textContent = "-" + formatPrice(discount);
  document.getElementById("sumTotal").textContent = formatPrice(total);

  pillEl.hidden = discountedItems === 0;
  document.getElementById("discountText").textContent =
    discountedItems + (discountedItems === 1 ? " Discount Applied" : " Discounts Applied");
}

// Remove (trash) button
itemsEl.addEventListener("click", (e) => {
  const btn = e.target.closest(".trash-btn");
  if (!btn) return;
  removeItem(Number(btn.dataset.id));
  renderCart();
});

// Quantity box
itemsEl.addEventListener("change", (e) => {
  if (!e.target.classList.contains("qty-input")) return;

  const id = Number(e.target.dataset.id);
  const item = getCart().find((i) => i.id === id);
  if (!item) return;

  let newQty = Math.floor(Number(e.target.value));
  if (!newQty || newQty < 1) newQty = 1;

  changeQty(id, newQty - item.qty);
  renderCart();
});

document.getElementById("checkoutBtn").addEventListener("click", () => {
  alert("Checkout is not connected yet.");
});

renderCart();