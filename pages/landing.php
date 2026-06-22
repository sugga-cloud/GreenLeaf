<?php include __DIR__ . '/../components/common/head.php'; ?>
<title>GreenLeaf Resume - AI-Powered Resume Builder</title>
<style>
    .hero-gradient {
        background: linear-gradient(135deg, rgba(21, 28, 39, 0.95) 0%, rgba(0, 108, 73, 0.9) 100%);
    }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    .float-anim { animation: float 6s ease-in-out infinite; }
    @keyframes float-delay {
        0% { transform: translateY(0px) rotate(3deg); }
        50% { transform: translateY(-14px) rotate(3deg); }
        100% { transform: translateY(0px) rotate(3deg); }
    }
    .float-anim-delay { animation: float-delay 5s ease-in-out 1s infinite; }
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .scroll-reveal { opacity: 0; transform: translateY(30px); transition: all 0.7s ease-out; }
    .scroll-reveal.visible { opacity: 1; transform: translateY(0); }
    .template-card { transition: transform 0.3s, box-shadow 0.3s; }
    .template-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
    .step-line { position: relative; }
    .step-line::after { content:''; position:absolute; top:50%; right:-24px; width:48px; height:2px; background: #006c49; opacity:0.3; }
    .step-line:last-child::after { display:none; }
    .faq-answer { max-height:0; overflow:hidden; transition: max-height 0.3s ease-out; }
    .faq-answer.open { max-height:300px; }
    .pricing-popular { position:relative; }
    .pricing-popular::before { content:'MOST POPULAR'; position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:#006c49; color:#fff; padding:2px 12px; border-radius:99px; font-size:10px; font-weight:700; letter-spacing:0.05em; }
</style>
</head>
<body class="bg-background text-on-background font-body-md min-h-screen flex flex-col selection:bg-primary selection:text-white">

<!-- Navigation -->
<nav class="fixed top-0 w-full z-50 bg-surface/80 backdrop-blur-md shadow-sm transition-all duration-300">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop py-4 flex justify-between items-center">
        <div class="flex items-center gap-2 text-primary font-headline-md font-bold">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">energy_savings_leaf</span>
            <span>GreenLeaf Resume</span>
        </div>
        <div class="hidden md:flex gap-8 items-center">
            <a href="#features" class="font-label-md text-on-surface-variant hover:text-primary transition-colors">Features</a>
            <a href="#templates" class="font-label-md text-on-surface-variant hover:text-primary transition-colors">Templates</a>
            <a href="#how-it-works" class="font-label-md text-on-surface-variant hover:text-primary transition-colors">How It Works</a>
            <a href="#pricing" class="font-label-md text-on-surface-variant hover:text-primary transition-colors">Pricing</a>
            <a href="#faq" class="font-label-md text-on-surface-variant hover:text-primary transition-colors">FAQ</a>
            <a href="?page=auth" class="font-label-md text-primary font-bold hover:opacity-80 transition-opacity">Sign In</a>
            <a href="?page=auth&mode=register" class="bg-primary text-on-primary px-6 py-2 rounded-full font-label-md shadow-md hover:shadow-lg active:scale-95 transition-all">Get Started Free</a>
        </div>
        <div class="md:hidden flex items-center gap-3">
            <a href="?page=auth" class="text-primary font-bold text-sm">Sign In</a>
            <a href="?page=auth&mode=register" class="bg-primary text-on-primary px-4 py-2 rounded-full text-xs font-bold">Join</a>
        </div>
    </div>
</nav>
<?php include __DIR__ . '/../components/common/announcement_banner.php'; ?>

<!-- Hero Section -->
<header class="relative pt-28 pb-20 lg:pt-40 lg:pb-28 overflow-hidden flex-shrink-0">
    <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-[800px] h-[800px] bg-primary/10 rounded-full blur-[100px] -z-10 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-[600px] h-[600px] bg-secondary/10 rounded-full blur-[80px] -z-10 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop flex flex-col lg:flex-row items-center gap-12 lg:gap-20 relative z-10">
        <div class="w-full lg:w-1/2 flex flex-col items-center text-center lg:items-start lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-surface-container-high border border-outline-variant/30 text-primary font-label-sm mb-6">
                <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">auto_awesome</span>
                <span>AI-Powered Resume Builder</span>
            </div>
            <h1 class="font-headline-xl text-5xl md:text-6xl lg:text-[72px] leading-tight text-on-surface mb-6 tracking-tight">
                Grow your career with <span class="text-primary relative inline-block">structure.
                    <svg class="absolute w-full h-4 -bottom-1 left-0 text-secondary-fixed opacity-70" viewBox="0 0 100 10" preserveAspectRatio="none">
                        <path d="M0,5 Q50,10 100,2" stroke="currentColor" stroke-width="3" fill="transparent" />
                    </svg>
                </span>
            </h1>
            <p class="font-body-lg text-xl text-on-surface-variant max-w-xl mb-8">
                Upload your existing resume or build from scratch. Our AI tailors every line to your target job, rewrites weak bullets, and formats everything into a recruiter-ready PDF — in under 60 seconds.
            </p>
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full justify-center lg:justify-start">
                <a href="?page=auth&mode=register" class="w-full sm:w-auto bg-primary text-on-primary px-10 py-4 rounded-full font-label-md text-lg shadow-xl shadow-primary/20 hover:shadow-2xl hover:-translate-y-1 active:scale-95 transition-all flex items-center justify-center gap-2">
                    Build My Resume Free
                    <span class="material-symbols-outlined">arrow_forward</span>
                </a>
                <a href="#how-it-works" class="w-full sm:w-auto px-8 py-4 rounded-full font-label-md text-lg text-on-surface hover:bg-surface-container transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">play_circle</span>
                    See how it works
                </a>
            </div>
            <div class="mt-8 flex flex-wrap items-center gap-6 text-on-surface-variant text-sm">
                <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Free to start</div>
                <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> No credit card</div>
                <div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> AI-powered</div>
            </div>
        </div>

        <!-- Hero Visual -->
        <div class="w-full lg:w-1/2 relative lg:h-[550px] flex items-center justify-center">
            <div class="w-full max-w-[380px] aspect-[1/1.3] bg-white rounded-2xl shadow-2xl overflow-hidden float-anim relative z-20 border border-outline-variant/20">
                <div class="w-full h-1/4 bg-primary p-6 flex flex-col justify-end text-white">
                    <div class="w-14 h-14 rounded-full bg-white/20 backdrop-blur mb-2 border border-white/40"></div>
                    <div class="h-5 w-3/4 bg-white/90 rounded mb-1.5"></div>
                    <div class="h-2.5 w-1/2 bg-white/60 rounded"></div>
                </div>
                <div class="p-5 flex flex-col gap-3">
                    <div class="h-3.5 w-1/4 bg-surface-variant rounded"></div>
                    <div class="h-1.5 w-full bg-surface-container rounded"></div>
                    <div class="h-1.5 w-full bg-surface-container rounded"></div>
                    <div class="h-1.5 w-5/6 bg-surface-container rounded"></div>
                    <div class="h-3.5 w-1/4 bg-surface-variant rounded mt-2"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><div class="h-2.5 w-3/4 bg-surface-container-high rounded mb-1.5"></div><div class="h-1.5 w-full bg-surface-container rounded mb-1"></div><div class="h-1.5 w-full bg-surface-container rounded"></div></div>
                        <div><div class="h-2.5 w-3/4 bg-surface-container-high rounded mb-1.5"></div><div class="h-1.5 w-full bg-surface-container rounded mb-1"></div><div class="h-1.5 w-full bg-surface-container rounded"></div></div>
                    </div>
                    <div class="h-3.5 w-1/4 bg-surface-variant rounded mt-2"></div>
                    <div class="flex flex-wrap gap-1.5">
                        <div class="h-5 w-14 bg-primary/10 rounded-full border border-primary/20"></div>
                        <div class="h-5 w-18 bg-primary/10 rounded-full border border-primary/20"></div>
                        <div class="h-5 w-12 bg-primary/10 rounded-full border border-primary/20"></div>
                    </div>
                </div>
            </div>
            <div class="absolute top-8 -left-8 lg:-left-16 bg-surface p-3.5 rounded-xl shadow-lg border border-outline-variant/30 flex items-center gap-3 z-30 transform -rotate-6 float-anim" style="animation-duration:5s;">
                <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-primary"><span class="material-symbols-outlined" style="font-size:20px;">auto_fix_high</span></div>
                <div><p class="font-label-md text-on-surface text-xs">AI Rewriting</p><p class="text-primary font-bold text-sm">Active</p></div>
            </div>
            <div class="absolute bottom-16 -right-4 lg:-right-10 bg-surface p-3.5 rounded-xl shadow-lg border border-outline-variant/30 flex items-center gap-3 z-30 float-anim-delay">
                <div class="w-9 h-9 rounded-full bg-secondary/10 flex items-center justify-center text-secondary"><span class="material-symbols-outlined" style="font-size:20px;">insights</span></div>
                <div><p class="font-label-md text-on-surface text-xs">ATS Score</p><p class="text-primary font-bold text-sm">95%</p></div>
            </div>
        </div>
    </div>
</header>

<!-- Stats Bar -->
<section class="py-8 bg-surface-container-low border-y border-outline-variant/20">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div><p class="font-headline-lg text-3xl text-primary font-bold">10K+</p><p class="font-label-sm text-on-surface-variant mt-1">Resumes Created</p></div>
        <div><p class="font-headline-lg text-3xl text-primary font-bold">5</p><p class="font-label-sm text-on-surface-variant mt-1">Premium Templates</p></div>
        <div><p class="font-headline-lg text-3xl text-primary font-bold">95%</p><p class="font-label-sm text-on-surface-variant mt-1">ATS Pass Rate</p></div>
        <div><p class="font-headline-lg text-3xl text-primary font-bold">60s</p><p class="font-label-sm text-on-surface-variant mt-1">Avg. Generation</p></div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-24 bg-surface-container-lowest relative">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 max-w-2xl mx-auto scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">star</span> Powerful Features
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">Everything you need to land your dream job</h2>
            <p class="font-body-lg text-on-surface-variant">From AI-powered content generation toATS-optimized formatting — we handle the heavy lifting so you can focus on what matters.</p>
        </div>

        <!-- Feature 1: AI Resume Generation (Full Width) -->
        <div class="scroll-reveal mb-8 md:col-span-2 bg-gradient-to-br from-primary/5 to-secondary/5 p-10 md:p-12 rounded-3xl border border-primary/10 relative overflow-hidden group">
            <div class="absolute -right-16 -bottom-16 opacity-5 text-primary group-hover:scale-110 transition-transform duration-700">
                <span class="material-symbols-outlined" style="font-size:250px;">psychology</span>
            </div>
            <div class="flex flex-col md:flex-row items-start gap-8 relative z-10">
                <div class="w-16 h-16 rounded-2xl bg-primary flex items-center justify-center text-white shadow-md shrink-0">
                    <span class="material-symbols-outlined text-3xl">auto_fix_high</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-2xl text-on-surface mb-3">AI-Powered Resume Generation</h3>
                    <p class="font-body-md text-on-surface-variant max-w-2xl mb-4">Our AI engine (powered by Llama 3.3 70B) analyzes your profile data and target job description to generate compelling, tailored resume content. It picks the strongest action verbs, quantifies achievements, and aligns your experience with what recruiters actually look for.</p>
                    <div class="flex flex-wrap gap-3">
                        <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">Smart Bullet Points</span>
                        <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">Job-Tailored Content</span>
                        <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">Action Verb Optimization</span>
                        <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold">Achievement Quantification</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 2: PDF Import -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-tertiary-container flex items-center justify-center text-tertiary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">upload_file</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">PDF Resume Import</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Already have a resume? Upload your PDF and our AI extracts all sections — education, experience, skills, projects — into structured data instantly.</p>
            </div>

            <!-- Feature 3: AI Chat Modify -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-secondary-container flex items-center justify-center text-secondary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">chat</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">AI Chat Agent</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Want to change something? Just tell the AI in plain English: "Make my summary shorter", "Add more leadership keywords", "Rewrite my experience at Google" — and watch it update live.</p>
            </div>

            <!-- Feature 4: Premium Templates -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">palette</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">5 Premium Templates</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Choose from Minimalist, Bold Creative, Classic, Modern, and Elegant — each designed by typography experts to pass ATS filters and impress hiring managers.</p>
            </div>

            <!-- Feature 5: Job Profile Matching -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-error/10 flex items-center justify-center text-error mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">work</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Job Profile Targeting</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Select your target role — Software Engineer, Product Manager, Data Scientist, Marketing Lead, UX Designer — and the AI tailors every bullet point to match that specific job.</p>
            </div>

            <!-- Feature 6: Profile Builder -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-tertiary/10 flex items-center justify-center text-tertiary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">person</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">7-Step Profile Builder</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Build your profile section by section: Personal info, Academics, Experience, Skills, Projects, Achievements, and Hobbies. Each step is guided with smart defaults.</p>
            </div>

            <!-- Feature 7: Real-time Preview -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">visibility</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Live Resume Preview</h3>
                <p class="font-body-md text-on-surface-variant text-sm">See exactly how your resume looks before downloading. Toggle between AI-generated and raw profile data, switch templates instantly, and export as print-ready PDF.</p>
            </div>

            <!-- Feature 8: ATS Optimization (Full Width) -->
            <div class="scroll-reveal md:col-span-3 bg-inverse-surface text-surface-container-lowest p-10 rounded-3xl relative overflow-hidden group">
                <div class="absolute -right-10 -bottom-10 opacity-10 text-primary-fixed-dim group-hover:rotate-12 transition-transform duration-700">
                    <span class="material-symbols-outlined" style="font-size:200px;">speed</span>
                </div>
                <div class="flex flex-col md:flex-row items-start gap-8 relative z-10">
                    <div class="w-16 h-16 rounded-2xl bg-primary-fixed text-on-primary-fixed flex items-center justify-center shadow-md shrink-0">
                        <span class="material-symbols-outlined text-3xl">fact_check</span>
                    </div>
                    <div>
                        <h3 class="font-headline-md text-2xl mb-3">ATS-Friendly Formatting</h3>
                        <p class="font-body-md opacity-80 max-w-2xl mb-4">Every template is engineered to parse perfectly by Applicant Tracking Systems (ATS) used by Fortune 500 companies. Proper heading hierarchy, clean text flow, and keyword-optimized layout ensure your resume never gets filtered out.</p>
                        <div class="flex flex-wrap gap-3">
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-bold text-primary-fixed">Clean Headings</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-bold text-primary-fixed">Keyword Optimized</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-bold text-primary-fixed">PDF Vector Output</span>
                            <span class="px-3 py-1 bg-white/10 rounded-full text-xs font-bold text-primary-fixed">No Tables/Columns</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature 9: Credit System -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center text-primary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">token</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Smart Credit System</h3>
                <p class="font-body-md text-on-surface-variant text-sm">AI generations use credits — so you only pay for what you need. Credits auto-refund if generation fails. Upgrade your plan for more.</p>
            </div>

            <!-- Feature 10: Notification System -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-tertiary/10 flex items-center justify-center text-tertiary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">notifications</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Smart Notifications</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Get notified when your resume finishes generating, when your plan is about to expire, or when there are new templates available. Stay on top of your job search.</p>
            </div>

            <!-- Feature 11: Template Store -->
            <div class="scroll-reveal bg-surface-container p-8 rounded-3xl border border-outline-variant/30 hover:border-primary/40 transition-all group">
                <div class="w-14 h-14 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary mb-5 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-2xl">storefront</span>
                </div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Template Store</h3>
                <p class="font-body-md text-on-surface-variant text-sm">Browse and unlock premium templates from the store. Free plans get classic templates; paid plans unlock designer-quality layouts with unique visual styles.</p>
            </div>
        </div>
    </div>
</section>

<!-- Templates Showcase -->
<section id="templates" class="py-24 bg-surface relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary/10 text-secondary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">palette</span> Beautiful Templates
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">Templates designed to impress</h2>
            <p class="font-body-lg text-on-surface-variant max-w-2xl mx-auto">Each template is crafted with precision typography, optimal whitespace, and ATS-compatible structure. Pick the one that matches your personality.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            <div class="template-card scroll-reveal bg-white rounded-2xl shadow-md border border-outline-variant/20 overflow-hidden cursor-pointer group">
                <div class="h-40 bg-gradient-to-b from-gray-100 to-white p-4 flex flex-col gap-2">
                    <div class="h-2.5 w-1/3 bg-gray-300 rounded"></div>
                    <div class="h-1.5 w-full bg-gray-200 rounded"></div>
                    <div class="h-1.5 w-4/5 bg-gray-200 rounded"></div>
                    <div class="h-1.5 w-full bg-gray-200 rounded"></div>
                    <div class="h-2.5 w-1/4 bg-gray-300 rounded mt-2"></div>
                    <div class="h-1.5 w-full bg-gray-200 rounded"></div>
                    <div class="h-1.5 w-3/4 bg-gray-200 rounded"></div>
                </div>
                <div class="p-3 text-center"><p class="font-label-md text-on-surface text-xs">Minimalist</p><p class="text-[10px] text-primary font-bold">Free</p></div>
            </div>
            <div class="template-card scroll-reveal bg-white rounded-2xl shadow-md border border-outline-variant/20 overflow-hidden cursor-pointer group" style="transition-delay:0.1s">
                <div class="h-40 p-4 flex flex-col gap-2 relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-red-500 opacity-10"></div>
                    <div class="relative h-2.5 w-1/3 bg-orange-400 rounded"></div>
                    <div class="relative h-1.5 w-full bg-orange-200 rounded"></div>
                    <div class="relative h-1.5 w-4/5 bg-orange-200 rounded"></div>
                    <div class="relative h-1.5 w-full bg-orange-200 rounded"></div>
                    <div class="relative h-2.5 w-1/4 bg-orange-400 rounded mt-2"></div>
                    <div class="relative h-1.5 w-full bg-orange-200 rounded"></div>
                </div>
                <div class="p-3 text-center"><p class="font-label-md text-on-surface text-xs">Bold Creative</p><p class="text-[10px] text-orange-500 font-bold">Pro</p></div>
            </div>
            <div class="template-card scroll-reveal bg-white rounded-2xl shadow-md border border-outline-variant/20 overflow-hidden cursor-pointer group" style="transition-delay:0.2s">
                <div class="h-40 p-4 flex flex-col gap-2">
                    <div class="h-2.5 w-1/3 bg-blue-800 rounded"></div>
                    <div class="h-1 w-full bg-blue-200 rounded"></div>
                    <div class="h-1 w-full bg-blue-200 rounded"></div>
                    <div class="h-1 w-4/5 bg-blue-200 rounded"></div>
                    <div class="h-0.5 w-full bg-blue-300 my-1"></div>
                    <div class="h-2.5 w-1/4 bg-blue-800 rounded"></div>
                    <div class="h-1 w-full bg-blue-200 rounded"></div>
                    <div class="h-1 w-3/4 bg-blue-200 rounded"></div>
                </div>
                <div class="p-3 text-center"><p class="font-label-md text-on-surface text-xs">Classic</p><p class="text-[10px] text-primary font-bold">Free</p></div>
            </div>
            <div class="template-card scroll-reveal bg-white rounded-2xl shadow-md border border-outline-variant/20 overflow-hidden cursor-pointer group" style="transition-delay:0.3s">
                <div class="h-40 p-4 flex flex-col gap-2 relative">
                    <div class="absolute top-0 left-0 w-1 h-full bg-emerald-500"></div>
                    <div class="pl-2 h-2.5 w-2/3 bg-emerald-600 rounded"></div>
                    <div class="pl-2 h-1.5 w-full bg-emerald-100 rounded"></div>
                    <div class="pl-2 h-1.5 w-4/5 bg-emerald-100 rounded"></div>
                    <div class="pl-2 h-2.5 w-1/3 bg-emerald-600 rounded mt-2"></div>
                    <div class="pl-2 h-1.5 w-full bg-emerald-100 rounded"></div>
                    <div class="pl-2 h-1.5 w-3/4 bg-emerald-100 rounded"></div>
                </div>
                <div class="p-3 text-center"><p class="font-label-md text-on-surface text-xs">Modern</p><p class="text-[10px] text-emerald-500 font-bold">Pro</p></div>
            </div>
            <div class="template-card scroll-reveal bg-white rounded-2xl shadow-md border border-outline-variant/20 overflow-hidden cursor-pointer group" style="transition-delay:0.4s">
                <div class="h-40 p-4 flex flex-col gap-2">
                    <div class="h-6 w-full bg-slate-800 rounded-sm flex items-center px-2"><div class="h-1.5 w-1/3 bg-white/60 rounded"></div></div>
                    <div class="h-1.5 w-full bg-slate-200 rounded"></div>
                    <div class="h-1.5 w-4/5 bg-slate-200 rounded"></div>
                    <div class="h-2 w-1/4 bg-slate-400 rounded mt-1"></div>
                    <div class="h-1.5 w-full bg-slate-200 rounded"></div>
                    <div class="h-1.5 w-full bg-slate-200 rounded"></div>
                    <div class="h-1.5 w-2/3 bg-slate-200 rounded"></div>
                </div>
                <div class="p-3 text-center"><p class="font-label-md text-on-surface text-xs">Elegant</p><p class="text-[10px] text-slate-500 font-bold">Elite</p></div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works" class="py-24 bg-surface-container-lowest border-t border-outline-variant/20">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">route</span> Simple Process
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">From signup to job-ready in 4 steps</h2>
            <p class="font-body-lg text-on-surface-variant">No complicated setup. No steep learning curve. Just sign up, fill your profile, pick a template, and let the AI do the rest.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="scroll-reveal text-center flex flex-col items-center">
                <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold mb-4 shadow-lg shadow-primary/30">1</div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Create Profile</h3>
                <p class="font-body-md text-on-surface-variant text-sm text-center">Sign up for free and build your profile — education, experience, skills, and more in our guided 7-step wizard.</p>
            </div>
            <div class="scroll-reveal text-center flex flex-col items-center" style="transition-delay:0.1s">
                <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold mb-4 shadow-lg shadow-primary/30">2</div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Pick a Template</h3>
                <p class="font-body-md text-on-surface-variant text-sm text-center">Choose from 5 designer-crafted templates. Or upload your existing PDF resume and let AI extract your data.</p>
            </div>
            <div class="scroll-reveal text-center flex flex-col items-center" style="transition-delay:0.2s">
                <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold mb-4 shadow-lg shadow-primary/30">3</div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">AI Generates</h3>
                <p class="font-body-md text-on-surface-variant text-sm text-center">Select your target job profile and our AI rewrites your content to match — optimizing for ATS and recruiter impact.</p>
            </div>
            <div class="scroll-reveal text-center flex flex-col items-center" style="transition-delay:0.3s">
                <div class="w-16 h-16 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold mb-4 shadow-lg shadow-primary/30">4</div>
                <h3 class="font-headline-md text-lg text-on-surface mb-2">Download & Apply</h3>
                <p class="font-body-md text-on-surface-variant text-sm text-center">Preview your resume live, tweak with the AI chat agent if needed, then download a print-ready PDF to send to recruiters.</p>
            </div>
        </div>
    </div>
</section>

<!-- AI Chat Agent Feature Highlight -->
<section class="py-24 bg-surface relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <div class="w-full lg:w-1/2 scroll-reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary/10 text-secondary font-label-sm mb-4">
                    <span class="material-symbols-outlined text-sm">chat</span> AI Assistant
                </div>
                <h2 class="font-headline-lg text-4xl text-on-surface mb-4">Talk to your resume like a human</h2>
                <p class="font-body-lg text-on-surface-variant mb-6">Our built-in AI Chat Agent understands natural language. Just type what you want to change and watch your resume update in real-time — no manual editing needed.</p>
                <div class="flex flex-col gap-3 mb-8">
                    <div class="flex items-start gap-3 p-3 bg-surface-container rounded-xl">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">auto_awesome</span>
                        <div><p class="font-label-md text-on-surface text-xs">"Make my summary more concise"</p></div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-surface-container rounded-xl">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">auto_awesome</span>
                        <div><p class="font-label-md text-on-surface text-xs">"Add leadership keywords to my experience"</p></div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-surface-container rounded-xl">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">auto_awesome</span>
                        <div><p class="font-label-md text-on-surface text-xs">"Rewrite my project description to sound more technical"</p></div>
                    </div>
                    <div class="flex items-start gap-3 p-3 bg-surface-container rounded-xl">
                        <span class="material-symbols-outlined text-primary text-lg mt-0.5">auto_awesome</span>
                        <div><p class="font-label-md text-on-surface text-xs">"Quantify my achievements with numbers"</p></div>
                    </div>
                </div>
                <a href="?page=auth&mode=register" class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 py-3 rounded-full font-label-md shadow-lg shadow-primary/20 hover:shadow-xl active:scale-95 transition-all">
                    Try AI Chat Free <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
            <div class="w-full lg:w-1/2 scroll-reveal" style="transition-delay:0.2s">
                <!-- Chat UI Mock -->
                <div class="bg-surface-container-lowest rounded-2xl shadow-2xl border border-outline-variant/30 overflow-hidden">
                    <div class="bg-primary text-white px-5 py-3 flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:20px;">smart_toy</span>
                        <span class="font-label-md text-sm">AI Resume Assistant</span>
                        <span class="ml-auto w-2 h-2 bg-green-400 rounded-full" style="animation: pulse-dot 2s infinite;"></span>
                    </div>
                    <div class="p-5 flex flex-col gap-3 max-h-80">
                        <div class="self-start bg-surface-container p-3 rounded-xl rounded-tl-sm max-w-[80%]">
                            <p class="text-xs text-on-surface">Hi! I've analyzed your resume. Your summary is strong but could be more concise. Want me to trim it?</p>
                        </div>
                        <div class="self-end bg-primary text-on-primary p-3 rounded-xl rounded-tr-sm max-w-[80%]">
                            <p class="text-xs">Yes, make it under 3 lines and focus on leadership</p>
                        </div>
                        <div class="self-start bg-surface-container p-3 rounded-xl rounded-tl-sm max-w-[80%]">
                            <p class="text-xs text-on-surface">Done! I've rewritten your summary to highlight leadership experience and kept it to 2 lines. <span class="text-primary font-bold">Live preview updated.</span></p>
                        </div>
                    </div>
                    <div class="border-t border-outline-variant/30 p-3 flex items-center gap-2">
                        <div class="flex-1 bg-surface rounded-full px-4 py-2 text-xs text-outline">Type your request...</div>
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center"><span class="material-symbols-outlined text-white" style="font-size:16px;">send</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-24 bg-surface-container-lowest border-t border-outline-variant/20">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">payments</span> Simple Pricing
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">Start free, upgrade when you're ready</h2>
            <p class="font-body-lg text-on-surface-variant max-w-2xl mx-auto">No hidden fees. No subscriptions that trap you. Pick the plan that fits your job search stage.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <!-- Starter -->
            <div class="scroll-reveal bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/30 flex flex-col">
                <h3 class="font-headline-md text-xl text-on-surface mb-2">Starter Launch</h3>
                <p class="font-label-sm text-on-surface-variant mb-4">Perfect for trying out the platform</p>
                <div class="mb-6"><span class="font-headline-xl text-4xl text-on-surface">Free</span></div>
                <ul class="flex flex-col gap-3 mb-8 flex-1">
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> 2 AI Resume Generations</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> 3 Free Templates</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Profile Builder</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> PDF Download</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> PDF Resume Import</li>
                </ul>
                <a href="?page=auth&mode=register" class="w-full py-3 rounded-xl font-label-md text-center border-2 border-primary text-primary hover:bg-primary hover:text-on-primary transition-all">Get Started Free</a>
            </div>
            <!-- Pro -->
            <div class="scroll-reveal pricing-popular bg-surface-container-lowest p-8 rounded-3xl border-2 border-primary flex flex-col relative" style="transition-delay:0.1s">
                <h3 class="font-headline-md text-xl text-on-surface mb-2">Pro Career Growth</h3>
                <p class="font-label-sm text-on-surface-variant mb-4">For active job seekers</p>
                <div class="mb-6"><span class="font-headline-xl text-4xl text-on-surface">$9.99</span><span class="text-on-surface-variant text-sm">/month</span></div>
                <ul class="flex flex-col gap-3 mb-8 flex-1">
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> 50 AI Resume Generations</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> All 5 Premium Templates</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> AI Chat Resume Modifier</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Priority AI Processing</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Unlimited Profile Updates</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> PDF Import + Edit</li>
                </ul>
                <a href="?page=auth&mode=register" class="w-full py-3 rounded-xl font-label-md text-center bg-primary text-on-primary shadow-lg shadow-primary/20 hover:shadow-xl active:scale-95 transition-all">Start Pro Trial</a>
            </div>
            <!-- Elite -->
            <div class="scroll-reveal bg-surface-container-lowest p-8 rounded-3xl border border-outline-variant/30 flex flex-col" style="transition-delay:0.2s">
                <h3 class="font-headline-md text-xl text-on-surface mb-2">Elite Unlimited</h3>
                <p class="font-label-sm text-on-surface-variant mb-4">For power users and teams</p>
                <div class="mb-6"><span class="font-headline-xl text-4xl text-on-surface">$29.99</span><span class="text-on-surface-variant text-sm">/month</span></div>
                <ul class="flex flex-col gap-3 mb-8 flex-1">
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> 500 AI Resume Generations</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> All Templates + Early Access</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> AI Chat (Unlimited Turns)</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Dedicated AI Processing</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Multi-Resume Management</li>
                    <li class="flex items-center gap-2 text-sm text-on-surface-variant"><span class="material-symbols-outlined text-primary text-lg">check</span> Priority Support</li>
                </ul>
                <a href="?page=auth&mode=register" class="w-full py-3 rounded-xl font-label-md text-center border-2 border-primary text-primary hover:bg-primary hover:text-on-primary transition-all">Go Elite</a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials" class="py-24 bg-surface border-t border-outline-variant/20">
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary/10 text-secondary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">group</span> Trusted by Thousands
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">What our users say</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="scroll-reveal bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/30">
                <div class="flex items-center gap-1 mb-4">
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                </div>
                <p class="font-body-md text-on-surface-variant mb-6 italic">"I uploaded my old resume and the AI completely rewrote my experience section. Got 3 interview calls within a week. The AI chat feature is a game-changer."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-xs">SK</div>
                    <div><p class="font-label-md text-on-surface text-xs">Sarah K.</p><p class="text-[10px] text-on-surface-variant">Software Engineer @ Google</p></div>
                </div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/30" style="transition-delay:0.1s">
                <div class="flex items-center gap-1 mb-4">
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                </div>
                <p class="font-body-md text-on-surface-variant mb-6 italic">"The templates are gorgeous and the AI actually understands job descriptions. I selected 'Product Manager' and it reworded everything to match PM terminology."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-tertiary-container flex items-center justify-center text-on-tertiary-container font-bold text-xs">AR</div>
                    <div><p class="font-label-md text-on-surface text-xs">Alex R.</p><p class="text-[10px] text-on-surface-variant">Product Manager @ Meta</p></div>
                </div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest p-8 rounded-2xl border border-outline-variant/30" style="transition-delay:0.2s">
                <div class="flex items-center gap-1 mb-4">
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                    <span class="material-symbols-outlined text-yellow-500" style="font-size:18px;font-variation-settings:'FILL' 1;">star</span>
                </div>
                <p class="font-body-md text-on-surface-variant mb-6 italic">"Free tier is generous enough to build a solid resume. The PDF import saved me hours of re-typing. Clean, professional output every time."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-on-secondary-container font-bold text-xs">MP</div>
                    <div><p class="font-label-md text-on-surface text-xs">Maya P.</p><p class="text-[10px] text-on-surface-variant">Data Scientist @ Netflix</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section id="faq" class="py-24 bg-surface-container-lowest border-t border-outline-variant/20">
    <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-16 scroll-reveal">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-label-sm mb-4">
                <span class="material-symbols-outlined text-sm">help</span> FAQ
            </div>
            <h2 class="font-headline-lg text-4xl text-on-surface mb-4">Frequently asked questions</h2>
        </div>
        <div class="flex flex-col gap-4">
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">Is GreenLeaf Resume really free?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">Yes! The Starter Launch plan is completely free. You get 2 AI resume generations, 3 templates, PDF import, and full profile building — no credit card required. Upgrade to Pro or Elite when you need more.</p></div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden" style="transition-delay:0.05s">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">How does the AI resume generation work?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">Our AI (Llama 3.3 70B via Groq) reads your profile data and the target job description, then rewrites each section with stronger action verbs, quantified achievements, and job-specific keywords. It never fabricates information — it only uses what you provide.</p></div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden" style="transition-delay:0.1s">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">Can I import my existing resume?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">Absolutely. Upload your PDF resume and our AI extracts all sections — personal info, education, experience, skills, projects, and achievements — into structured profile data. You can then edit, regenerate, or switch templates instantly.</p></div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden" style="transition-delay:0.15s">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">What is the AI Chat Agent?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">The AI Chat Agent is a built-in assistant that lets you modify your resume using natural language. Just type commands like "Make my summary shorter" or "Add more technical keywords" and the AI updates your resume in real-time with live preview.</p></div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden" style="transition-delay:0.2s">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">Are the resumes ATS-friendly?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">Yes. All templates use clean heading hierarchy, proper text flow, and no complex tables or columns that confuse ATS software. Our 95% ATS pass rate means your resume gets seen by human recruiters, not filtered out by bots.</p></div>
            </div>
            <div class="scroll-reveal bg-surface-container-lowest border border-outline-variant/30 rounded-2xl overflow-hidden" style="transition-delay:0.25s">
                <button onclick="this.nextElementSibling.classList.toggle('open'); this.querySelector('.arrow').classList.toggle('rotate-180')" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-surface-container transition-colors">
                    <span class="font-label-md text-on-surface text-sm">Do credits expire?</span>
                    <span class="material-symbols-outlined text-on-surface-variant arrow transition-transform" style="font-size:20px;">expand_more</span>
                </button>
                <div class="faq-answer px-6 pb-4"><p class="text-sm text-on-surface-variant">AI credits are valid for the duration of your plan. If a resume generation fails (e.g., empty profile), credits are automatically refunded. You can always check your remaining credits on the dashboard.</p></div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA -->
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0 bg-primary z-0"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPjxyZWN0IHdpZHRoPSI4IiBoZWlnaHQ9IjgiIGZpbGw9IiMwMDZjNDkiPjwvcmVjdD48cGF0aCBkPSJNMCAwTDggOFpNOCAwTDAgOFoiIHN0cm9rZT0iIzAzOGI2MSIgc3Ryb2tlLXdpZHRoPSIxIj48L3BhdGg+PC9zdmc+')] opacity-20 z-0 mix-blend-overlay"></div>
    <div class="max-w-4xl mx-auto px-margin-mobile md:px-margin-desktop text-center relative z-10 text-on-primary flex flex-col items-center">
        <span class="material-symbols-outlined text-6xl mb-6 text-primary-fixed" style="font-variation-settings: 'FILL' 1;">park</span>
        <h2 class="font-headline-xl text-4xl md:text-5xl mb-6">Ready to grow your career?</h2>
        <p class="font-body-lg text-xl mb-10 opacity-90 max-w-2xl">Join 10,000+ professionals who landed interviews at top companies using GreenLeaf Resume. Start building yours for free — no credit card needed.</p>
        <div class="flex flex-col sm:flex-row items-center gap-4">
            <a href="?page=auth&mode=register" class="bg-surface text-primary px-12 py-5 rounded-full font-headline-md text-xl shadow-2xl hover:scale-105 active:scale-95 transition-transform flex items-center gap-2">
                Get Started Free <span class="material-symbols-outlined">arrow_forward</span>
            </a>
            <a href="#features" class="px-8 py-5 rounded-full font-headline-md text-xl text-on-primary border-2 border-white/30 hover:bg-white/10 transition-colors">
                Explore Features
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include __DIR__ . '/../components/common/footer.php'; ?>

<!-- Decorative leaf -->
<div class="fixed bottom-0 right-0 -z-10 opacity-5 pointer-events-none overflow-hidden h-96 w-96">
    <svg class="w-full h-full scale-150 translate-x-1/4 translate-y-1/4 text-primary" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path d="M47.7,-64.1C60.9,-54.6,70.1,-39.8,75.4,-23.7C80.7,-7.5,82,10.1,75.6,24.8C69.3,39.5,55.2,51.3,40.1,60.7C24.9,70.1,8.6,77.1,-8.5,75.9C-25.6,74.7,-43.5,65.3,-56.3,51.2C-69.1,37.1,-76.8,18.5,-76.1,0.4C-75.3,-17.7,-66.1,-35.4,-53,-44.8C-39.9,-54.3,-22.9,-55.5,-5.5,-59.4C11.9,-63.3,23.8,-69.9,47.7,-64.1Z" fill="currentColor" transform="translate(100 100)"></path>
    </svg>
</div>

<script>
// Scroll reveal animation
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>
