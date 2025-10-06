// ===== AOS =====
document.addEventListener('DOMContentLoaded', () => {
  if (window.AOS) AOS.init({ duration: 1000, once: true });
});

// ===== Navbar scroll shadow (supports .custom-navbar or .navbar) =====
const navbar = document.querySelector('.custom-navbar') || document.querySelector('.navbar');
if (navbar) {
  const onScroll = () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  };
  onScroll();
  window.addEventListener('scroll', onScroll);
}

// ===== Smooth scroll for same-page links =====
document.querySelectorAll('a.nav-link[href^="#"]').forEach(link => {
  link.addEventListener('click', e => {
    const target = document.querySelector(link.getAttribute('href'));
    if (!target) return;
    e.preventDefault();

    const y = target.getBoundingClientRect().top + window.pageYOffset - 70; // adjust for fixed navbar
    window.scrollTo({ top: y, behavior: 'smooth' });

    // collapse mobile menu after click (Bootstrap 5)
    const openCollapse = document.querySelector('.navbar-collapse.show');
    if (openCollapse && window.bootstrap) {
      new bootstrap.Collapse(openCollapse).hide();
    }
  });
});

// ===== Loader hide with delay =====
window.addEventListener('load', () => {
  const loader = document.getElementById('loader');
  const content = document.getElementById('content'); // optional
  if (!loader) return;

  // change 2000 to adjust how long the loader stays visible
  setTimeout(() => {
    loader.classList.add('hidden');
    // show main content after fade-out (only if you’re hiding it initially)
    setTimeout(() => {
      if (content) content.style.display = 'block';
    }, 1000); // match your CSS transition
  }, 2000);
});

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
      const productCard = button.closest('.product-card');
      const productName = productCard.querySelector('h5').textContent;

      // Remove existing toast for this product if any
      const existingToast = productCard.parentElement.querySelector('.external-toast');
      if (existingToast) existingToast.remove();

      // Create toast div
      const toastDiv = document.createElement('div');
      toastDiv.className = 'external-toast alert alert-success shadow-sm';
      toastDiv.textContent = `✅ ${productName} added to cart!`;

      productCard.parentElement.appendChild(toastDiv);

      // Get product card position
      const cardRect = productCard.getBoundingClientRect();
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
      const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

      // Position toast just below and to the right of product card
      toastDiv.style.position = 'absolute';
      toastDiv.style.top = (cardRect.bottom + scrollTop + 8) + 'px'; // 8px below the card
      toastDiv.style.left = (cardRect.right + scrollLeft - toastDiv.offsetWidth) + 'px'; // align to right edge of card
      toastDiv.style.zIndex = '9999';
      toastDiv.style.width = '200px';

      // Because offsetWidth may be zero now (element not yet visible), delay positioning left
      setTimeout(() => {
        const updatedCardRect = productCard.getBoundingClientRect();
        toastDiv.style.left = (updatedCardRect.right + scrollLeft - toastDiv.offsetWidth) + 'px';
      }, 0);

      // Auto-remove after 3 seconds
      setTimeout(() => {
        toastDiv.remove();
      }, 3000);
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const addToCartButtons = document.querySelectorAll(".add-to-cart");

  // Create Cart Bar
  const cartBar = document.createElement("div");
  cartBar.className = "position-fixed bottom-0 start-0 end-0 bg-success text-white d-flex justify-content-between align-items-center px-4 py-2";
  cartBar.style.zIndex = "9999";
  cartBar.style.display = "none";
  document.body.appendChild(cartBar);

  let cart = {
    items: 0,
    total: 0
  };

  addToCartButtons.forEach(button => {
    button.addEventListener("click", function () {
      // Check if this is a login button (means user is not logged in)
      if (this.textContent.includes('Login to Add to Cart')) {
        return; // Let the link handle the redirect
      }
      
      const productCard = this.closest(".product-card");

      // 🔐 Prevent adding multiple quantity controls
      if (productCard.querySelector(".quantity-control")) return;

      const priceText = productCard.querySelector(".price").innerText;
      const price = parseInt(priceText.replace(/[₹,]/g, "")); // Fix ₹1,000
      const productName = productCard.querySelector("h5").innerText;
      const productImage = productCard.querySelector("img").src;

      // ✅ Save product to localStorage
      updateLocalStorageCart(productName, price, productImage);

      // Create Quantity Control
      const quantityControl = document.createElement("div");
      quantityControl.className = "quantity-control d-flex align-items-center justify-content-center";
      quantityControl.setAttribute("data-price", price); // Store price for access later
      quantityControl.innerHTML = `
        <button class="btn btn-outline-danger btn-sm minus">–</button>
        <span class="quantity fw-bold mx-2">1</span>
        <button class="btn btn-outline-success btn-sm plus">+</button>
      `;
      this.replaceWith(quantityControl);

      let quantity = 1;
      cart.items += 1;
      cart.total += price;
      updateCartBar();

      // Show toast safely
      const toastEl = document.getElementById("cartToast");
      if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
      }

      const minusBtn = quantityControl.querySelector(".minus");
      const plusBtn = quantityControl.querySelector(".plus");
      const quantitySpan = quantityControl.querySelector(".quantity");

      plusBtn.addEventListener("click", () => {
        quantity++;
        quantitySpan.innerText = quantity;
        cart.items += 1;
        cart.total += price;
        updateCartBar();
        updateLocalStorageCart(productName, price, productImage, 1); // ✅ Update quantity +1
      });

      minusBtn.addEventListener("click", () => {
        if (quantity > 1) {
          quantity--;
          quantitySpan.innerText = quantity;
          cart.items -= 1;
          cart.total -= price;
          updateCartBar();
          updateLocalStorageCart(productName, price, productImage, -1); // ✅ Update quantity -1
        } else {
          quantityControl.replaceWith(button); // Put back "Add to Cart"
          cart.items -= 1;
          cart.total -= price;
          updateCartBar();
          removeFromLocalStorageCart(productName); // ✅ Remove from cart
        }
      });
    });
  });

  function updateCartBar() {
    if (cart.items > 0) {
      cartBar.innerHTML = `
        <div><strong>${cart.items} Item${cart.items > 1 ? "s" : ""} • ₹${cart.total}</strong></div>
        <a href="cart.php" class="btn btn-light btn-sm">View Cart</a>
      `;
      cartBar.style.display = "flex";
    } else {
      cartBar.style.display = "none";
    }
  }

  // ✅ Add/update product in localStorage
  function updateLocalStorageCart(name, price, image, quantityChange = 1) {
    let storedCart = JSON.parse(localStorage.getItem("vedalife_cart")) || [];
    const existingItem = storedCart.find(item => item.name === name);

    if (existingItem) {
      existingItem.quantity += quantityChange;
      if (existingItem.quantity <= 0) {
        // Remove item if quantity is 0 or less
        storedCart = storedCart.filter(item => item.name !== name);
      }
    } else {
      storedCart.push({
        name: name,
        price: price,
        quantity: 1,
        image: image
      });
    }

    localStorage.setItem("vedalife_cart", JSON.stringify(storedCart));
  }

  // ✅ Remove product from localStorage cart
  function removeFromLocalStorageCart(name) {
    let storedCart = JSON.parse(localStorage.getItem("vedalife_cart")) || [];
    storedCart = storedCart.filter(item => item.name !== name);
    localStorage.setItem("vedalife_cart", JSON.stringify(storedCart));
  }
});
// ===== Flip Card Functionality =====
document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll('.service-card-container');

  cards.forEach(cardContainer => {
    const card = cardContainer.querySelector('.service-card');
    const discover = cardContainer.querySelector('.discover-link');
    const back = cardContainer.querySelector('.back-link');

    if (discover && back && card) {
      discover.addEventListener('click', function(e) {
        e.preventDefault();
        card.classList.add('flip');
      });

      back.addEventListener('click', function(e) {
        e.preventDefault();
        card.classList.remove('flip');
      });
    }
  });
});
document.addEventListener('click', function(e) {
    const discover = e.target.closest('.discover-link');
    const back = e.target.closest('.back-link');
    if (discover) {
      e.preventDefault();
      discover.closest('.service-card').classList.add('flip');
    }
    if (back) {
      e.preventDefault();
      back.closest('.service-card').classList.remove('flip');
    }
  });

  // --- Dynamic height fix so rows don't overlap ---
  function sizeCardContainers() {
    document.querySelectorAll('.service-card-container').forEach(container => {
      const card = container.querySelector('.service-card');
      if (!card) return;
      const front = card.querySelector('.card-front');
      const back  = card.querySelector('.card-back');

      // Temporarily ensure both faces are measurable
      const prev = card.classList.contains('flip');
      card.classList.remove('flip');
      // Measure both faces’ natural heights
      const frontH = front ? front.offsetHeight : 0;
      card.classList.add('flip');
      const backH = back ? back.offsetHeight : 0;

      // Restore original state
      if (!prev) card.classList.remove('flip');

      const maxH = Math.max(frontH, backH);
      container.style.height = maxH + 'px';
    });
  }

  // Run after everything (including images) is loaded, and on resize
  window.addEventListener('load', sizeCardContainers);
  window.addEventListener('resize', () => {
    // Debounce a bit for performance
    clearTimeout(window.__cardResizeTimer);
    window.__cardResizeTimer = setTimeout(sizeCardContainers, 150);
  });

  // If any image loads late (e.g., cache miss), recalc heights
  document.querySelectorAll('img').forEach(img => {
    img.addEventListener('load', sizeCardContainers, { once: true });
  });