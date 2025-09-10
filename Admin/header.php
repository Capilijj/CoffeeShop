<?php
// Admin header/navbar for User Order Dashboard and Update Menu
?>
<div class="admin-navbar">
  <div class="admin-navbar-left">
    <span class="admin-navbar-title">Coffeeshop Admin</span>
    <span class="admin-navbar-links">
      <a href="admin.php" class="admin-nav-link"><i class="fa fa-list"></i> User Orders</a>
  <a href="edit_menu.php" class="admin-nav-link"><i class="fa fa-coffee"></i> Edit Menu</a>
    </span>
  </div>
  <div class="admin-navbar-right">
    <a href="../LoginPage/logout.php" class="admin-nav-link logout"><i class="fa fa-sign-out-alt"></i> Logout</a>
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
    margin-right: 24px;
  }
  .admin-navbar-links {
    display: flex;
    gap: 24px;
    margin-left: 12px;
  }
  .admin-nav-link {
    color: #ffcc99;
    text-decoration: none;
    font-size: 1.08rem;
    font-weight: 600;
    transition: color 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .admin-nav-link:hover {
    color: #fff;
  }
  .admin-navbar-right {
    margin-left: auto;
  }
  .admin-nav-link.logout {
    color: #ffd6b3;
    font-weight: 700;
    margin-left: 18px;
  }
</style>
