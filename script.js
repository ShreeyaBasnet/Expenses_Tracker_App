function togglePage() {
  const landing = document.getElementById('landingPage');
  const dash = document.getElementById('dashPage');
  const btn = document.getElementById('toggleBtn');

  if (landing.classList.contains('active')) {
    landing.classList.remove('active');
    dash.classList.add('active');
    btn.textContent = '← Back to Landing';
    btn.classList.remove('landing-mode');
  } else {
    dash.classList.remove('active');
    landing.classList.add('active');
    btn.textContent = 'View Dashboard →';
    btn.classList.add('landing-mode');
  }
  window.scrollTo(0, 0);
}
function scrollToSection(sectionId) {
  const section = document.getElementById(sectionId);

  if (section) {
    section.scrollIntoView({
      behavior: 'smooth'
    });
  }
}function openLogin() {
  localStorage.setItem("formMode", "login");
  window.location.href = "login.php";
}

function openSignup() {
  localStorage.setItem("formMode", "signup");
  window.location.href = "login.php";
}

function scrollToSection(id) {
  document.getElementById(id).scrollIntoView({ behavior: "smooth" });
}