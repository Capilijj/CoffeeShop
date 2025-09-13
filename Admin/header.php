<?php
// Admin header/navbar for User Order Dashboard and Update Menu
?>
<div class="admin-navbar">
  <div class="admin-navbar-left">
    <span class="admin-navbar-title">Coffeeshop Admin</span>
    <span class="admin-navbar-links">
      <a href="admin.php" class="admin-nav-link"><i class="fa fa-list"></i> <span class="nav-text">User Orders</span></a>
      <a href="edit_menu.php" class="admin-nav-link"><i class="fa fa-coffee"></i> <span class="nav-text">Edit Menu</span></a>
    </span>
  </div>
  <div class="admin-navbar-right">
    <a href="../LoginPage/logout.php" class="admin-nav-link logout"><i class="fa fa-sign-out-alt"></i> <span class="nav-text">Logout</span></a>
  </div>
</div>
<style>
  .admin-navbar {
    background: #5c4033;
    box-shadow: 0 2px 8px rgba(160,82,45,0.08);
    padding: 0 32px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .admin-navbar-left {
    display: flex;
    align-items: center;
    gap: 32px;
  }
  .admin-navbar-title {
    color: #fff;
    font-family: 'Lora', serif;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: 1.2px;
    margin-right: 20px;
  }
  .admin-navbar-links {
    display: flex;
    gap: 16px;
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
      padding: 12px 16px;
      align-items: flex-start;
      gap: 12px;
    }
    .admin-navbar-left,
    .admin-navbar-right {
      width: 100%;
      justify-content: space-between;
      display: flex;
      align-items: center;
    }
    .admin-navbar-links {
      gap: 8px;
    }
    .admin-navbar-title {
      font-size: 1.2rem;
      margin-right: 0;
    }
    .admin-nav-link {
      font-size: 0.9rem;
    }
    .nav-text {
      /* Hide the link text and only show icons */
      display: none;
    }
    .admin-nav-link i {
      font-size: 1.2rem;
    }
    .admin-nav-link {
      padding: 6px;
      border: 1px solid transparent;
      border-radius: 4px;
    }
    .admin-nav-link:hover {
        border-color: #ffcc99;
    }
  }
</style>