const products = [
  { id: 1,  name: "Backpack",                        price: 59.99,   oldPrice: 89.99,   image: "images/product1.png",  sale: true },
  { id: 2,  name: "Men's Tshirt",                    price: 129.0,   oldPrice: 200,     image: "images/product2.png",  sale: true },
  { id: 3,  name: "Men's Cotton Jacket",             price: 74.5,    oldPrice: 99.0,    image: "images/product3.png",  sale: true },
  { id: 4,  name: "Men's Long Sleeve Tshirt",        price: 39.0,    oldPrice: 55,      image: "images/product4.png",  sale: true },
  { id: 5,  name: "Twisted Silver Bracelet",         price: 24.99,   oldPrice: 34.99,   image: "images/product5.png",  sale: true },
  { id: 6,  name: "Simple Silver ring",              price: 45.0,    oldPrice: 60,      image: "images/product6.png",  sale: true },
  { id: 7,  name: "Diamond ring",                    price: 45.0,    oldPrice: 100,     image: "images/product7.png",  sale: true },
  { id: 8,  name: "Copper ring",                     price: 45.0,    oldPrice: 60,      image: "images/product8.png",  sale: true },
  { id: 9,  name: "WD Elements Portable Hard Drive", price: 200.0,   oldPrice: 300,     image: "images/product9.png",  sale: true },
  { id: 10, name: "Solid State Drive",               price: 350.0,   oldPrice: 500,     image: "images/product10.png", sale: true },
  { id: 11, name: "A55 SSD",                         price: 45.0,    oldPrice: 80,      image: "images/product11.png", sale: true },
  { id: 12, name: "WD Hard Drive",                   price: 230.0,   oldPrice: 300,     image: "images/product12.png", sale: true },
  { id: 13, name: "Desktop Monitor",                 price: 5000.0,  oldPrice: 10000,   image: "images/product13.png", sale: true },
  { id: 14, name: "QLED Gaming Monitor",             price: 30000.0, oldPrice: 50000,   image: "images/product14.png", sale: true },
  { id: 15, name: "Winter Windproof Jacket",         price: 30.0,    oldPrice: 45,      image: "images/product15.png", sale: true },
  { id: 16, name: "Black Ladies Jacket",             price: 50.0,    oldPrice: 70,      image: "images/product16.png", sale: true },
  { id: 17, name: "Blue Jacket with Tshirt",         price: 144.0,   oldPrice: 200,     image: "images/product17.png", sale: true },
  { id: 18, name: "White Tshirt",                    price: 100.0,   oldPrice: 200,     image: "images/product18.png", sale: true },
  { id: 19, name: "Half Sleeve Tshirt for Women",    price: 15.0,    oldPrice: 30,      image: "images/product19.png", sale: true },
  { id: 20, name: "Combo Tshirt and Pant",           price: 50.0,    oldPrice: 80,      image: "images/product20.png", sale: true },
];

const grid = document.getElementById("productGrid");

products.forEach((p) => {
  const card = document.createElement("article");
  card.className = "product-card";

  card.innerHTML = `
    ${p.sale ? '<span class="sale-tag">Sale</span>' : ""}
    <div class="product-img">
      <img src="${p.image}" alt="${p.name}" loading="lazy" />
    </div>
    <div class="product-info">
      <h3 class="product-name">${p.name}</h3>
      <p class="product-price">
        <span class="price-now">${formatPrice(p.price)}</span>
        ${p.oldPrice ? `<span class="price-old">${formatPrice(p.oldPrice)}</span>` : ""}
      </p>
      <button class="add-btn" type="button">Add to cart</button>
    </div>
  `;

  const btn = card.querySelector(".add-btn");
  btn.addEventListener("click", () => {
    addToCart(p);
    btn.textContent = "Added";
    setTimeout(() => (btn.textContent = "Add to cart"), 900);
  });

  grid.appendChild(card);
});