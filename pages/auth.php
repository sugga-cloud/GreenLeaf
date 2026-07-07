<?php
require_once __DIR__ . '/../services/Auth.php';
Auth::start_session();
require_once __DIR__ . '/../sqlite/db.php';

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login';
$error = '';

// Handle Authorization Form POST Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check (skip for dev bypass logins)
    $post_password = $_POST['password'] ?? '';
    if ($post_password !== 'devmode_bypass' && !Auth::verify_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Check if this is a dev bypass login (password is 'devmode_bypass')
        if ($password === 'devmode_bypass') {
            if (Auth::login_dev_bypass($email)) {
                // Determine redirect based on user type
                $check = $db->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    echo '<script>window.location.href = "?page=user_dashboard";</script>';
                } else {
                    echo '<script>window.location.href = "?page=admin_dashboard";</script>';
                }
                exit;
            } else {
                $error = 'Dev login is not enabled. Enable it in Admin Settings.';
            }
        }

        // Normal login flow
        // Fetch Admin Credentials from database dynamic settings
        $admin_user = $db->query("SELECT value FROM settings WHERE key = 'admin_username'")->fetchColumn() ?: 'admin@greenleaf.com';
        $admin_pass_hash = $db->query("SELECT value FROM settings WHERE key = 'admin_password'")->fetchColumn();

        if ($email === $admin_user && $admin_pass_hash && Auth::verify_password($password, $admin_pass_hash)) {
            Auth::login_admin();
            echo '<script>window.location.href = "?page=admin_dashboard";</script>';
            exit;
        }

        // Fetch Student credentials from database users
        $user_stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $user_stmt->execute([$email]);
        $student = $user_stmt->fetch();

        if ($student) {
            $stored_hash = $student['password'] ?? null;
            $password_ok = false;

            if ($stored_hash && Auth::verify_password($password, $stored_hash)) {
                $password_ok = true;
            } elseif ($stored_hash === null) {
                // Migration: old accounts with no password — accept any of the legacy passwords
                if (in_array($password, ['student123', 'sazid123', 'password123'])) {
                    $password_ok = true;
                    // Hash and store the password for next login
                    $new_hash = Auth::hash_password($password);
                    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $student['id']]);
                }
            }

            if ($password_ok) {
                if (isset($student['trial_status']) && $student['trial_status'] === 'Revoked') {
                    $error = 'Your access has been revoked by an administrator.';
                } else {
                    Auth::login_student((int)$student['id']);
                    echo '<script>window.location.href = "?page=user_dashboard";</script>';
                    exit;
                }
            } else {
                $error = 'Invalid email address or security password. Please try again.';
            }
        } else {
            $error = 'Invalid email address or security password. Please try again.';
        }
    }

    if ($action === 'register') {
        $fname = trim($_POST['first_name'] ?? '');
        $lname = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!empty($fname) && !empty($email) && !empty($password)) {
            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } else {
                $check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetchColumn() > 0) {
                    $error = 'Email address is already registered. Sign in instead!';
                } else {
                    $hash = Auth::hash_password($password);
                    $beta = $db->query("SELECT value FROM settings WHERE key = 'beta_mode'")->fetchColumn();
                    $default_credits = $beta ? (int)($db->query("SELECT value FROM settings WHERE key = 'credit_override'")->fetchColumn() ?: 20) : 5;
                    $ins = $db->prepare("INSERT INTO users (first_name, last_name, email, password, current_plan, ai_credits) VALUES (?, ?, ?, ?, 'Starter Launch', ?)");
                    $ins->execute([$fname, $lname, $email, $hash, $default_credits]);
                    $new_id = $db->lastInsertId();

                    Auth::login_student((int)$new_id);
                    echo '<script>window.location.href = "?page=student_registration";</script>';
                    exit;
                }
            }
        } else {
            $error = 'All fields are required.';
        }
    }
    } // end CSRF check else
}

include __DIR__ . '/../components/common/head.php'; 
?>
<title>GreenLeaf Resume - <?= $mode === 'register' ? 'Create Account' : 'Sign In' ?></title>
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .dark .glass-panel {
        background: rgba(42, 49, 61, 0.85);
    }
