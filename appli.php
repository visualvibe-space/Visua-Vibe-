<!DOCTYPE html>
<html lang="en">
<head>
   <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-N3BTCEH53R"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-N3BTCEH53R');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon using your logo -->
    <link rel="icon" href="images/vlogo.png" type="image/png">
    <title>Visual Vibes | Your Idea, Our Vision</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3a0ca3;
            --accent: #f72585;
            --dark: #1a1d28;
            --dark-secondary: #252a38;
            --light: #f8f9fa;
            --light-secondary: #e9ecef;
            --text: rgba(255,255,255,0.95);
            --text-secondary: rgba(255,255,255,0.7);
            --card-bg: rgba(255,255,255,0.08);
            --card-hover: rgba(67, 97, 238, 0.15);
            --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            --shadow: 0 10px 30px rgba(0,0,0,0.25);
            --shadow-hover: 0 20px 40px rgba(0,0,0,0.35);
            --border: 1px solid rgba(255,255,255,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--text);
            line-height: 1.7;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(26, 29, 40, 0.95);
            backdrop-filter: blur(15px);
            z-index: 1000;
            padding: 0.9rem 0;
            border-bottom: var(--border);
            transition: var(--transition);
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            height: 80px;
        }
        
        .logo {
            height: 100%;
            display: flex;
            align-items: center;
        }
        
        .logo img {
            height: 50px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
            filter: brightness(0) invert(1);
            background: transparent !important;
        }
        
        .logo img:hover {
            transform: scale(1.05);
        }
        
        .nav-links {
            display: flex;
            gap: 2.5rem;
        }
        
        .nav-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-links a:hover, 
        .nav-links a.active {
            color: var(--text);
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            transition: width 0.4s;
            border-radius: 2px;
        }
        
        .nav-links a:hover::after, 
        .nav-links a.active::after {
            width: 100%;
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 1001;
            transition: var(--transition);
            position: relative;
            width: 30px;
            height: 30px;
        }
        
        .mobile-menu-btn .hamburger {
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: var(--text);
            transition: var(--transition);
            top: 50%;
            left: 0;
            transform: translateY(-50%);
        }
        
        .mobile-menu-btn .hamburger::before,
        .mobile-menu-btn .hamburger::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background-color: var(--text);
            transition: var(--transition);
        }
        
        .mobile-menu-btn .hamburger::before {
            top: -8px;
        }
        
        .mobile-menu-btn .hamburger::after {
            top: 8px;
        }
        
        .mobile-menu-btn.active .hamburger {
            background-color: transparent;
        }
        
        .mobile-menu-btn.active .hamburger::before {
            transform: rotate(45deg);
            top: 0;
        }
        
        .mobile-menu-btn.active .hamburger::after {
            transform: rotate(-45deg);
            top: 0;
        }
        
        /* Header/Hero */
        header {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
            background: linear-gradient(135deg, var(--dark-secondary) 0%, var(--dark) 100%);
        }
        
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.03"><polygon fill="white" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
            z-index: 0;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease forwards;
            background: linear-gradient(to right, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            max-width: 700px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease 0.3s forwards;
        }
        
        .cta-button {
            display: inline-block;
            padding: 1.2rem 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease 0.6s forwards;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }
        
        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.7s;
        }
        
        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        
        .cta-button:hover::before {
            left: 100%;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        section {
            padding: 8rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            font-size: 3rem;
            font-weight: 800;
            position: relative;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .section-title.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 120px;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            margin: 1.5rem auto;
            border-radius: 3px;
        }
        
        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto 4rem;
            font-size: 1.2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease 0.2s, transform 0.8s ease 0.2s;
        }
        
        .section-subtitle.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Portfolio Section */
        .portfolio-section {
            background: linear-gradient(to bottom, var(--dark), var(--dark-secondary));
            position: relative;
            overflow: hidden;
        }
        
        .portfolio-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(67, 97, 238, 0.1), transparent 70%);
            z-index: 0;
        }
        
        .portfolio-item {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            transition: var(--transition);
            border: var(--border);
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease, var(--transition);
        }
        
        .portfolio-item.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .portfolio-item:hover {
            transform: translateY(-10px);
            background: var(--card-hover);
            border-color: rgba(67, 97, 238, 0.3);
            box-shadow: var(--shadow-hover);
        }
        
        /* Website Image Styles */
        .website-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
            background: var(--dark-secondary);
            border-radius: 10px 10px 0 0;
        }
        
        .portfolio-content {
            padding: 2rem;
        }
        
        .portfolio-content h3 {
            color: var(--text);
            margin-bottom: 1rem;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .portfolio-content p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        /* Explore Button */
        .explore-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            font-size: 0.9rem;
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }
        
        .explore-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.5);
            color: white;
        }
        
        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 1rem 2rem;
            background: var(--card-bg);
            color: var(--text);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            border: var(--border);
            margin-bottom: 2rem;
        }
        
        .back-button:hover {
            background: var(--card-hover);
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .hero-title {
                font-size: 4rem;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: rgba(26, 29, 40, 0.98);
                flex-direction: column;
                gap: 0;
                padding: 1.5rem 0;
                border-bottom: 2px solid var(--primary);
                backdrop-filter: blur(15px);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .nav-links a {
                padding: 1.2rem 2rem;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                color: var(--text);
                transition: all 0.3s ease;
                font-size: 1.1rem;
            }
            
            .nav-links a:last-child {
                border-bottom: none;
            }
            
            .nav-links a:hover {
                background: rgba(67, 97, 238, 0.2);
                color: white;
                padding-left: 2.5rem;
            }
            
            .logo img {
                height: 35px;
            }
            
            .nav-links {
                top: 70px;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .website-image {
                height: 200px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .cta-button {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
            
            section {
                padding: 5rem 0;
            }
            
            .nav-container {
                height: 70px;
                padding: 0 1.5rem;
            }
            
            .logo img {
                height: 30px;
            }
            
            .nav-links {
                top: 70px;
            }
            
            .portfolio-item {
                padding: 1.5rem;
            }
            
            .website-image {
                height: 180px;
            }
        }

        /* Technology Section Styles */
/* Application Development Section Styles */
.application-section {
    background: linear-gradient(to bottom, var(--dark), var(--dark-secondary));
    position: relative;
    overflow: hidden;
}

.application-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 20% 50%, rgba(67, 97, 238, 0.15), transparent 70%);
    z-index: 0;
}

.app-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 2.5rem;
    text-align: center;
    transition: var(--transition);
    border: var(--border);
    backdrop-filter: blur(10px);
    height: 100%;
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s ease, var(--transition);
}

.app-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.app-card:hover {
    transform: translateY(-10px);
    background: var(--card-hover);
    border-color: rgba(247, 37, 133, 0.3);
    box-shadow: var(--shadow-hover);
}

.app-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, var(--accent), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
}

