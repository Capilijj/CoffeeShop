// Highlight active nav link on click and on load
document.addEventListener('DOMContentLoaded', function() {
  var navLinks = document.querySelectorAll('.nav-links a');
  // Exclude the Log In button from navLinks for active state logic
  var filteredNavLinks = Array.from(navLinks).filter(function(link) {
    return !link.classList.contains('nav-login-btn');
  });
  function setActiveLink() {
    var path = window.location.pathname + window.location.hash;
    // If on login page, remove all active states
    if (window.location.pathname.endsWith('/index.php')) {
      filteredNavLinks.forEach(function(link) { link.classList.remove('active'); });
      return;
    }
    filteredNavLinks.forEach(function(link) {
      link.classList.remove('active');
      // For dashboard section links
      if (link.hash && window.location.pathname.includes('dashboard.php')) {
        if (window.location.hash === link.hash) {
          link.classList.add('active');
        }
      } else if (link.pathname === window.location.pathname && !link.hash) {
        link.classList.add('active');
      }
    });
  }
  navLinks.forEach(function(link) {
    // Only add click event for non-login links
    if (!link.classList.contains('nav-login-btn')) {
      link.addEventListener('click', function() {
        filteredNavLinks.forEach(function(l) { l.classList.remove('active'); });
        this.classList.add('active');
      });
    }
  });
  setActiveLink();
  window.addEventListener('hashchange', setActiveLink);
  window.addEventListener('popstate', setActiveLink);
});
document.addEventListener('DOMContentLoaded', function() {
  var profileBtn = document.getElementById('profileBtn');
  var dropdown = document.getElementById('profileDropdown');
  if (profileBtn && dropdown) {
    profileBtn.addEventListener('click', function(e) {
      e.preventDefault();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });
    document.addEventListener('click', function(e) {
      if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });
  }
});
