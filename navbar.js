class CustomNavbar extends HTMLElement {
    connectedCallback() {
        this.attachShadow({ mode: 'open' });
        this.shadowRoot.innerHTML = `
           <style>
        :host {
          display: block;
          width: 100%;
          position: sticky;
          top: 0;
          z-index: 50;
          background-color: white;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .navbar-container {
          max-width: 1280px;
          margin: 0 auto;
          padding: 0 1rem;
          height: 4rem;
          display: flex;
          align-items: center;
          justify-content: space-between;
        }
        .logo {
          font-size: 1.5rem;
          font-weight: 700;
          color: #6366f1;
          text-decoration: none;
          display: flex;
          align-items: center;
        }
        .logo-icon {
          margin-right: 0.5rem;
        }
        .nav-links {
          display: flex;
          gap: 1.5rem;
        }
        .nav-link {
          color: #4b5563;
          font-weight: 500;
          text-decoration: none;
          transition: color 0.2s;
          display: flex;
          align-items: center;
        }
        .nav-link:hover {
          color: #6366f1;
        }
        .nav-link i {
          margin-right: 0.25rem;
        }
        .mobile-menu-button {
          display: none;
          background: none;
          border: none;
          cursor: pointer;
        }
        @media (max-width: 768px) {
          .mobile-menu-button {
            display: block;
          }
          .nav-links {
            display: none;
          }
          .mobile-menu {
            position: absolute;
            top: 4rem;
            left: 0;
            right: 0;
            background-color: white;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
          }
        }
      </style>
      <div class="navbar-container">
        <a href="dashboard.php" class="logo">
          <i data-feather="activity" class="logo-icon"></i>
          Gymrats
        </a>
        
        <div class="nav-links">
          <a href="store.php" class="nav-link">
            <i data-feather="shopping-bag"></i> Store
          </a>
          <a href="workouts.php" class="nav-link">
            <i data-feather="activity"></i> Workouts
          </a>
          <?php if(isset($_SESSION['user'])): ?>
            <a href="dashboard.php" class="nav-link">
              <i data-feather="user"></i> Dashboard
            </a>
            <a href="login.php" class="nav-link">
              <i data-feather="log-out"></i> Logout
            </a>
          <?php endif; ?>
        </div>
        
        <button class="mobile-menu-button">
          <i data-feather="menu"></i>
        </button>
      </div>
      
      
    `;
        
        // Initialize feather icons in shadow DOM
        const featherScript = document.createElement('script');
        featherScript.src = 'https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js';
        this.shadowRoot.appendChild(featherScript);
        
        featherScript.onload = () => {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        };
    }
}

customElements.define('custom-navbar', CustomNavbar);