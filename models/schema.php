<?php
class Schema {
    private static $dbInstance = null;

    public static function getDB() {
        if (self::$dbInstance === null) {
            self::$dbInstance = new PDO('sqlite:' . __DIR__ . '/../sqlite/database.sqlite');
            self::$dbInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$dbInstance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$dbInstance;
    }

    public static function init() {
        $db = self::getDB();

        // 1. Users Table (including plan, ai_credits, max_resumes limit tracking columns)
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT,
            last_name TEXT,
            email TEXT,
            phone TEXT,
            location TEXT,
            trial_status TEXT DEFAULT 'Active',
            usage INTEGER DEFAULT 0,
            last_payment TEXT,
            current_plan TEXT DEFAULT 'Starter Launch',
            plan_expiry TEXT,
            plan_subscribed_at TEXT,
            ai_credits INTEGER DEFAULT 5,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Seed demo student user if empty
        $check_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($check_users == 0) {
            $stmt = $db->prepare("INSERT INTO users (first_name, last_name, email, phone, location, current_plan, ai_credits) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Sazid', 'Ahmed', 'sazid@startup.com', '+1 (555) 019-2834', 'Silicon Valley, CA', 'Starter Launch', 5]);
            $stmt->execute(['Student', 'Demo', 'student@greenleaf.com', '+1 (555) 000-0000', 'Boston, MA', 'Starter Launch', 5]);
        }

        // 2. Plans Table
        $db->exec("CREATE TABLE IF NOT EXISTS plans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE,
            price REAL,
            duration_days INTEGER DEFAULT 30,
            access_paid_templates INTEGER DEFAULT 0,
            max_resumes INTEGER DEFAULT 2,
            ai_credits INTEGER DEFAULT 10,
            features TEXT,
            status TEXT DEFAULT 'Active'
        )");

        // Migrations: granular permission columns on plans
        try { $db->exec("ALTER TABLE plans ADD COLUMN perm_ai_modify INTEGER DEFAULT 0"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE plans ADD COLUMN perm_web_speech INTEGER DEFAULT 0"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE plans ADD COLUMN perm_custom_profiles INTEGER DEFAULT 0"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE plans ADD COLUMN perm_pdf_print INTEGER DEFAULT 0"); } catch (Exception $e) {}

        // Seed default plans if empty
        $check_plans = $db->query("SELECT COUNT(*) FROM plans")->fetchColumn();
        if ($check_plans == 0) {
            $insert_plan = $db->prepare("INSERT INTO plans (name, price, duration_days, access_paid_templates, max_resumes, ai_credits, features, status, perm_ai_modify, perm_web_speech, perm_custom_profiles, perm_pdf_print) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_plan->execute(['Starter Launch', 0.00, 30, 0, 2, 5, '2 Resumes Limit,Standard layouts only,No AI dynamic optimizations', 'Active', 0, 0, 0, 0]);
            $insert_plan->execute(['Pro Career Growth', 19.99, 30, 1, 10, 50, '10 Resumes limit,Morph AI Custom Modify widget,Dynamic duplicate & PDF printing', 'Active', 1, 1, 1, 1]);
            $insert_plan->execute(['Elite Career Advanced', 49.99, 30, 1, 9999, 500, 'Unlimited resumes generation,Unlimited Web Speech API,Career growth metrics dashboard', 'Active', 1, 1, 1, 1]);
        } else {
            // Ensure existing plans have correct permission flags (for upgrades)
            $db->exec("UPDATE plans SET perm_ai_modify = 1, perm_web_speech = 1, perm_custom_profiles = 1, perm_pdf_print = 1 WHERE name IN ('Pro Career Growth', 'Elite Career Advanced')");
        }

        // 3. Settings Table
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT
        )");

        // Insert default settings if empty
        $stmt = $db->query("SELECT COUNT(*) FROM settings");
        if ($stmt->fetchColumn() == 0) {
            $db->exec("INSERT INTO settings (key, value) VALUES ('groq_api_key', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('admin_username', 'admin@greenleaf.com')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('admin_password', 'admin123')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('platform_currency', 'USD')");
            
            // OAuth and SMTP configuration keys
            $db->exec("INSERT INTO settings (key, value) VALUES ('oauth_google_client_id', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('oauth_google_client_secret', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('oauth_google_redirect_uri', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('smtp_host', 'smtp.mailtrap.io')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('smtp_port', '2525')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('smtp_username', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('smtp_password', '')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('smtp_encryption', 'tls')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_mode', '0')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('dev_login_enabled', '0')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_global_credits', '50')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_perm_ai_modify', '1')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_perm_web_speech', '1')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_perm_custom_profiles', '1')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_perm_pdf_print', '1')");
            $db->exec("INSERT INTO settings (key, value) VALUES ('beta_perm_paid_templates', '1')");
        } else {
            // Ensure beta settings exist for older DBs
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_mode', '0')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('dev_login_enabled', '0')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_global_credits', '50')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_perm_ai_modify', '1')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_perm_web_speech', '1')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_perm_custom_profiles', '1')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_perm_pdf_print', '1')"); } catch (Exception $e) {}
            try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('beta_perm_paid_templates', '1')"); } catch (Exception $e) {}
        }

        // 4. Resumes Table
        $db->exec("CREATE TABLE IF NOT EXISTS resumes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            job_profile TEXT,
            template TEXT,
            ai_content TEXT,
            status TEXT DEFAULT 'completed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Migration: add columns if missing (for existing DBs)
        try { $db->exec("ALTER TABLE resumes ADD COLUMN ai_content TEXT"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE resumes ADD COLUMN status TEXT DEFAULT 'completed'"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE resumes ADD COLUMN last_error TEXT"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE users ADD COLUMN password TEXT"); } catch (Exception $e) {}

        // 5. Personal Details Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_personal (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            full_name TEXT,
            email TEXT,
            phone TEXT,
            dob TEXT,
            gender TEXT,
            nationality TEXT,
            address TEXT,
            city TEXT,
            linkedin TEXT,
            github TEXT,
            portfolio TEXT,
            summary TEXT
        )");

        // 6. Academics Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_academics (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            degree TEXT,
            institution TEXT,
            board_university TEXT,
            start_year TEXT,
            end_year TEXT,
            grade TEXT,
            description TEXT
        )");

        // 7. Experience Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_experience (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            job_title TEXT,
            company TEXT,
            location TEXT,
            start_date TEXT,
            end_date TEXT,
            is_current INTEGER DEFAULT 0,
            description TEXT
        )");

        // 8. Skills Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_skills (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            skill_name TEXT,
            proficiency TEXT
        )");

        // 9. Projects Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            title TEXT,
            tech_stack TEXT,
            url TEXT,
            start_date TEXT,
            end_date TEXT,
            description TEXT
        )");

