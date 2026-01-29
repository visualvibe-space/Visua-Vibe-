<?php
require_once "config.php";



/* Fetch portfolio videos */
$stmt = $pdo->prepare("
    SELECT *
    FROM portfolio_videos
    WHERE is_active = 1
    ORDER BY display_order ASC, created_at DESC
");

$stmt->execute();
$portfolioVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


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
        
        /* Fix for portrait videos */
        .portfolio-video {
            width: 100%;
            height: 400px;
            object-fit: contain;
            display: block;
            background: var(--dark-secondary);
            border-radius: 10px;
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
            
            .portfolio-video {
                height: 350px;
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
            
            .portfolio-video {
                height: 300px;
            }
        }
        /* Video Editing & Motion Graphics Section Styles */
.video-section {
    background: linear-gradient(to bottom, var(--dark-secondary), var(--dark));
    position: relative;
    overflow: hidden;
}

.video-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 60% 40%, rgba(67, 97, 238, 0.15), transparent 70%);
    z-index: 0;
}

.video-card {
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

.video-card.visible {
    opacity: 1;
    transform: translateY(0);
}

.video-card:hover {
    transform: translateY(-10px);
    background: var(--card-hover);
    border-color: rgba(67, 97, 238, 0.3);
    box-shadow: var(--shadow-hover);
}

.video-icon {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: inline-block;
}

.video-card h3 {
    color: var(--text);
    margin-bottom: 1rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.video-card p {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    font-size: 1rem;
    line-height: 1.6;
}

.video-list {
    list-style: none;
    padding: 0;
    text-align: left;
}

.video-list li {
    color: var(--text-secondary);
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    position: relative;
    padding-left: 1.5rem;
}

.video-list li:last-child {
    border-bottom: none;
}

.video-list li::before {
    content: '▸';
    position: absolute;
    left: 0;
    color: var(--primary);
    font-weight: bold;
}

/* Responsive adjustments for video section */
@media (max-width: 768px) {
    .video-card {
        padding: 2rem 1.5rem;
    }
    
    .video-icon {
        font-size: 2.5rem;
    }
}
/* Video Portfolio Section Styles */
.portfolio-section {
    background: linear-gradient(to bottom, var(--dark-secondary), var(--dark));
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
    background: radial-gradient(circle at 60% 40%, rgba(67, 97, 238, 0.15), transparent 70%);
    z-index: 0;
}

.video-container {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 1rem;
    background: var(--dark-secondary);
    min-height: 300px;
}

.video-thumbnail {
    position: relative;
    cursor: pointer;
    transition: var(--transition);
    border-radius: 15px;
    overflow: hidden;
    height: 300px;
}

.video-thumbnail:hover {
    transform: scale(1.02);
}

.thumbnail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 15px;
    transition: var(--transition);
}

.video-thumbnail:hover .thumbnail-image {
    filter: brightness(0.8);
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    transition: var(--transition);
    opacity: 0.9;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.video-thumbnail:hover .play-button {
    transform: translate(-50%, -50%) scale(1.1);
    opacity: 1;
}

.play-button i {
    margin-left: 4px;
}

.video-iframe {
    width: 100%;
    height: 300px;
    border: none;
    border-radius: 15px;
    display: none;
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
    padding: 2rem;
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

.portfolio-content {
    padding: 1.5rem 0 0;
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
    margin-bottom: 0;
}

/* Responsive adjustments for video portfolio */
@media (max-width: 768px) {
    .video-thumbnail,
    .video-iframe {
        height: 250px;
    }
    
    .video-container {
        min-height: 250px;
    }
    
    .play-button {
        width: 60px;
        height: 60px;
        font-size: 1.2rem;
    }
    
    .portfolio-item {
        padding: 1.5rem;
    }
}

@media (max-width: 576px) {
    .video-thumbnail,
    .video-iframe {
        height: 200px;
    }
    
    .video-container {
        min-height: 200px;
    }
    
    .play-button {
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
}
    </style>
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
                <a href="#portfolio">Portfolio</a>
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
            <h1 class="hero-title">Video Editing & Motion Graphics</h1>
            <p class="hero-subtitle">Bringing stories to life with cinematic editing and dynamic animations that captivate audiences and elevate your brand.</p>
            <a href="#portfolio" class="cta-button">View Our Work</a>
        </div>
    </header>

    <!-- Video Editing & Motion Graphics Section -->
<section id="video-editing" class="video-section">
    <div class="container">
        <h2 class="section-title">Video Editing & Motion Graphics</h2>
        <p class="section-subtitle">Professional video editing and dynamic motion graphics to bring your content to life with stunning visual appeal.</p>
        
        <div class="row justify-content-center">
            <!-- Video Editing Services -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="video-card">
                    <div class="video-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3>Video Editing Services</h3>
                    <p>Comprehensive video editing and post-production:</p>
                    <ul class="video-list">
                        <li>Corporate Videos</li>
                        <li>Social Media Content</li>
                        <li>YouTube Videos</li>
                        <li>Commercial Ads</li>
                        <li>Event Coverage</li>
                        <li>Documentaries</li>
                        <li>Color Correction</li>
                        <li>Audio Enhancement</li>
                    </ul>
                </div>
            </div>
            
            <!-- Motion Graphics Services -->
            <div class="col-lg-6 col-md-6 mb-4">
                <div class="video-card">
                    <div class="video-icon">
                        <i class="fas fa-star-of-life"></i>
                    </div>
                    <h3>Motion Graphics Services</h3>
                    <p>Creating dynamic animated visuals and graphics:</p>
                    <ul class="video-list">
                        <li>Explainer Videos</li>
                        <li>Animated Infographics</li>
                        <li>Title Sequences</li>
                        <li>UI/UX Animations</li>
                        <li>Character Animation</li>
                        <li>Visual Effects</li>
                        <li>3D Animation</li>
                        <li>Particle Effects</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Software & Tools -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-12">
                <div class="video-card">
                    <div class="video-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Software & Tools</h3>
                    <p>We use industry-standard software for professional results:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="video-list">
                                <li>Adobe Premiere Pro</li>
                                <li>Adobe After Effects</li>
                                <li>Final Cut Pro</li>
                                <li>DaVinci Resolve</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="video-list">
                                <li>Adobe Photoshop</li>
                                <li>Adobe Illustrator</li>
                                <li>Cinema 4D</li>
                                <li>Blender</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
    
    <!-- Portfolio Section -->
  <!-- Portfolio Section -->
<section id="portfolio" class="portfolio-section">
    <div class="container-fluid">

        <h2 class="section-title">Our Video Portfolio</h2>
        <p class="section-subtitle">
            Explore our collection of video editing and motion graphics projects.
        </p>

        <?php if (!empty($portfolioVideos)): ?>

            <?php
            $count = 0;
            foreach ($portfolioVideos as $video):

                // Start new row after every 3 videos
                if ($count % 3 === 0):
            ?>
                <div class="portfolio-item">
                    <div class="row">
            <?php endif; ?>

                <div class="col-md-4 mb-4">

                    <div class="video-container">

                        <!-- VIDEO PLAYER -->
                        <video
                            class="video-player"
                            width="100%"
                            height="300"
                            controls
                            preload="metadata"
                            poster="<?= htmlspecialchars($video['thumbnail_file']) ?>"
                        >
                            <source src="<?= htmlspecialchars($video['video_file']) ?>" type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>

                    </div>

                    <div class="portfolio-content">
                        <h3><?= htmlspecialchars($video['title']) ?></h3>
                        <p><?= htmlspecialchars($video['description']) ?></p>
                    </div>

                </div>

            <?php
                $count++;

                // Close row after 3 columns
                if ($count % 3 === 0):
            ?>
                    </div>
                </div>
            <?php endif; endforeach; ?>

            <!-- Close unclosed row -->
            <?php if ($count % 3 !== 0): ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>

            <p style="text-align:center;color:#999;">
                No portfolio videos available.
            </p>

        <?php endif; ?>

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

        // Update the checkVisibility function to include video cards
function checkVisibility() {
    const elements = document.querySelectorAll('.section-title, .section-subtitle, .portfolio-item, .tech-card, .app-card, .merch-card, .uiux-card, .process-step, .video-card');
    
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.classList.add('visible');
        }
    });
}

// Video thumbnail click handler for Google Drive iframes
document.addEventListener('DOMContentLoaded', function() {
    const videoThumbnails = document.querySelectorAll('.video-thumbnail');
    
    videoThumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const videoSrc = this.getAttribute('data-video-src');
            const container = this.parentElement;
            const iframe = container.querySelector('.video-iframe');
            
            // Set the iframe source
            iframe.src = videoSrc;
            
            // Hide thumbnail and show iframe
            this.style.display = 'none';
            iframe.style.display = 'block';
        });
    });
});
    </script>
</body>
</html>