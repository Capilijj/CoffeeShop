<?php
// FILE: header.php
// Admin header/navbar for User Order Dashboard, Update Menu, Add-ons, and Feedback
?>
<div class="admin-navbar">
  <div class="admin-navbar-left">
    <span class="admin-navbar-title">Coffeeshop Admin</span>
    <span class="admin-navbar-links">
      <a href="admin.php" class="admin-nav-link"><i class="fa fa-list"></i> <span class="nav-text">User Orders</span></a>
      <a href="edit_menu.php" class="admin-nav-link"><i class="fa fa-coffee"></i> <span class="nav-text">Edit Menu</span></a>
      <a href="admin_addons.php" class="admin-nav-link"><i class="fa fa-cogs"></i> <span class="nav-text">Manage Add-ons</span></a>
      <a href="admin_feedback.php" class="admin-nav-link"><i class="fa fa-comments"></i> <span class="nav-text">View Feedback</span></a>
    </span>
  </div>
  <div class="admin-navbar-right">
    <a href="../LoginPage/logout.php" class="admin-nav-link logout"><i class="fa fa-sign-out-alt"></i> <span class="nav-text">Logout</span></a>
  </div>
</div>
<style>
  /* Inilipat ang CSS para sa header dito, dahil ito ang pinaka-lohikal na lokasyon. */
  .admin-navbar {
    background: #5c4033;
    box-shadow: 0 2px 8px rgba(160,82,45,0.08);
    /* NEW FIX: PADDING adjusted to 50px on the right for more space */
    padding: 0 50px 0 32px; 
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    /* Para manatiling nasa taas ng page */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 100;
  }
  .admin-navbar-left {
    display: flex;
    align-items: center;
    gap: 32px;
  }
  .admin-navbar-title {
    color: #fff;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: 1.2px;
    margin-right: 20px;
  }
  .admin-navbar-links {
    display: flex;
    /* NEW FIX: Reduced gap to save horizontal space */
    gap: 12px;
  }
  .admin-nav-link {
    color: #f8f5f1;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .admin-nav-link:hover {
    color: #ffcc99;
  }
  .admin-nav-link i {
    font-size: 1rem;
  }
  .admin-nav-link.logout {
    color: #f44336;
  }
  .admin-nav-link.logout:hover {
    color: #d32f2f;
  }

  /* Responsive styles for admin header */
  @media (max-width: 768px) {
    .admin-navbar {
      flex-direction: column;
      height: auto;
      padding: 12px 16px; /* Optimized mobile padding */
      align-items: flex-start;
      gap: 12px;
    }
    .admin-navbar-left {
      flex-direction: column;
      align-items: flex-start;
      gap: 8px;
      width: 100%;
    }
    .admin-navbar-title {
      margin-right: 0;
      margin-bottom: 8px;
    }
    .admin-navbar-links {
      flex-direction: column;
      gap: 8px;
    }
    .admin-navbar-right {
      width: 100%;
      text-align: right;
    }
  }
</style>