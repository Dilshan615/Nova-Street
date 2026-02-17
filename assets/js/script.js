document.addEventListener("DOMContentLoaded", function () {
  // Initialize AOS
  AOS.init({
    duration: 800,
    easing: "ease-in-out",
    once: true,
    mirror: false,
  });

  // Navbar scroll effect
  const navbar = document.getElementById("mainNav");
  window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
      navbar.classList.add("scrolled");
    } else {
      navbar.classList.remove("scrolled");
    }
  });

  // ADVANCED SEARCH TOGGLE
  const searchBtn = document.querySelector('.bi-search').parentElement;
  const searchOverlay = document.getElementById('searchOverlay');
  const searchClose = document.getElementById('searchClose');

  if (searchBtn && searchOverlay) {
    searchBtn.addEventListener('click', function () {
      searchOverlay.classList.add('active');
      searchOverlay.querySelector('input').focus();
      document.body.style.overflow = 'hidden';
    });
  }

  if (searchClose && searchOverlay) {
    searchClose.addEventListener('click', function () {
      searchOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    });
  }

  // Close on Escape
  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
      searchOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  });

  // CART & WISHLIST LOGIC
  let cart = JSON.parse(localStorage.getItem('novastreet-cart')) || [];
  let wishlist = JSON.parse(localStorage.getItem('novastreet-wishlist')) || [];

  const updateUICounters = () => {
    const cartBadges = document.querySelectorAll('.bi-bag + .badge');
    const favBadges = document.querySelectorAll('.bi-heart + .badge');

    cartBadges.forEach(badge => badge.textContent = cart.length);
    favBadges.forEach(badge => badge.textContent = wishlist.length);
  };

  const showToast = (msg) => {
    let toast = document.querySelector('.toast-msg');
    if (!toast) {
      toast = document.createElement('div');
      toast.className = 'toast-msg';
      document.body.appendChild(toast);
    }
    toast.textContent = msg;
    toast.classList.add('status-active');
    setTimeout(() => toast.classList.remove('status-active'), 3000);
  };

  const getProductData = (btn) => {
    const card = btn.closest('.product-card');
    return {
      id: Date.now() + Math.random(),
      name: card.querySelector('h5').textContent,
      price: card.querySelector('h6')?.textContent || "$120.00",
      img: card.querySelector('img').src
    };
  };

  // Add to Cart
  document.querySelectorAll('.cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      cart.push(getProductData(this));
      localStorage.setItem('novastreet-cart', JSON.stringify(cart));
      updateUICounters();
      renderCart();
      showToast('Added to Cart Bag!');
    });
  });

  // Favorite Toggle
  document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const product = getProductData(this);
      const index = wishlist.findIndex(item => item.name === product.name);

      if (index === -1) {
        wishlist.push(product);
        showToast('Added to Wishlist!');
        this.querySelector('i').classList.replace('bi-heart', 'bi-heart-fill');
      } else {
        wishlist.splice(index, 1);
        showToast('Removed from Wishlist');
        this.querySelector('i').classList.replace('bi-heart-fill', 'bi-heart');
      }
      localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
      updateUICounters();
      renderWishlist();
    });
  });

  // Drawer Selectors
  const cartDrawer = document.getElementById('cartDrawer');
  const wishlistDrawer = document.getElementById('wishlistDrawer');
  const drawerOverlay = document.getElementById('drawerOverlay');

  // Open Cart
  document.querySelectorAll('.bi-bag').forEach(icon => {
    icon.parentElement.addEventListener('click', (e) => {
      e.preventDefault();
      cartDrawer.classList.add('active');
      drawerOverlay.classList.add('active');
      renderCart();
    });
  });

  // Open Wishlist
  document.querySelectorAll('.bi-heart').forEach(icon => {
    if (!icon.closest('.product-card')) { // Only main navbar heart
      icon.parentElement.addEventListener('click', (e) => {
        e.preventDefault();
        wishlistDrawer.classList.add('active');
        drawerOverlay.classList.add('active');
        renderWishlist();
      });
    }
  });

  const closeDrawers = () => {
    cartDrawer?.classList.remove('active');
    wishlistDrawer?.classList.remove('active');
    drawerOverlay?.classList.remove('active');
  };

  document.getElementById('cartClose')?.addEventListener('click', closeDrawers);
  document.getElementById('wishlistClose')?.addEventListener('click', closeDrawers);
  drawerOverlay?.addEventListener('click', closeDrawers);

  const renderCart = () => {
    const container = document.querySelector('.cart-items-container');
    const totalEl = document.getElementById('cartTotalAmount');
    if (!container) return;

    if (cart.length === 0) {
      container.innerHTML = '<div class="text-center py-5"><i class="bi bi-bag-x display-1 opacity-10"></i><p class="mt-3 text-muted">Bag is empty</p></div>';
      if (totalEl) totalEl.textContent = '$0.00';
      return;
    }

    let total = 0;
    container.innerHTML = cart.map((item, index) => {
      total += parseFloat(item.price.replace('$', ''));
      return `
            <div class="drawer-item">
                <img src="${item.img}" class="drawer-item-img">
                <div class="flex-grow-1">
                    <h6 class="mb-1">${item.name}</h6>
                    <p class="small text-muted mb-2">${item.price}</p>
                    <button class="btn btn-sm text-danger p-0 small" onclick="removeFromCart(${index})">Remove</button>
                </div>
            </div>`;
    }).join('');
    if (totalEl) totalEl.textContent = `$${total.toFixed(2)}`;
  };

  const renderWishlist = () => {
    const container = document.querySelector('.wishlist-items-container');
    if (!container) return;

    if (wishlist.length === 0) {
      container.innerHTML = '<div class="text-center py-5"><i class="bi bi-heart display-1 opacity-10"></i><p class="mt-3 text-muted">Wishlist is empty</p></div>';
      return;
    }

    container.innerHTML = wishlist.map((item, index) => `
        <div class="drawer-item">
            <img src="${item.img}" class="drawer-item-img">
            <div class="flex-grow-1">
                <h6 class="mb-1">${item.name}</h6>
                <p class="small text-muted mb-2">${item.price}</p>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-dark rounded-pill px-2 py-0" style="font-size: 0.7rem" onclick="moveToCart(${index})">Add to Bag</button>
                    <button class="btn btn-sm text-danger p-0" onclick="removeFromWishlist(${index})">Remove</button>
                </div>
            </div>
        </div>`).join('');
  };

  window.removeFromCart = (index) => {
    cart.splice(index, 1);
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    updateUICounters();
    renderCart();
    renderCartPage(); // Update cart page if open
    updateSummary(calculateSubtotal()); // Update summary on cart page
  };

  window.removeFromWishlist = (index) => {
    wishlist.splice(index, 1);
    localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
    updateUICounters();
    renderWishlist();
    renderWishlistPage(); // Update wishlist page if open
  };

  window.moveToCart = (index) => {
    cart.push(wishlist[index]);
    wishlist.splice(index, 1);
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
    updateUICounters();
    renderCart();
    renderWishlist();
    renderCartPage(); // Update cart page if open
    renderWishlistPage(); // Update wishlist page if open
    showToast('Moved to Shopping Bag!');
  };

  const calculateSubtotal = () => {
    let subtotal = 0;
    cart.forEach(item => {
      subtotal += parseFloat(item.price.replace('$', ''));
    });
    return subtotal;
  };

  updateUICounters();
  renderCart();
  renderWishlist();


  // SMOOTH SCROLLING FOR NAV LINKS
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        window.scrollTo({
          top: target.offsetTop - 80,
          behavior: "smooth",
        });
      }
    });
  });

  // CATEGORY TABS SIMULATION
  const categoryLinks = document.querySelectorAll(".category-tabs .nav-link");
  categoryLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      categoryLinks.forEach((l) => l.classList.remove("active"));
      this.classList.add("active");

      // Here you would normally filter products, but for design we just toggle active state
    });
  });

  // NEWSLETTER SUCCESS SIMULATION
  const newsletterForm = document.querySelector(".newsletter-form");
  if (newsletterForm) {
    newsletterForm.addEventListener("submit", function (e) {
      e.preventDefault();
      const input = this.querySelector("input");
      const btn = this.querySelector("button");

      const originalText = btn.innerHTML;
      btn.innerHTML = "Subscribed!";
      btn.classList.replace("btn-premium", "btn-success");
      input.value = "";

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.replace("btn-success", "btn-premium");
      }, 3000);
    });
  }

  // CONTACT FORM SIMULATION
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button');
      const originalText = btn.innerHTML;

      btn.innerHTML = '<i class="bi bi-check2-circle"></i> Message Sent!';
      btn.classList.replace('btn-premium', 'btn-success');

      this.reset();

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.replace('btn-success', 'btn-premium');
      }, 3000);
    });
  }

  // PAGE SPECIFIC RENDERING
  const renderCartPage = () => {
    const listBody = document.getElementById('cartPageList');
    if (!listBody) return;

    if (cart.length === 0) {
      listBody.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-bag-x display-1 opacity-10"></i>
                <h3 class="mt-4">Your bag is empty</h3>
                <a href="products.html" class="btn btn-premium mt-3 rounded-pill">Continue Shopping</a>
            </div>`;
      updateSummary(0);
      return;
    }

    let subtotal = 0;
    listBody.innerHTML = cart.map((item, index) => {
      subtotal += parseFloat(item.price.replace('$', ''));
      return `
            <div class="cart-page-item d-flex align-items-center">
                <img src="${item.img}" class="cart-item-image me-4">
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1">${item.name}</h5>
                    <p class="text-muted mb-0">Premium Fashion Item</p>
                    <h5 class="text-accent-color mt-2">${item.price}</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-light rounded-circle" onclick="removeFromCart(${index})"><i class="bi bi-trash text-danger"></i></button>
                </div>
            </div>`;
    }).join('');
    updateSummary(subtotal);
  };

  const updateSummary = (subtotal) => {
    const tax = subtotal * 0.02;
    const total = subtotal + tax;

    if (document.getElementById('summarySubtotal')) {
      document.getElementById('summarySubtotal').textContent = `$${subtotal.toFixed(2)}`;
      document.getElementById('summaryTax').textContent = `$${tax.toFixed(2)}`;
      document.getElementById('summaryTotal').textContent = `$${total.toFixed(2)}`;
    }
  };

  const renderWishlistPage = () => {
    const grid = document.getElementById('wishlistPageGrid');
    if (!grid) return;

    if (wishlist.length === 0) {
      grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-heart display-1 opacity-10"></i>
                <h3 class="mt-4">Wishlist is empty</h3>
                <a href="products.html" class="btn btn-premium mt-3 rounded-pill">Explore Collections</a>
            </div>`;
      return;
    }

    grid.innerHTML = wishlist.map((item, index) => `
        <div class="col-md-4 col-lg-3">
            <div class="wishlist-page-card">
                <div class="wishlist-img-box">
                    <img src="${item.img}" alt="Product">
                    <button class="remove-wish-btn" onclick="removeFromWishlist(${index})"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-4">
                    <h6 class="fw-bold mb-1">${item.name}</h6>
                    <p class="text-accent-color fw-bold mb-3">${item.price}</p>
                    <button class="btn btn-dark w-100 rounded-pill py-2" onclick="moveToCart(${index})">Add to Bag</button>
                </div>
            </div>
        </div>`).join('');
  };

  // Run page-specific renders
  renderCartPage();
  renderWishlistPage();
});
