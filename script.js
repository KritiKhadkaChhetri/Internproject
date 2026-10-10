const CURRENCY = "$"; // use "Rs. " if your prices are in rupees

function formatPrice(value) {
  return CURRENCY + value.toFixed(2);
}

/* ---------- Cart storage ---------- */
function getCart() {
  try {
    return JSON.parse(localStorage.getItem("cart")) || [];
  } catch (e) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem("cart", JSON.stringify(cart));
  renderBadge();
}

function getCount() {
  return getCart().reduce((sum, item) => sum + item.qty, 0);
}

function addToCart(product) {
  const cart = getCart();
  const found = cart.find((item) => item.id === product.id);
  if (found) {
    found.qty += 1;
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      oldPrice: product.oldPrice || null,
      image: product.image,
      qty: 1,
    });
  }
  saveCart(cart);
}

function changeQty(id, delta) {
  const cart = getCart();
  const item = cart.find((i) => i.id === id);
  if (!item) return;
  item.qty += delta;
  saveCart(cart.filter((i) => i.qty > 0));
}

function removeItem(id) {
  saveCart(getCart().filter((i) => i.id !== id));
}

function clearCart() {
  saveCart([]);
}

/* ---------- Navbar badge ---------- */
const badge = document.getElementById("badge");

function renderBadge() {
  if (!badge) return;
  const count = getCount();
  badge.textContent = count;
  badge.classList.toggle("hidden", count === 0);
}

renderBadge();

/* ---------- "Get started" button (index.html only) ---------- */
const getStarted = document.getElementById("getStarted");
if (getStarted) {
  getStarted.addEventListener("click", () => {
    window.location.href = "products.html";
  });
}