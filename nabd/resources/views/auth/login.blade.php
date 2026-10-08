<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | نـبـض AI</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --bg-color: #0f172a;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* دوائر زخرفية في الخلفية */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            z-index: 0;
            filter: blur(80px);
            opacity: 0.15;
        }
        body::before { background: #6366f1; top: -100px; right: -100px; }
        body::after { background: #22c55e; bottom: -100px; left: -100px; }

        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            z-index: 1;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .brand-logo {
            width: 85px;
            height: 85px;
            background: #fff;
            color: var(--primary-color);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            transform: rotate(-5deg);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            margin-bottom: 10px;
            margin-right: 10px;
            font-size: 0.9rem;
        }

        .form-control-modern {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 18px !important;
            padding: 14px 20px !important;
            color: #fff !important;
            transition: 0.3s;
        }

        .form-control-modern:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2) !important;
        }

        .form-control-modern::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .btn-login {
            background: var(--primary-grad);
            border: none;
            border-radius: 18px;
            padding: 15px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
            transition: 0.3s;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.5);
            color: #fff;
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .demo-box {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            padding: 15px;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>
</head>
<body>

    <div class="login-card animate__animated animate__fadeInUp">
        <div class="text-center mb-5">
            <div class="brand-logo">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <h1 class="h3 fw-bold text-white mb-2">نـبـض AI</h1>
            <p class="text-white-50 small px-4">مرحباً بك مجدداً، سجل دخولك للوصول إلى مركز التحليل الذكي</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-envelope me-1"></i> البريد الإلكتروني</label>
                <input type="email" name="email" value="{{ old('email') }}" 
                       class="form-control form-control-lg form-control-modern" 
                       placeholder="أدخل بريدك الإلكتروني" required>
            </div>

            <div class="mb-4">
                <label class="form-label"><i class="fa-solid fa-lock me-1"></i> كلمة المرور</label>
                <input type="password" name="password" 
                       class="form-control form-control-lg form-control-modern" 
                       placeholder="أدخل كلمة المرور" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input shadow-none" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">تذكرني دائماً</label>
                </div>
            </div>

            <button class="btn btn-login w-100 fw-bold fs-5">
                دخول للنظام <i class="fa-solid fa-arrow-left ms-2"></i>
            </button>
        </form>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>