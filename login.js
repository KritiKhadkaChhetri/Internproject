const form = document.getElementById("loginForm");
const title = document.getElementById("formTitle");
const nameField = document.getElementById("nameField");
const nameInput = document.getElementById("name");
const emailInput = document.getElementById("email");
const passwordInput = document.getElementById("password");
const errorEl = document.getElementById("formError");
const submitBtn = document.getElementById("submitBtn");
const switchText = document.getElementById("switchText");
const switchLink = document.getElementById("switchLink");

let mode = "login"; // "login" or "signup"

function renderMode() {
  const isLogin = mode === "login";
  title.textContent = isLogin ? "Get started today!" : "Create your account";
  nameField.hidden = isLogin;
  submitBtn.textContent = isLogin ? "Login" : "Signup";
  switchText.textContent = isLogin ? "You Don't have an account ?" : "Already have an account ?";
  switchLink.textContent = isLogin ? "Signup" : "Login";
  errorEl.textContent = "";
  [nameInput, emailInput, passwordInput].forEach((i) => i.classList.remove("invalid"));
}

switchLink.addEventListener("click", (e) => {
  e.preventDefault();
  mode = mode === "login" ? "signup" : "login";
  renderMode();
});

function fail(input, message) {
  input.classList.add("invalid");
  input.focus();
  errorEl.textContent = message;
}

form.addEventListener("submit", (e) => {
  e.preventDefault();

  [nameInput, emailInput, passwordInput].forEach((i) => i.classList.remove("invalid"));
  errorEl.textContent = "";

  const name = nameInput.value.trim();
  const email = emailInput.value.trim();
  const password = passwordInput.value;

  if (mode === "signup" && !name) return fail(nameInput, "Please enter your name.");
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return fail(emailInput, "Please enter a valid email address.");
  if (password.length < 6) return fail(passwordInput, "Password must be at least 6 characters.");

  // Demo only: remember who is logged in (the password is NOT stored)
  const user = { name: name || email.split("@")[0], email };
  localStorage.setItem("user", JSON.stringify(user));

  window.location.href = "products.html";
});

renderMode();