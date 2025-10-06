let searchForm = document.querySelector('.search-form');

document.querySelector('#search-btn').onclick = () => {
    searchForm.classList.toggle('active');
}

let loginForm = document.querySelector('.login-form');
document.querySelector('#login-btn').onclick = () => {
    loginForm.classList.toggle('active');
}

const menuBtn = document.querySelector('#menu-btn');
menuBtn.onclick = () => {
        navbar.classList.toggle('active');
    };

document.querySelector("#cart-btn").addEventListener("click", () => {
  window.location.href = "cart.html";  // redirects to cart page
});