.app-card h3 {
    color: var(--text);
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.app-card p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    font-size: 1rem;
    line-height: 1.6;
}

.app-list {
    list-style: none;
    padding: 0;
    text-align: left;
}

.app-list li {
    color: var(--text-secondary);
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    position: relative;
    padding-left: 1.5rem;
}

.app-list li:last-child {
    border-bottom: none;
}

.app-list li::before {
    content: '▸';
    position: absolute;
    left: 0;
    color: var(--accent);
    font-weight: bold;
}

/* Responsive adjustments for application section */
@media (max-width: 768px) {
    .app-card {
        padding: 2rem 1.5rem;
    }
    
    .app-icon {
        font-size: 2.5rem;
    }
}  </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <img src="images/visual vibe logo.svg" class="img-fluid" style="width:300px; height:150px  !important; width:220px !important;">

            <button class="mobile-menu-btn">
                <span class="hamburger"></span>
            </button>
            <div class="nav-links">
                <a href="index.html">Home</a>
                <!-- <a href="#portfolio">Portfolio</a> -->
                <a href="index.html #contact">Contact</a>
                <a href="index.html #enquiry">Enquiry</a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <header id="home">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <a href="index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <h1 class="hero-title">Mobile Applications Development</h1>
            <p class="hero-subtitle">Crafting stunning, Mobile Applications that deliver exceptional user experiences and drive business growth.</p>
            <!-- <a href="#portfolio" class="cta-button">View Our Work</a> -->
        </div>
    </header>
    
        <!-- Technology Stack Section -->
       <!-- Application Development Section -->