        // 10. Achievements Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_achievements (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            title TEXT,
            issuer TEXT,
            date TEXT,
            description TEXT
        )");

        // 11. Hobbies Table
        $db->exec("CREATE TABLE IF NOT EXISTS profile_hobbies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            hobby TEXT
        )");

        // 12. Job Profiles Table
        $db->exec("CREATE TABLE IF NOT EXISTS job_profiles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE,
            description TEXT,
            icon TEXT DEFAULT 'work'
        )");

        // Seed default job profiles if empty
        $check_profiles = $db->query("SELECT COUNT(*) FROM job_profiles")->fetchColumn();
        if ($check_profiles == 0) {
            $default_profiles = [
                ['Python Developer', 'Backend logic, data pipelines, and automation expertise.', 'terminal'],
                ['Full Stack Developer', 'Bridging the gap between frontend beauty and backend power.', 'layers'],
                ['Data Scientist', 'Statistical modeling, predictive insights, and storytelling with data.', 'monitoring'],
                ['Project Manager', 'Strategic planning, team leadership, and delivery excellence.', 'assignment_ind']
            ];
            $insert_profile = $db->prepare("INSERT INTO job_profiles (name, description, icon) VALUES (?, ?, ?)");
            foreach ($default_profiles as $p) {
                $insert_profile->execute($p);
            }
        }

        // 13. Support Tickets Table
        $db->exec("CREATE TABLE IF NOT EXISTS tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            subject TEXT,
            category TEXT,
            description TEXT,
            status TEXT DEFAULT 'Open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 14. Notifications Table
        $db->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER DEFAULT 1,
            title TEXT,
            message TEXT,
            type TEXT DEFAULT 'Info',
            is_read INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 15. Templates Table (resume layout catalog)
        $db->exec("CREATE TABLE IF NOT EXISTS templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE,
            description TEXT,
            type TEXT DEFAULT 'Free',
            accent_color TEXT DEFAULT '#006C49',
            icon TEXT DEFAULT 'description',
            image_url TEXT,
            status TEXT DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Seed default templates if empty
        $check_templates = $db->query("SELECT COUNT(*) FROM templates")->fetchColumn();
        if ($check_templates == 0) {
            $defaults = [
                ['Minimalist', 'Clean, simple, high-readability layout with centered header. Perfect for traditional industries.', 'Free', '#006C49', 'menu', 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZA7r6UjZ0X_2M3uoO5v6fRfsgO3993aGR8wE6uIWikqbD3si8T8o6Tl_wXPEAAoz4HPSm6H98ClUtPLPEvWaUNKE4K4_1BHzfJf645Ub8siv0woX0wjvjy4vznle-xAS7n334tvmeS8fd_QERiniE1hgtG4MDVL_UEXamRrAxLr9BpksmuW3-fZE-a33wnL4TV5oe5YQdK_zOAxYPKuMCMnUdE5iFG0M0UglxcGvLeKqRmBZQYBGsbCduXQVs6a5rpYc5Brq_1to'],
                ['Standard Modern', 'Universal two-column structure for dynamic student internships and job applications.', 'Free', '#006C49', 'grid_view', 'https://lh3.googleusercontent.com/aida-public/AB6AXuAJhse6ro0YjBWWSXqy5pZ6Dc0UbgnRV5MIMpMLY3FJjjiCadbN7VQb4RaUmX4Pu9Pk_hlgCv1XdY5atwNpusynXoEd6iaNOww-wsNosMDZBooK90Tb6-aRCI9vFgcqUT_S7Xse0JHdP_NDUuKWgqPVhw0Jt_35vQdM0rLA-GY2BraHFKU9drMN7HrcpDn4HMUt7ATfH6hJVOEyP4Za6_07qgRFDjgGVhfDIvDGHX-fC3mRlwefMDn0SzldXyP4hSHK59HF848E9gA'],
                ['Creative Leaf', 'Vibrant layout featuring forest accents and premium asymmetric curves to stand out instantly.', 'Free', '#006C49', 'spa', 'https://lh3.googleusercontent.com/aida-public/AB6AXuD5XlefmKgq0tgllhUxEpsF3dhjNULfCyRXkfmycy098xnbFPGk4d2WMX08v51WcznZlP1oOePpc1svDczUk5xqjR4BPQiKNAPHP0m7c58UtUDQqzRT0caC_2HhDHHxNIEN8Ap1PJBLXA4DBF1CjdmyrQZNN9QQys6a1yePl3CCL18FIRUp7dzDA45i4K0xvCgzkynJ1JrKjJ8SJQ8024BBH8RBoCjSyHBk7bVASzGsgpfW0C5wN-BBN2lD0EWB9v7sVnnY71zmDrc'],
                ['Modern Tech Pro', 'Engineered specific sections for tech stacks, dynamic project links, and code highlights.', 'Paid', '#0F2C59', 'terminal', ''],
                ['Executive Elite', 'High-end classy serif alignments tailored for product managers, directors, and strategic leadership roles.', 'Paid', '#1A1A2E', 'military_tech', ''],
            ];
            $ins = $db->prepare("INSERT INTO templates (name, description, type, accent_color, icon, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($defaults as $t) $ins->execute($t);
        }

        // 16. Plan Templates Junction Table (which paid templates each plan includes)
        $db->exec("CREATE TABLE IF NOT EXISTS plan_templates (
            plan_id INTEGER,
            template_id INTEGER,
            PRIMARY KEY (plan_id, template_id)
        )");

        // Seed default associations: Pro Career Growth gets the 2 paid templates
        $check_pt = $db->query("SELECT COUNT(*) FROM plan_templates")->fetchColumn();
        if ($check_pt == 0) {
            $pro = $db->query("SELECT id FROM plans WHERE name = 'Pro Career Growth' LIMIT 1")->fetch();
            $elite = $db->query("SELECT id FROM plans WHERE name = 'Elite Career Advanced' LIMIT 1")->fetch();
            $paid = $db->query("SELECT id FROM templates WHERE type = 'Paid'")->fetchAll(PDO::FETCH_COLUMN);

            $ins_pt = $db->prepare("INSERT OR IGNORE INTO plan_templates (plan_id, template_id) VALUES (?, ?)");
            if ($pro) foreach ($paid as $tid) $ins_pt->execute([$pro['id'], $tid]);
            if ($elite) foreach ($paid as $tid) $ins_pt->execute([$elite['id'], $tid]);
        }
        // 17. Feedbacks Table
        $db->exec("CREATE TABLE IF NOT EXISTS feedbacks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Feedback setting
        try { $db->exec("INSERT OR IGNORE INTO settings (key, value) VALUES ('feedback_enabled', '0')"); } catch (Exception $e) {}
    }

    public static function close() {
        self::$dbInstance = null;
    }
}
?>