</style>
</head>
<body class="bg-surface text-on-surface font-body-md min-h-screen flex selection:bg-primary selection:text-white">

<!-- Left Presentation Side (Hidden on mobile) -->
<div class="hidden lg:flex flex-col justify-between w-1/2 bg-primary p-12 relative overflow-hidden text-on-primary">
    <!-- Background Decor -->
    <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/3 w-96 h-96 bg-primary-fixed/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-[500px] h-[500px] bg-surface-tint/40 rounded-full blur-3xl"></div>
    
    <div class="relative z-10">
        <a href="?page=landing" class="flex items-center gap-2 font-headline-md font-bold text-white hover:opacity-80 transition-opacity w-max">
            <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">energy_savings_leaf</span>
            GreenLeaf Resume
        </a>
    </div>

    <div class="relative z-10 max-w-lg mt-20">
        <h2 class="font-headline-xl text-5xl leading-tight font-bold mb-6">
            Structure your success story.
        </h2>
        <p class="font-body-lg text-xl opacity-90 mb-12">
            Join the platform that helps professionals build resumes that get noticed, pass ATS filters, and land interviews.
        </p>
        
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-4 bg-black/10 p-4 rounded-2xl backdrop-blur-sm border border-white/10 w-max">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                    <span class="material-symbols-outlined">auto_awesome</span>
                </div>
                <div>
                    <h4 class="font-label-md text-lg">AI Content Assistant</h4>
                    <p class="font-label-sm opacity-80">Write bullet points that shine</p>
                </div>
            </div>
            <div class="flex items-center gap-4 bg-black/10 p-4 rounded-2xl backdrop-blur-sm border border-white/10 w-max ml-12">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                    <span class="material-symbols-outlined">design_services</span>
                </div>
                <div>
                    <h4 class="font-label-md text-lg">Premium Templates</h4>
                    <p class="font-label-sm opacity-80">Stand out visually</p>
                </div>
            </div>
        </div>
    </div>

    <div class="relative z-10 mt-auto pt-20">
        <p class="font-label-sm opacity-60">© 2026 GreenLeaf Resume. Built by Wribix</p>
    </div>
</div>

