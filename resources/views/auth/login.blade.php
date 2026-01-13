@extends('layouts.app')

@section('content')

    <!-- ================= CURTAIN PRELOADER ================= -->
    <div class="cp-preloader" id="cp-preloader"></div>
    <!-- ===================================================== -->

    <body>
    <main>

        <video class="auth-sidebar__video" playsinline autoplay loop muted
               src="{{ asset('images/background.mp4') }}"></video>

        <div class="containers">

            <div class="logo">
                <img src="{{ asset('images/logo.svg') }}" alt="" />
                <div class="logo-text">
                    <p>O'ZBEKISTON TEMIR YO'LLARI</p>
                    <span>AKSIYADORLIK JAMIYATI</span>
                </div>
            </div>

            <div class="tabs">
                <div class="tab active" data-tab="password" onclick="showTab('login')">
                    <i style="width: 18px; height: 18px" data-lucide="log-in"></i>
                    Password Login
                </div>
                <div class="tab" data-tab="eri" onclick="showTab('eri')">
                    <i style="width: 18px; height: 18px" data-lucide="shield-check"></i>
                    ERI Key Login
                </div>
            </div>

            <div class="tab-wrapper">
                <!-- Password Login Tab -->
                <div class="tab-content active" id="login-tab">
                    <form action="{{ route('login') }}" method="POST">
                        @csrf

                        <div class="input-group">
                            <i data-lucide="user" class="icon"></i>
                            <input type="text" name="username" placeholder="Loginni kiriting" required>
                        </div>

                        <div class="input-group">
                            <i data-lucide="lock" class="icon"></i>
                            <input type="password" id="password" name="password" placeholder="Parolni kiriting" required>
                            <i data-lucide="eye" onclick="togglePassword()" style="position: absolute; right: 15px; top: 13px; cursor: pointer; color: #999;"></i>
                        </div>

                        <div class="remember-forgot">
                            <label class="remember-me">
                                <input type="checkbox">
                                Meni eslab qolish
                            </label>
                            <div class="forgot">
                                <a href="#">Parolni unutdingizmi?</a>
                            </div>
                        </div>

                        <button type="submit" class="button">
                            <i data-lucide="log-in"></i>
                            Kirish
                        </button>
                    </form>
                </div>

                <!-- ERI Tab -->
                <div class="tab-content" id="eri-tab">
                    <input type="hidden" name="eri" id="selectedEri" value="">

                    <div class="input-group">
                        <i data-lucide="key" class="icon"></i>
                        <select class="form-select" id="eriSelect" disabled required>
                            <option value="">Tanlanmagan</option>
                        </select>
                    </div>

                    <button type="button" class="button btn-usb" onclick="openUsbToken()">
                        <img src="https://esi.uz/oauth2/assets/images/icons/pfx.ico" alt="pfx" style="height: 20px">
                        ERI ni tanlang
                    </button>

                    <button onclick="signin()" class="button" id="eriLoginBtn" disabled style="margin-top: 15px;">
                        <i data-lucide="shield-check"></i>
                        Kirish
                    </button>
                </div>
            </div>
        </div>

        <!-- ERI Modal -->
        <div class="eri-modal" id="eriModal">
            <div class="eri-modal-content">
                <div class="eri-modal-header">
                    <h5>ERI ni tanlang</h5>
                    <button class="eri-modal-close" onclick="closeEriModal()">×</button>
                </div>
                <div class="eri-modal-body" id="eriModalBody">
                    <div class="loading-spinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">ERI kalit tekshirilmoqda...</span>
                        </div>
                        <p class="mt-3">E-IMZO yuklanmoqda...</p>
                    </div>
                </div>
            </div>
        </div>

    </main>
    </body>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2@11') }}"></script>
    <script src="{{ asset('js/e_imzo.js') }}"></script>
    <script src="{{ asset('js/e_imzo_client.js') }}"></script>
    <script src="{{ asset('js/login_e_imzo.js') }}"></script>

    <!-- ================= CURTAIN PRELOADER SCRIPT ================= -->
    <script>
        (function () {
            const preloader = document.getElementById("cp-preloader");
            const STRIPS = 16;

            for (let i = 0; i < STRIPS; i++) {
                const strip = document.createElement("div");
                strip.className = "cp-strip";

                const inner = document.createElement("span");
                inner.style.setProperty("--i", i);

                strip.appendChild(inner);
                preloader.appendChild(strip);
            }

            setTimeout(() => {
                preloader.style.display = "none";
            }, 3300);
        })();
    </script>
    <!-- ============================================================ -->

    <script>
        localStorage.clear();
        lucide.createIcons();

        // Tab switching function
        function showTab(tabName) {
            // Remove active from all tabs and content
            document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));

            // Add active to selected tab
            if (tabName === 'login') {
                document.querySelector('[data-tab="password"]').classList.add("active");
                document.getElementById("login-tab").classList.add("active");
            } else if (tabName === 'eri') {
                document.querySelector('[data-tab="eri"]').classList.add("active");
                document.getElementById("eri-tab").classList.add("active");
            }

            // Recreate icons
            lucide.createIcons();
        }

        // Password toggle function
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }

        // Functions from original login (openUsbToken, closeEriModal, signin)
        // These should be defined in your login_e_imzo.js file
    </script>

