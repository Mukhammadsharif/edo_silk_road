<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hujjatlar Boshqaruvi</title>

  <!-- Google Fonts - Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <style>
      :root {
        --sidebar-width: 256px;
        --navbar-height: 64px;
        --primary: #794DFD;
        --primary-hover: #d9467a;
        --primary-light: #fdf2f8;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --text: #1e293b;
        --text-muted: #64748b;
        --border: #e2e8f0;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        background-color: var(--bg);
        color: var(--text);
        min-height: 100vh;
      }

      /* Sidebar - Light & Dark Mode */
      .sidebar {
        position: fixed;
        top: 50px;
        left: 0;
        width: var(--sidebar-width);
        height: calc(100vh - var(--navbar-height));
        background-color: #ffffff; 
        border-right: 1px solid var(--border);
        padding: 1.5rem 0;
        overflow-y: auto;
        z-index: 30;
        display: flex;
        flex-direction: column;
        transition: background-color 0.3s;
      }

      .sidebar.dark {
        background-color: #794dfd;
      }

      /* Dark mode: normal menu text & icons - white */
      .sidebar.dark .menu-header {
        color: white;
      }

      .sidebar.dark .menu-header i:first-child,
      .sidebar.dark .menu-header .chevron {
        color: white;
      }

      /* Dark mode hover */
      .sidebar.dark .menu-header:hover {
        background-color: #ffffff1f;
        color: #fff;
      }

      .sidebar.dark .menu-header:hover i {
        color: var(--primary);
      }

      /* Dark mode submenu */
      .sidebar.dark .submenu {
        background-color: #5B39BE;
      }

      .sidebar.dark .submenu-item {
        color: white;
      }

      .sidebar.dark .submenu-item:hover {
        background-color: #1e293b;
        color: var(--primary);
      }

      .sidebar.dark .submenu-item:hover i {
        color: var(--primary);
      }

      .sidebar.dark .submenu-item i {
        color: white;
      }
      

      /* Dark mode logout border */
      .sidebar.dark .logout {
        border-top-color: #334155;
      }

      .menu-item {
        position: relative;
        margin: 0.25rem 1rem;
        border-radius: 0.5rem;
        overflow: hidden;
      }

      .menu-header {
        display: flex;
        align-items: center;
        justify-content: space-between; 
        gap: 1rem;  
        padding:10px 1rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.3s;
      }

      .menu-header:hover {
        background-color: var(--primary-light);
        color: var(--primary);
      }

      .menu-header:hover i {
        color: var(--primary);
      }

      /* Active menu */
      .menu-item.active > .menu-header {
        background-color: var(--primary);
        color: white;
      }

      .menu-item.active > .menu-header i {
        color: white;
      }

      .menu-item.active > .menu-header .menu-text {
        color: white;
      }

      .menu-item.active .chevron {
        color: white;
      }


      /* Main icon */
      .menu-header i:first-child {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        color: var(--text-muted);
        transition: color 0.3s;
      }

      /* Chevron icon */
  .chevron {
    width: 14px;
    height: 14px;
    transition: transform 0.3s, color 0.3s;
    color: var(--text-muted); /* default color */
  }

  /* Rotate chevron when open */
  .menu-item.open .chevron {
    transform: rotate(90deg);
  }

  /* Active menu - chevron white */
  .menu-item.active .chevron {
    color: white !important;
  }

  /* Hover state for menu header - chevron becomes primary */
  .menu-header:hover .chevron {
    color: var(--primary) !important;
  }

  /* Dark mode - default chevron color */
  .sidebar.dark .chevron {
    color: white; /* default dark mode */
  }

  /* Dark mode hover - chevron becomes primary */
  .sidebar.dark .menu-header:hover .chevron {
    color: #fff !important;
  }

  /* Dark mode active - chevron white */
  .sidebar.dark .menu-item.active .chevron {
    color: white !important;
  }

      .menu-text {
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex: 1;
      }

      /* Submenu */
      .submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        background-color: #f8f9fb;
      }

      .menu-item.open .submenu {
        max-height: 500px;
      }

      .submenu-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.65rem 1.75rem 0.65rem 3.5rem;
        color: var(--text-muted);
        font-size: 13.5px;
        cursor: pointer;
        transition: all 0.2s;
      }

      .submenu-item:hover {
        background-color: var(--primary-light);
        color: var(--primary);
        padding-left: 3.8rem;
      }

      .submenu-item i {
        width: 22px;
        height: 22px;
        color: var(--text-muted);
        transition: color 0.2s;
      }

      .submenu-item:hover i {
        color: var(--primary);
      }

      .logout {
        margin-top: auto;
        border-top: 1px solid  var(--border);
        padding-top: 1rem;
        margin-top: 2rem;
      }

      .logout .menu-item:hover .menu-header {
        background-color:#E2427E;
        color: #fff;
      }

      .logout .menu-item:hover .menu-header i {
        color: #dc2626;
      }

      /* Theme Toggle Tabs */
      .theme-tabs {
        display: flex;
        background-color: #f1f5f9;
        border-radius: 0.5rem;
        margin: 6rem 1rem 1rem 1rem;
        width: calc(100% - 2rem);
        padding: 4px;
      }

      .sidebar.dark .theme-tabs {
        background-color: #00000026;
      }

      .theme-tab {
        flex: 1;
        text-align: center;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.3s;
        padding: 4px 0;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .theme-tab.active {
        background-color: var(--primary);
        color: white;
      }

      .theme-tab i {
        width: 20px;
        height: 20px;
        color: var(--text-muted);
        transition: color 0.3s;
      }

      /* Active theme tab icon - always white */
      .theme-tab.active i {
        color: white;
      }

      .theme-tab:hover:not(.active) i {
        color: var(--primary);
      }
      /* Sidebar Icons */
  .menu-header i:first-child,
  .submenu-item i,
  .chevron {
    color: var(--primary); /* default primary color */
    transition: color 0.3s;
  }

  /* Active menu */
  .menu-item.active > .menu-header i,
  .menu-item.active .chevron,
  .menu-item.active .submenu-item i {
    color: white; /* active icons white */
  }

  /* Hover icons */
  .menu-header:hover i,
  .submenu-item:hover i {
    color: var(--primary); /* hover same as primary */
  }

  /* Dark mode adjustments */
  .sidebar.dark .menu-header i:first-child,
  .sidebar.dark .submenu-item i,
  .sidebar.dark .chevron {
    color: var(--primary); /* dark mode icons not active */
  }

  .sidebar.dark .menu-item.active > .menu-header i,
  .sidebar.dark .menu-item.active .chevron,
  .sidebar.dark .menu-item.active .submenu-item i {
    color: white; /* active in dark mode */
  }

  .sidebar.dark .menu-header:hover i,
  .sidebar.dark .submenu-item:hover i {
    color: var(--primary); /* hover in dark mode */
  }

    </style>
</head>
<body>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <nav class="h-full flex flex-col">
      <div class="flex-1">

        <!-- Asosiy -->
        <div class="menu-item active">
          <div class="menu-header">
            <i data-lucide="home" style="width: 22px; height: 22px;"></i>
            <span class="menu-text">Asosiy</span>
            
          </div>
        </div>

        <!-- Hujjat yaratish -->
        <div class="menu-item">
          <div class="menu-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
              <i data-lucide="file-plus-2" style="width: 22px; height: 22px;"></i>
              <span class="menu-text">Hujjat yaratish</span>
            </div>
            <i data-lucide="chevron-right" class="chevron"></i>
          </div>
          <div class="submenu">
            <div class="submenu-item">
              <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
              <span>Yangi hujjat</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="copy" style="width: 18px; height: 18px;"></i>
              <span>Shablondan</span>
            </div>
          </div>
        </div>

        <!-- Hujjatlar -->
        <div class="menu-item">
          <div class="menu-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
              <i data-lucide="folder-open" style="width: 22px; height: 22px;"></i>
              <span class="menu-text">Hujjatlar</span>
            </div>
            <i data-lucide="chevron-right" class="chevron"></i>
          </div>
          <div class="submenu">
            <div class="submenu-item">
              <i data-lucide="clock" style="width: 18px; height: 18px;"></i>
              <span>Kutayotganlar</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
              <span>Tasdiqlangan</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="x-circle" style="width: 18px; height: 18px;"></i>
              <span>Rad etilgan</span>
            </div>
          </div>
        </div>

        <!-- Menga yuborilgan -->
        <div class="menu-item">
          <div class="menu-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
              <i data-lucide="mail" style="width: 22px; height: 22px;"></i>
              <span class="menu-text">Menga yuborilgan</span>
            </div>
            <i data-lucide="chevron-right" class="chevron"></i>
          </div>
          <div class="submenu">
            <div class="submenu-item">
              <i data-lucide="inbox" style="width: 18px; height: 18px;"></i>
              <span>Yangi</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="archive" style="width: 18px; height: 18px;"></i>
              <span>Arxiv</span>
            </div>
          </div>
        </div>

        <!-- Mening hujjatlarim -->
        <div class="menu-item">
          <div class="menu-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
              <i data-lucide="file-text" style="width: 22px; height: 22px;"></i>
              <span class="menu-text">Mening hujjatlarim</span>
            </div>
            <i data-lucide="chevron-right" class="chevron"></i>
          </div>
          <div class="submenu">
            <div class="submenu-item">
              <i data-lucide="send" style="width: 18px; height: 18px;"></i>
              <span>Yuborilgan</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="edit" style="width: 18px; height: 18px;"></i>
              <span>Qoralama</span>
            </div>
          </div>
        </div>

        <!-- Tashkiliy tuzilma -->
        <div class="menu-item">
          <div class="menu-header">
            <i data-lucide="users" style="width: 22px; height: 22px;"></i>
            <span class="menu-text">Tashkiliy tuzilma</span>
          </div>
        </div>

        <!-- Sozlamalar -->
        <div class="menu-item">
          <div class="menu-header">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
              <i data-lucide="settings" style="width: 22px; height: 22px;"></i>
              <span class="menu-text">Sozlamalar</span>
            </div>
            <i data-lucide="chevron-right" class="chevron"></i>
          </div>
          <div class="submenu">
            <div class="submenu-item">
              <i data-lucide="user" style="width: 18px; height: 18px;"></i>
              <span>Profil</span>
            </div>
            <div class="submenu-item">
              <i data-lucide="shield" style="width: 18px; height: 18px;"></i>
              <span>Xavfsizlik</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Logout -->
      <div class="logout">
        <div class="menu-item">
          <div class="menu-header">
            <i data-lucide="log-out" style="width: 22px; height: 22px;"></i>
            <span class="menu-text">Chiqish</span>
          </div>
        </div>
      </div>

      <!-- Theme Toggle Tabs -->
      <div class="theme-tabs">
        <div class="theme-tab active" data-theme="light">
          <i data-lucide="sun" style="width: 20px; height: 20px;"></i>
        </div>
        <div class="theme-tab" data-theme="dark">
          <i data-lucide="moon" style="width: 20px; height: 20px;"></i>
        </div>
      </div>
    </nav>
  </aside>

  <script>
    lucide.createIcons();

    // Dropdown toggle
    document.querySelectorAll('.menu-header').forEach(header => {
      header.addEventListener('click', function() {
        const menuItem = this.parentElement;
        
        if (this.querySelector('.chevron')) {
          menuItem.classList.toggle('open');
        }
        
        document.querySelectorAll('.menu-item').forEach(item => {
          item.classList.remove('active');
        });
        menuItem.classList.add('active');
      });
    });

    // Theme toggle - only affects sidebar
    const sidebar = document.getElementById('sidebar');
    const themeTabs = document.querySelectorAll('.theme-tab');

    themeTabs.forEach(tab => {
      tab.addEventListener('click', function() {
        themeTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        const theme = this.getAttribute('data-theme');
        if (theme === 'dark') {
          sidebar.classList.add('dark');
        } else {
          sidebar.classList.remove('dark');
        }
      });
    });
  </script>
</body>
</html>