<!-- Right Auth Side -->
<div class="w-full lg:w-1/2 flex items-center justify-center p-6 relative">
    
    <div class="w-full max-w-md glass-panel p-8 md:p-12 rounded-3xl border border-outline-variant/30 shadow-2xl relative z-10">
        
        <div class="lg:hidden flex items-center justify-center gap-2 font-headline-md font-bold text-primary mb-8">
            <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">energy_savings_leaf</span>
            GreenLeaf Resume
        </div>

        <div class="text-center mb-6">
            <h1 class="font-headline-lg text-3xl text-on-surface mb-2">
                <?= $mode === 'register' ? 'Create an account' : 'Welcome back' ?>
            </h1>
            <p class="font-body-md text-on-surface-variant">
                <?= $mode === 'register' ? 'Start building your career profile today.' : 'Sign in to continue your progress.' ?>
            </p>
        </div>

        <!-- Strict Access Alerts -->
        <?php if (isset($_GET['err']) && $_GET['err'] === 'unauthorized_admin'): ?>
          <div class="mb-5 p-3.5 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 text-xs font-semibold animate-fade-in border border-error/20">
            <span class="material-symbols-outlined text-base">lock</span>
            <span>Strict Access Gate: Admin authentication required to access dashboard!</span>
          </div>
        <?php elseif (isset($_GET['err']) && $_GET['err'] === 'unauthorized_student'): ?>
          <div class="mb-5 p-3.5 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 text-xs font-semibold animate-fade-in border border-error/20">
            <span class="material-symbols-outlined text-base">lock</span>
            <span>Access Blocked: Please sign in as a student to access resume tools!</span>
          </div>
        <?php endif; ?>

        <!-- Form Validation Errors -->
        <?php if (!empty($error)): ?>
          <div class="mb-5 p-3.5 bg-error-container text-on-error-container rounded-xl flex items-center gap-2 text-xs font-bold animate-fade-in border border-error/20">
            <span class="material-symbols-outlined text-base">error</span>
            <span><?= htmlspecialchars($error) ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" class="flex flex-col gap-5">
            <input type="hidden" name="action" value="<?= htmlspecialchars($mode) ?>">
            <?= Auth::csrf_field() ?>
            
            <?php if ($mode === 'register'): ?>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-on-surface-variant font-bold text-xs" for="fname">First Name</label>
                    <input id="fname" type="text" name="first_name" class="w-full p-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/50 font-semibold text-xs" placeholder="Jane" required>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="font-label-md text-on-surface-variant font-bold text-xs" for="lname">Last Name</label>
                    <input id="lname" type="text" name="last_name" class="w-full p-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/50 font-semibold text-xs" placeholder="Doe" required>
                </div>
            </div>
            <?php endif; ?>

            <div class="flex flex-col gap-1.5">
                <label class="font-label-md text-on-surface-variant font-bold text-xs" for="email">Email Address</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">mail</span>
                    <input id="email" type="email" name="email" class="w-full pl-10 pr-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/50 font-semibold text-xs" placeholder="your@email.com" required>
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <div class="flex justify-between items-center">
                    <label class="font-label-md text-on-surface-variant font-bold text-xs" for="password">Password</label>
                    <?php if ($mode === 'login'): ?>
                    <a href="#" class="font-label-sm text-primary hover:underline text-xs font-semibold">Forgot password?</a>
                    <?php endif; ?>
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">lock</span>
                    <input id="password" type="password" name="password" class="w-full pl-10 pr-10 py-3 bg-surface-container-lowest border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all placeholder:text-outline/50 font-semibold text-xs" placeholder="••••••••" required>
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface">
                        <span class="material-symbols-outlined text-sm">visibility</span>
                    </button>
                </div>
            </div>

            <?php if ($mode === 'login'): ?>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="remember" class="rounded border-outline-variant text-primary focus:ring-primary w-4 h-4">
                <label for="remember" class="font-label-md text-on-surface-variant cursor-pointer text-xs font-semibold">Remember me for 30 days</label>
            </div>
            <?php endif; ?>

            <button type="submit" class="w-full bg-primary text-on-primary py-3.5 rounded-xl font-label-md text-base font-bold hover:shadow-lg active:scale-[0.98] transition-all mt-2 flex items-center justify-center gap-2">
                <?= $mode === 'register' ? 'Create Account' : 'Sign In' ?>
                <span class="material-symbols-outlined text-sm">login</span>
            </button>
        </form>

        <div class="mt-6 flex items-center gap-4">
            <div class="h-px bg-outline-variant/50 flex-1"></div>
            <span class="font-label-sm text-outline text-[10px] font-bold">OR CONTINUE WITH</span>
            <div class="h-px bg-outline-variant/50 flex-1"></div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4">
            <button class="flex items-center justify-center gap-2 py-2 border border-outline-variant rounded-xl hover:bg-surface-container transition-colors font-label-md text-on-surface text-xs font-bold">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-4 h-4" alt="Google">
                Google
            </button>
            <button class="flex items-center justify-center gap-2 py-2 border border-outline-variant rounded-xl hover:bg-surface-container transition-colors font-label-md text-on-surface text-xs font-bold">
                <img src="https://www.svgrepo.com/show/448234/linkedin.svg" class="w-4 h-4" alt="LinkedIn">
                LinkedIn
            </button>
        </div>

        <p class="text-center mt-8 font-label-md text-on-surface-variant text-xs font-semibold">
            <?= $mode === 'register' ? 'Already have an account?' : 'Don\'t have an account?' ?> 
            <a href="?page=auth&mode=<?= $mode === 'register' ? 'login' : 'register' ?>" class="text-primary hover:underline font-extrabold">
                <?= $mode === 'register' ? 'Sign in' : 'Sign up' ?>
            </a>
        </p>


    </div>
</div>

</body>
</html>
