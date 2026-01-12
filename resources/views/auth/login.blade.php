@extends('layouts.app')

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

@section('content')

  <!-- ================= CURTAIN PRELOADER ================= -->
  <div class="cp-preloader" id="cp-preloader"></div>
  <!-- ===================================================== -->

  <body>
    <main >

      <video class="auth-sidebar__video" playsinline autoplay loop muted
        src="{{ asset('images/background.mp4') }}"></video>

      <div class="containers">

        <div class="logo">
          <img src="{{ asset('images/logo.svg') }}" alt="" />
          <div class="logo-text">
            <p>O’ZBEKISTON TEMIR YO’LLARI</p>
            <span>AKSIYADORLIK JAMIYATI</span>
          </div>
        </div>

        <div class="tabs">
          <div class="tab active" data-tab="password">
            <i style="width: 18px; height: 18px" data-lucide="log-in"></i>
            Password Login
          </div>
          <div class="tab" data-tab="eri">
            <i style="width: 18px; height: 18px" data-lucide="shield-check"></i>
            ERI Key Login
          </div>
        </div>

        <div class="tab-wrapper">
          <div class="tab-content active" id="password">
            <form action="{{ route('login') }}" method="POST">
              @csrf

              <div class="input-group">
                <i data-lucide="user" class="icon"></i>
                <input type="text" name="username" placeholder="Loginni kiriting" required>
              </div>

              <div class="input-group">
                <i data-lucide="lock" class="icon"></i>
                <input type="password" name="password" placeholder="Parolni kiriting" required>
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

          <div class="tab-content" id="eri">
            <form>
              <div class="input-group">
                <i data-lucide="key" class="icon"></i>
                <select required>
                  <option value=""></option>
                  <option>ERI Key 1</option>
                  <option>ERI Key 2</option>
                </select>
              </div>

              <div class="input-group">
                <i data-lucide="lock" class="icon"></i>
                <input type="password" placeholder="PIN for ERI Key" required>
              </div>

              <button type="submit" class="button">
                <i data-lucide="shield-check"></i>
                Authenticate with ERI Key
              </button>
            </form>
          </div>
        </div>
      </div>

      <script>
        lucide.createIcons();

        document.querySelectorAll(".tab").forEach(tab => {
          tab.addEventListener("click", () => {
            document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
            document.querySelectorAll(".tab-content").forEach(c => c.classList.remove("active"));
            tab.classList.add("active");
            document.getElementById(tab.dataset.tab).classList.add("active");
            lucide.createIcons();
          });
        });
      </script>

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

@endsection

<style>
  /* ================= YOUR ORIGINAL STYLES (UNCHANGED) ================= */

  /* ... ALL YOUR EXISTING CSS EXACTLY AS YOU SENT ... */

  /* ================= CURTAIN PRELOADER STYLES ================= */
  
/* ================= CURTAIN PRELOADER STYLES ================= */
.cp-preloader {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    background: transparent; /* dark background looks more premium */
}

.cp-strip {
    flex: 1;
    overflow: hidden;
}

.cp-strip span {
    display: block;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        135deg,
        #6d28d9 0%,
        #7c3aed 30%,
        #a78bfa 60%,
        #c084fc 100%
    );
    transform: translateX(-100%); /* start hidden from left */
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
  
</style>


<style>
  .main {
    display: flex;
    align-items: center;
    min-height: 100vh;
    justify-content: center;
   
    overflow: hidden;
    position: relative;
  }

  .containers {
    position: absolute;
    top: 10%;
    left: 10%;
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

  /* Tabs with tiny icons */
  .tabs {
    display: flex;
    background: rgba(121, 77, 253, 0.1);
    /* changed alpha version of new color */
    border-radius: 12px;
    overflow: hidden;
    margin-top: 30px;
  }

  .tab {
    flex: 1;
    padding: 12px 14px;
    text-align: center;
    color: #666;
    font-size: 13px;
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
    background: #794DFD;
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

  .input-group {
    position: relative;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
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

  .icon {
    position: absolute;
    left: 14px;
    width: 18px;
    height: 18px;
    color: #999;
    transition: color 0.4s ease;
    pointer-events: none;
  }

  .input-group:focus-within .icon {
    color: #794DFD;
  }

  .button {
    width: 100%;
    padding: 15px;
    background: #794DFD;
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s ease;
    box-shadow: 0 8px 20px rgba(121, 77, 253, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  button:hover {
    background: #5c36c1;
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(121, 77, 253, 0.4);
  }

  .forgot a {
    color: #794DFD;
    font-size: 14px;
    text-decoration: none;
  }

  .forgot a:hover {
    text-decoration: underline;
  }

  /* Yangi qator — checkbox va forgot bir qatorda */
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
  }

  .auth-sidebar__video {
    width: 50vw;
    height: 100vh;
    object-fit: cover;
    position: absolute;
    right: 0;
    top: 0;
    z-index: -1;
  }

  .logo {
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .logo p {
    font-size: 16px;
    font-weight: 600;
    color: #515151;
  }

  .logo span {
    font-size: 13px;
    color: #959595;
  }

  .logo-text {
    display: flex;
    flex-direction: column;
  }

  .logo-text p {
    margin: 0;
    color: #707070;
  }

  .icon {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
  }

  input:-internal-autofill-selected {
    box-shadow: 0 0 0px 1000px white inset !important;
    -webkit-text-fill-color: #000 !important;
    bckground-color: white !important;
  }
</style>