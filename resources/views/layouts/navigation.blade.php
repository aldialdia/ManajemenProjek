<header class="top-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search projects, tasks..." class="search-input">
        </div>
    </div>

    <div class="navbar-right">
        <button class="navbar-btn" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </button>

        <button class="navbar-btn" title="Messages">
            <i class="fas fa-envelope"></i>
        </button>

        <div class="dropdown" id="userDropdown">
            <button class="user-menu" onclick="toggleDropdown('userDropdown')">
                @auth
                    <div class="avatar avatar-sm">
                        {{ auth()->user()->initials }}
                    </div>
                    <span class="user-menu-name">{{ auth()->user()->name }}</span>
                @else
                    <div class="avatar avatar-sm">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-menu-name">Guest</span>
                @endauth
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="dropdown-menu">
                @auth
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        My Profile
                    </a>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        Settings
                    </a>
                    <hr style="margin: 0.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"
                            style="width: 100%; background: none; border: none; cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="dropdown-item">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="dropdown-item">
                        <i class="fas fa-user-plus"></i>
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<style>
    .top-navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 2rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    }

    .navbar-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sidebar-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 1.25rem;
        color: var(--dark);
        cursor: pointer;
        padding: 0.5rem;
    }

    .search-box {
        position: relative;
        width: 320px;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        background: white;
        transition: all 0.2s;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-btn {
        position: relative;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: none;
        background: white;
        color: var(--secondary);
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .navbar-btn:hover {
        background: #f1f5f9;
        color: var(--primary);
    }

    .notification-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 18px;
        height: 18px;
        background: var(--danger);
        color: white;
        font-size: 0.65rem;
        font-weight: 600;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 12px;
        border: none;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .user-menu:hover {
        background: #f1f5f9;
    }

    .user-menu-name {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--dark);
    }

    .user-menu i.fa-chevron-down {
        font-size: 0.75rem;
        color: var(--secondary);
    }

    @media (max-width: 768px) {
        .sidebar-toggle {
            display: block;
        }

        .search-box {
            display: none;
        }
    }
</style>

<script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.classList.toggle('active');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        const dropdowns = document.querySelectorAll('.dropdown');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
    });
</script>