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
      if (window.location.pathname.endsWith("index.html") || window.location.pathname.endsWith("/") || window.location.pathname === "") {
        navbar.classList.remove("scrolled");
      }
    }
  });

  // Active navigation link detection based on path
  const currentPath = window.location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".navbar-nav .nav-link").forEach(link => {
    const href = link.getAttribute("href");
    if (href === currentPath || (currentPath === "index.html" && href.startsWith("index.html#"))) {
      link.classList.add("active");
    } else {
      link.classList.remove("active");
    }
  });

  // Drawer & Overlay Selectors
  const searchOverlay = document.getElementById('searchOverlay');
  const cartDrawer = document.getElementById('cartDrawer');
  const wishlistDrawer = document.getElementById('wishlistDrawer');
  const drawerOverlay = document.getElementById('drawerOverlay');

  // Trigger buttons
  const navSearchBtn = document.getElementById('navSearchBtn');
  const navWishlistBtn = document.getElementById('navWishlistBtn');
  const navCartBtn = document.getElementById('navCartBtn');

  // Close buttons
  const searchClose = document.getElementById('searchClose');
  const cartClose = document.getElementById('cartClose');
  const wishlistClose = document.getElementById('wishlistClose');

  // ADVANCED SEARCH TOGGLE
  if (navSearchBtn && searchOverlay) {
    navSearchBtn.addEventListener('click', function (e) {
      e.preventDefault();
      searchOverlay.classList.add('active');
      searchOverlay.querySelector('input')?.focus();
      document.body.style.overflow = 'hidden';
    });
  }

  if (searchClose && searchOverlay) {
    searchClose.addEventListener('click', function () {
      searchOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    });
  }

  // Close search overlay on Escape key
  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && searchOverlay && searchOverlay.classList.contains('active')) {
      searchOverlay.classList.remove('active');
      document.body.style.overflow = 'auto';
    }
  });

  // CART & WISHLIST STORAGE AND COUNTERS
  let cart = JSON.parse(localStorage.getItem('novastreet-cart')) || [];
  let wishlist = JSON.parse(localStorage.getItem('novastreet-wishlist')) || [];

  const updateUICounters = () => {
    const cartBadges = document.querySelectorAll('#navCartBtn .badge');
    const favBadges = document.querySelectorAll('#navWishlistBtn .badge');

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

  const animateBadge = (btnId) => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.classList.add('pulse-animation');
      setTimeout(() => btn.classList.remove('pulse-animation'), 600);
    }
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

  // Add to Cart Handlers
  document.querySelectorAll('.cart-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      cart.push(getProductData(this));
      localStorage.setItem('novastreet-cart', JSON.stringify(cart));
      updateUICounters();
      animateBadge('navCartBtn');
      renderCart();
      showToast('Added to Shopping Bag!');
    });
  });

  // Favorite / Wishlist Toggle Handlers
  document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      const product = getProductData(this);
      const index = wishlist.findIndex(item => item.name === product.name);

      if (index === -1) {
        wishlist.push(product);
        showToast('Added to Wishlist!');
        this.classList.add('active');
        animateBadge('navWishlistBtn');
      } else {
        wishlist.splice(index, 1);
        showToast('Removed from Wishlist');
        this.classList.remove('active');
      }
      localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
      updateUICounters();
      renderWishlist();
    });
  });

  // Open Cart Drawer
  if (navCartBtn && cartDrawer) {
    navCartBtn.addEventListener('click', (e) => {
      e.preventDefault();
      cartDrawer.classList.add('active');
      drawerOverlay?.classList.add('active');
      renderCart();
    });
  }

  // Open Wishlist Drawer
  if (navWishlistBtn && wishlistDrawer) {
    navWishlistBtn.addEventListener('click', (e) => {
      e.preventDefault();
      wishlistDrawer.classList.add('active');
      drawerOverlay?.classList.add('active');
      renderWishlist();
    });
  }

  // Close Drawers
  const closeDrawers = () => {
    cartDrawer?.classList.remove('active');
    wishlistDrawer?.classList.remove('active');
    drawerOverlay?.classList.remove('active');
  };

  cartClose?.addEventListener('click', closeDrawers);
  wishlistClose?.addEventListener('click', closeDrawers);
  drawerOverlay?.addEventListener('click', closeDrawers);

  // Render Cart inside Drawer
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

  // Render Wishlist inside Drawer
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

  // Global functions bound to window for inline onclick attributes
  window.removeFromCart = (index) => {
    cart.splice(index, 1);
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    updateUICounters();
    renderCart();
    renderCartPage(); // Update dedicated page if open
  };

  window.removeFromWishlist = (index) => {
    wishlist.splice(index, 1);
    localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
    updateUICounters();
    renderWishlist();
    renderWishlistPage(); // Update dedicated page if open
  };

  window.moveToCart = (index) => {
    cart.push(wishlist[index]);
    wishlist.splice(index, 1);
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
    updateUICounters();
    animateBadge('navCartBtn');
    renderCart();
    renderWishlist();
    renderCartPage(); // Update dedicated page if open
    renderWishlistPage(); // Update dedicated page if open
    showToast('Moved to Shopping Bag!');
  };

  // Grouped Quantity Adjustment Controls (Shopping Bag page specific)
  window.changeQuantity = (name, offset) => {
    if (offset === 1) {
      const sample = cart.find(item => item.name === name);
      if (sample) {
        cart.push({ ...sample, id: Date.now() + Math.random() });
      }
    } else if (offset === -1) {
      const index = cart.findIndex(item => item.name === name);
      if (index !== -1) {
        cart.splice(index, 1);
      }
    }
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    updateUICounters();
    renderCart();
    renderCartPage();
  };

  window.removeFromCartByName = (name) => {
    cart = cart.filter(item => item.name !== name);
    localStorage.setItem('novastreet-cart', JSON.stringify(cart));
    updateUICounters();
    renderCart();
    renderCartPage();
    showToast('Item removed from Shopping Bag');
  };

  window.moveToWishlistByName = (name) => {
    const sample = cart.find(item => item.name === name);
    if (sample) {
      if (!wishlist.some(w => w.name === sample.name)) {
        wishlist.push(sample);
        localStorage.setItem('novastreet-wishlist', JSON.stringify(wishlist));
      }
      cart = cart.filter(item => item.name !== name);
      localStorage.setItem('novastreet-cart', JSON.stringify(cart));
      updateUICounters();
      renderCart();
      renderCartPage();
      renderWishlistPage();
      showToast('Moved to Wishlist!');
    }
  };

  // Dedicated Cart Page rendering (Grouped Layout)
  const renderCartPage = () => {
    const listBody = document.getElementById('cartPageList');
    if (!listBody) return;

    if (cart.length === 0) {
      listBody.innerHTML = `
            <div class="text-center py-5" data-aos="fade-up">
                <i class="bi bi-bag-x display-1 opacity-10"></i>
                <h3 class="mt-4 fw-bold">Your bag is empty</h3>
                <p class="text-muted">Explore our luxury collections to add styling items.</p>
                <a href="products.html" class="btn btn-premium mt-3 rounded-pill">Continue Shopping</a>
            </div>`;
      updateSummary(0);
      return;
    }

    // Grouping logic for items in cart
    const grouped = [];
    cart.forEach(item => {
      const existing = grouped.find(g => g.name === item.name);
      if (existing) {
        existing.quantity += 1;
      } else {
        grouped.push({
          name: item.name,
          price: item.price,
          img: item.img,
          quantity: 1,
          rawPrice: parseFloat(item.price.replace('$', ''))
        });
      }
    });

    let subtotal = 0;
    listBody.innerHTML = grouped.map((item) => {
      const itemSubtotal = item.rawPrice * item.quantity;
      subtotal += itemSubtotal;
      return `
            <div class="cart-page-item d-md-flex align-items-center justify-content-between" data-aos="fade-up">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <img src="${item.img}" class="cart-item-image me-4">
                    <div>
                        <h5 class="fw-bold mb-1">${item.name}</h5>
                        <div class="cart-item-meta">
                            <span class="meta-badge">Size: M</span>
                            <span class="meta-badge">Color: Natural</span>
                        </div>
                        <h6 class="text-accent-color mt-2 fw-bold">${item.price} each</h6>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between justify-content-md-end gap-4 flex-wrap w-100-mobile">
                    <div class="quantity-control">
                        <button type="button" onclick="changeQuantity('${item.name}', -1)">-</button>
                        <input type="text" value="${item.quantity}" readonly>
                        <button type="button" onclick="changeQuantity('${item.name}', 1)">+</button>
                    </div>
                    <div class="text-end min-w-100-px">
                        <h5 class="fw-bold mb-0 text-accent-color">$${itemSubtotal.toFixed(2)}</h5>
                    </div>
                    <div class="d-flex gap-3 mt-2 mt-md-0">
                        <button class="action-link" onclick="moveToWishlistByName('${item.name}')">Move to Wishlist</button>
                        <button class="btn btn-light rounded-circle p-2" onclick="removeFromCartByName('${item.name}')" title="Remove"><i class="bi bi-trash text-danger"></i></button>
                    </div>
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

  // Dedicated Wishlist Page rendering
  const renderWishlistPage = () => {
    const grid = document.getElementById('wishlistPageGrid');
    const countText = document.getElementById('wishlistCountText');
    if (!grid) return;

    if (countText) {
      countText.textContent = wishlist.length === 1 
        ? "1 beautiful item saved in your Favorites Board." 
        : `${wishlist.length} beautiful items saved in your Favorites Board.`;
    }

    if (wishlist.length === 0) {
      grid.innerHTML = `
            <div class="col-12 text-center py-5" data-aos="fade-up">
                <i class="bi bi-heart display-1 opacity-10"></i>
                <h3 class="mt-4 fw-bold">Your Favorites Board is Empty</h3>
                <p class="text-muted">Start curation by saving designs you love.</p>
                <a href="products.html" class="btn btn-premium mt-3 rounded-pill">Curate Collection</a>
            </div>`;
      return;
    }

    grid.innerHTML = wishlist.map((item, index) => `
        <div class="col-md-4 col-sm-6" data-aos="fade-up">
            <div class="wishlist-page-card">
                <div class="wishlist-img-box">
                    <img src="${item.img}" alt="Product">
                    <button class="remove-wish-btn" onclick="removeFromWishlist(${index})" aria-label="Remove item"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="p-4">
                    <h6 class="fw-bold mb-1">${item.name}</h6>
                    <p class="text-accent-color fw-bold mb-3">${item.price}</p>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm rounded-pill border-light bg-light px-3" style="font-size: 0.8rem;" id="sizeSelect-${index}">
                            <option>Size M</option>
                            <option>Size S</option>
                            <option>Size L</option>
                        </select>
                        <button class="btn btn-dark btn-sm w-100 rounded-pill py-2" onclick="moveToCart(${index})">Add to Bag</button>
                    </div>
                </div>
            </div>
        </div>`).join('');
  };

  // Initialize view-specific components
  updateUICounters();
  renderCart();
  renderWishlist();
  renderCartPage();
  renderWishlistPage();

  // Share Favorites board clipboard action
  const shareWishlistBtn = document.getElementById('shareWishlistBtn');
  if (shareWishlistBtn) {
    shareWishlistBtn.addEventListener('click', function () {
      const shareUrl = window.location.href + "?shared=true";
      navigator.clipboard.writeText(shareUrl).then(() => {
        showToast('Favorites Board Link Copied to Clipboard!');
      }).catch(() => {
        showToast('Failed to copy link.');
      });
    });
  }

  // Interactive Promo code coupon apply simulation (cart page)
  const promoApplyBtn = document.getElementById('promoApplyBtn');
  if (promoApplyBtn) {
    promoApplyBtn.addEventListener('click', function () {
      const code = document.getElementById('promoInput').value.trim().toUpperCase();
      if (code === 'NOVA20') {
        let subtotal = 0;
        cart.forEach(item => {
          subtotal += parseFloat(item.price.replace('$', ''));
        });
        const discount = subtotal * 0.20;
        const newSubtotal = subtotal - discount;
        const tax = newSubtotal * 0.02;
        const total = newSubtotal + tax;
        
        document.getElementById('summarySubtotal').innerHTML = `<del class="text-muted small">${subtotal.toFixed(2)}</del> $${newSubtotal.toFixed(2)}`;
        document.getElementById('summaryTax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('summaryTotal').textContent = `$${total.toFixed(2)}`;
        showToast('Promo NOVA20 Applied: 20% OFF!');
      } else {
        showToast('Invalid Coupon Code');
      }
    });
  }

  // Checkout process flow step simulation
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
      const originalText = this.innerHTML;
      this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
      this.disabled = true;
      setTimeout(() => {
        showToast('Order Placed Successfully! Redirecting...');
        const steps = document.querySelectorAll('.step-item');
        if (steps.length >= 3) {
          steps[0].classList.remove('active');
          steps[1].classList.remove('active');
          steps[2].classList.add('active');
        }
        const cartList = document.getElementById('cartPageList');
        if (cartList) {
          cartList.innerHTML = `
                <div class="text-center py-5 animate-order-placed" data-aos="zoom-in">
                    <i class="bi bi-patch-check-fill text-success display-1 mb-4"></i>
                    <h3 class="mt-4 fw-bold">Thank You for Your Order!</h3>
                    <p class="text-muted mt-2">Your order number is #NS-${Math.floor(100000 + Math.random() * 900000)}.</p>
                    <a href="products.html" class="btn btn-premium mt-3 rounded-pill">Continue Shopping</a>
                </div>`;
        }
        cart = [];
        localStorage.setItem('novastreet-cart', JSON.stringify(cart));
        updateUICounters();
        renderCart();
      }, 2000);
    });
  }

  // SMOOTH SCROLLING FOR HASH LINKS
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetId = this.getAttribute("href");
      if (targetId === "#") return;
      const target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
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
      btn.style.backgroundColor = "#2ed573";
      btn.style.color = "#fff";
      input.value = "";

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.backgroundColor = "";
        btn.style.color = "";
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
      btn.style.backgroundColor = "#2ed573";
      btn.style.borderColor = "#2ed573";

      this.reset();

      setTimeout(() => {
        btn.innerHTML = originalText;
        btn.style.backgroundColor = "";
        btn.style.borderColor = "";
      }, 3000);
    });
  }
});