<section id="application-development" class="application-section">
    <div class="container">
        <h2 class="section-title">Application Development</h2>
        <p class="section-subtitle">Building powerful, scalable, and user-friendly applications for web and mobile platforms.</p>
        
        <div class="row justify-content-center">
            <!-- Mobile App Development -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="app-card">
                    <div class="app-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Applications</h3>
                    <p>Native and cross-platform mobile apps for iOS and Android:</p>
                    <ul class="app-list">
                        <li>React Native</li>
                        <li>Flutter</li>
                        <li>iOS Swift</li>
                        <li>Android Kotlin</li>
                        <li>Cross-platform Solutions</li>
                        <li>App Store Deployment</li>
                    </ul>
                </div>
            </div>
            
            <!-- Web Applications -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="app-card">
                    <div class="app-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Web Applications</h3>
                    <p>Dynamic and interactive web applications with modern frameworks:</p>
                    <ul class="app-list">
                        <li>React.js</li>
                        <li>Vue.js</li>
                        <li>Angular</li>
                        <li>Node.js</li>
                        <li>Progressive Web Apps</li>
                        <li>Real-time Applications</li>
                    </ul>
                </div>
            </div>
            
            <!-- Desktop Applications -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="app-card">
                    <div class="app-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <h3>Desktop Applications</h3>
                    <p>Cross-platform desktop applications for various business needs:</p>
                    <ul class="app-list">
                        <li>Electron.js</li>
                        <li>Java FX</li>
                        <li>.NET Framework</li>
                        <li>Python Tkinter</li>
                        <li>Cross-platform Desktop Apps</li>
                        <li>System Integration</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Additional Row for Backend & Database -->
        <div class="row justify-content-center mt-4">
            <!-- Backend Development -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="app-card">
                    <div class="app-icon">
                        <i class="fas fa-server"></i>
                    </div>
                    <h3>Backend & API Development</h3>
                    <p>Robust server-side solutions and API integrations:</p>
                    <ul class="app-list">
                        <li>RESTful APIs</li>
                        <li>GraphQL</li>
                        <li>Microservices Architecture</li>
                        <li>Authentication & Authorization</li>
                        <li>WebSocket & Real-time</li>
                        <li>Third-party API Integration</li>
                    </ul>
                </div>
            </div>
            
            <!-- Database & Cloud -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="app-card">
                    <div class="app-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Database & Cloud Solutions</h3>
                    <p>Scalable data management and cloud infrastructure:</p>
                    <ul class="app-list">
                        <li>MongoDB</li>
                        <li>MySQL & PostgreSQL</li>
                        <li>Firebase</li>
                        <li>AWS & Azure</li>
                        <li>Cloud Deployment</li>
                        <li>Database Optimization</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
    
        

    

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle functionality with cross transition
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const navLinks = document.querySelector('.nav-links');
        
        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                    
                    // Update active class
                    document.querySelectorAll('.nav-links a').forEach(link => {
                        link.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Close mobile menu if open
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        mobileMenuBtn.classList.remove('active');
                    }
                }
            });
        });
        
        // Update active link based on scroll position
        window.addEventListener('scroll', () => {
            const scrollPosition = window.scrollY;
            
            document.querySelectorAll('section').forEach(section => {
                const sectionTop = section.offsetTop - 100;
                const sectionHeight = section.offsetHeight;
                const sectionId = section.getAttribute('id');
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                    document.querySelectorAll('.nav-links a').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        });

        // Scroll animation for elements
        function checkVisibility() {
            const elements = document.querySelectorAll('.section-title, .section-subtitle, .portfolio-item');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        }
        
        // Initial check
        checkVisibility();
        
        // Check on scroll
        window.addEventListener('scroll', checkVisibility);

       // Update the checkVisibility function to include app cards
function checkVisibility() {
    const elements = document.querySelectorAll('.section-title, .section-subtitle, .portfolio-item, .tech-card, .app-card');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('visible');
        }
    });
}
    </script>
    
</body>
</html>