@endsection

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-size: 16px;
    }

    body {
        font-family: 'Poppins', sans-serif;
    }

    main {
        display: flex;
        align-items: center;
        min-height: 100vh;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

.containers {
    position: absolute;
    top: 22%;
    left: 12%;
    width: 440px;
    padding: 40px 35px 0 35px;
    animation: fadeIn 1.2s ease-out;
    z-index: 1;
}
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }

    to {
        opacity: 1;
        transform: scale(1);
    }
}

.logo {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-text {
    display: flex;
    flex-direction: column;
}

.logo-text p {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #515151;
}

.logo-text span {
    font-size: 13px;
    color: #959595;
}

/* Tabs */
.tabs {
    display: flex;
    background: rgba(121, 77, 253, 0.1);
    border-radius: 12px;
    overflow: hidden;
    margin-top: 30px;
}

    .tab {
        flex: 1;
        padding: 12px 14px;
        text-align: center;
        color: #666;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.4s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }

.tab.active {
    background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
    color: #fff;
    box-shadow: 0 4px 15px rgba(121, 77, 253, 0.4);
}

.tab-wrapper {
    position: relative;
    min-height: 320px;
    overflow: hidden;
    margin-top: 40px;
    z-index: 1000;
}

.tab-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    display: none;
}

.tab-content.active {
    display: block;
}

/* Inputs */
.input-group {
    position: relative;
    margin-bottom: 30px;
    display: flex !important;
    align-items: center !important;
}

input,
select {
    width: 100%;
    padding: 15px 15px 15px 42px;
    background: rgba(255, 255, 255, 0.8) !important;
    border: 2px dashed #e3e3e3;
    border-radius: 12px;
    color: #333;
    font-size: 16px;
    outline: none;
    transition: all 0.4s ease;
}

input:focus,
select:focus {
    border-color: #794DFD;
    box-shadow: 0 0 20px rgba(121, 77, 253, 0.2);
}

input:-internal-autofill-selected {
    box-shadow: 0 0 0px 1000px white inset !important;
    -webkit-text-fill-color: #000 !important;
    background-color: white !important;
}

    .icon {
        position: absolute;
        left: 14px;
        top: 13px;
        width: 22px;
        height: 22px;
        color: #999;
        transition: color 0.4s ease;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

.input-group:focus-within .icon {
    color: #794DFD;
}

/* Button */
.button {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #794DFD 0%, #9d6fff 100%);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.button:hover:not(:disabled) {
    background: linear-gradient(135deg,rgb(99, 61, 214) 0%,rgb(127, 74, 241) 100%);
    transform: translateY(-4px);
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Remember and Forgot */
.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0 30px 0;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
    cursor: pointer;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #794DFD;
    cursor: pointer;
    padding: 0;
    margin: 0;
}

.forgot a {
    color: #794DFD;
    font-size: 14px;
    text-decoration: none;
}

.forgot a:hover {
    text-decoration: underline;
}

/* Video Background */
.auth-sidebar__video {
    width: 50vw;
    height: 100vh;
    object-fit: cover;
    position: absolute;
    right: 0;
    top: 0;
    z-index: -1;
}

    /* ERI Modal */
    .eri-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        align-items: center;
        justify-content: center;
    }

    .eri-modal.show {
        display: flex;
    }

    .eri-modal-content {
        background-color: #fff;
        border-radius: 12px;
        width: 90%;
        max-height: 80vh;
        max-width: 500px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .eri-modal-header {
        padding: 20px 25px;
        border-bottom: 1px solid #e3e3e3;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .eri-modal-header h5 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .eri-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #999;
        line-height: 1;
    }

    .eri-modal-close:hover {
        color: #333;
    }

    .eri-modal-body {
        padding: 30px 25px;
        overflow-y: auto;
    }

    .loading-spinner {
        text-align: center;
    }

    .eri-item {
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 15px rgba(255, 255, 255, 1);
    }

    .eri-item:hover {
        border: 1px solid #3200f6;
        background: #f8f9ff;
        box-shadow: 0 4px 15px rgba(121, 77, 253, 0.4);
    }

    .eri-item.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: rgba(206,7,30,0.1) !important;
        border-color: #ce071e;
    }

    .eri-item.disabled:hover {
        border-color: #ce071e;
        background: rgba(206,7,30,0.1) !important;
    }

    .eri-item-header {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .eri-item-header img {
        height: 20px;
        margin-right: 8px;
    }

    .eri-item-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .eri-item-type {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .eri-type-physical {
        background: #e3f2fd;
        color: #1976d2;
    }

    .eri-type-legal {
        background: #fff3e0;
        color: #f57c00;
    }

    .eri-item-details {
        display: grid;
        gap: 5px;
        font-size: 13px;
        color: #666;
    }

    .eri-detail-row {
        margin-bottom: 5px;
    }

    .eri-detail-label {
        font-weight: 500;
    }

    .eri-detail-value {
        color: #333;
    }

    .eri-org-info {
        margin: 10px 0;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 6px;
        font-size: 13px;
    }

    .eri-item-footer {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .eri-expiry {
        color: #d29404;
        font-size: 12px;
    }

    .eri-expiry.expired {
        color: #ce071e;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border 0.75s linear infinite;
    }
.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 0.25em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

.text-primary {
    color: #794DFD;
}

.mt-3 {
    margin-top: 1rem;
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ================= CURTAIN PRELOADER STYLES ================= */
.cp-preloader {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    background: transparent;
}

.cp-strip {
    flex: 1;
    overflow: hidden;
}

.cp-strip span {
    display: block;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg,
            #6d28d9 0%,
            #7c3aed 30%,
            #a78bfa 60%,
            #c084fc 100%);
    transform: translateX(-100%);
    will-change: transform;
    animation: cp-slide 1.5s cubic-bezier(0.22, 0.61, 0.36, 1) forwards;
}

.cp-strip span {
    animation-delay: calc(var(--i) * 20ms);
}

@keyframes cp-slide {
    to {
        transform: translateX(100%);
    }
}

.input-group button{
    box-shadow: 0 0 0px 1000px white inset !important;
    -webkit-text-fill-color: #000 !important;
    background-color: white !important;
    width:100%;
    padding: 12px 15px 12px 42px;
    background: rgba(255, 255, 255, 0.8) !important;
    border: 2px dashed #e3e3e3;
    border-radius: 12px;
    color: #333;
    font-size: 16px;
    outline: none;
    transition: all 0.4s ease;
}
</style>
