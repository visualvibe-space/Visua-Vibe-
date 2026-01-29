<?php
require_once 'config.php'; // Database class file
// session_start();
// Create DB object


// Fetch active carousel slides
$sql = "
    SELECT title, subtitle, image_url, description
    FROM carousel_slides
    WHERE is_active = 1
    ORDER BY display_order ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Fetch active team members */
$stmt = $pdo->prepare("
    SELECT *
    FROM team_members
    WHERE is_active = 1
    ORDER BY 
        FIELD(category,
            'Founders & CEO',
            'Management',
            'Developers',
            'Designers',
            'Graphics Team',
            'Marketing',
            'Others'
        ),
        display_order ASC,
        created_at ASC
");

$stmt->execute();
$teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Group members by category */
$teams = [];

foreach ($teamMembers as $member) {
    $teams[$member['category']][] = $member;
}
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
<!-- Meta Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1773463996623071');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1773463996623071&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon using your logo -->
    <link rel="icon" href="images/vlogo.png" type="image/png">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Visual Vibes | Your Idea, Our Vision</title>
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
        
        /* Carousel Section */
        .carousel-section {
            padding: 6rem 0;
            background: var(--dark-secondary);
            position: relative;
            overflow: hidden;
        }
        
        .carousel-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: var(--shadow);
        }
        
        .carousel-slides {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            height: 600px;
        }
        
        .carousel-slide {
            min-width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .carousel-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 3rem;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            color: white;
            z-index: 2;
        }
        
        .carousel-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.3s;
        }
        
        .carousel-description {
            font-size: 1.2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease 0.5s;
            max-width: 600px;
        }
        
        .carousel-slide.active .carousel-title,
        .carousel-slide.active .carousel-description {
            opacity: 1;
            transform: translateY(0);
        }
        
        .carousel-controls {
            position: absolute;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            gap: 1rem;
            z-index: 3;
        }
        
        .carousel-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .carousel-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
        }
        
        .carousel-indicators {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.8rem;
            z-index: 3;
        }
        
        .carousel-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .carousel-indicator.active {
            background: white;
            transform: scale(1.2);
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
        
        /* About Section */
        .about-section {
            background: linear-gradient(to bottom, var(--dark), var(--dark-secondary));
            position: relative;
            overflow: hidden;
        }
        
        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(67, 97, 238, 0.1), transparent 70%);
            z-index: 0;
        }
        
        .about {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .about-image {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            opacity: 0;
            transform: translateX(-30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .about-image.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .about-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.2), rgba(247, 37, 133, 0.1));
            z-index: 1;
        }
        
        .about-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
            transition: transform 0.8s ease;
        }
        
        .about-image:hover img {
            transform: scale(1.05);
        }
        
        .about-content {
            opacity: 0;
            transform: translateX(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .about-content.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .about-content h3 {
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            color: var(--text);
        }
        
        .about-content h3 span {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .about-content p {
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
            font-size: 1.1rem;
        }
        
        /* Services Section */
        .services-section {
            background: var(--dark-secondary);
            position: relative;
            overflow: hidden;
        }
        
        .services-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(67, 97, 238, 0.1), transparent 70%);
            z-index: 0;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
            position: relative;
            z-index: 1;
        }
        
        .service-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 3rem 2rem;
            transition: var(--transition);
            text-align: center;
            border: var(--border);
            backdrop-filter: blur(10px);
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease, var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s ease;
        }
        
        .service-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .service-card:hover {
            transform: translateY(-15px);
            background: var(--card-hover);
            border-color: rgba(67, 97, 238, 0.3);
            box-shadow: var(--shadow-hover);
        }
        
        .service-card:hover::before {
            transform: scaleX(1);
        }
        
        .service-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .service-card h3 {
            margin-bottom: 1.2rem;
            color: var(--text);
            font-size: 1.6rem;
            font-weight: 700;
        }
        
        .service-card p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.6;
        }
        
        /* Team Section */
        .team-section {
            padding: 120px 0;
            background: linear-gradient(to bottom, var(--dark-secondary), var(--dark));
            position: relative;
            overflow: hidden;
        }
        
        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 70%, rgba(67, 97, 238, 0.1), transparent 70%);
            z-index: 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 25px;
            color: var(--text);
            font-size: 3rem;
            position: relative;
            font-weight: 800;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -35px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 2px;
        }
        
        .section-caption {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 80px;
            font-size: 1.2rem;
            color: var(--text-secondary);
            font-weight: 500;
            position: relative;
        }
        
        .team-category {
            margin-bottom: 100px;
            position: relative;
        }
        
        .team-category-title {
            text-align: center;
            margin-bottom: 50px;
            color: var(--text);
            position: relative;
            padding-bottom: 20px;
            font-size: 2.2rem;
            font-weight: 700;
        }
        
        .team-category-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 2px;
        }
        
        .team-member {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 20px;
            border-radius: 20px;
            background: var(--card-bg);
            box-shadow: var(--shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: var(--border);
            backdrop-filter: blur(5px);
        }
        
        .team-member::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            opacity: 0.1;
            transition: left 0.6s ease;
        }
        
        .team-member:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            background: var(--card-hover);
            border-color: rgba(67, 97, 238, 0.3);
        }
        
        .team-member:hover::before {
            left: 0;
        }
        
        /* CEO Image - Largest */
        .team-category:first-child .team-img {
            width: 280px;
            height: 280px;
            border: 5px solid var(--accent);
            background: linear-gradient(135deg, var(--primary), var(--accent)) border-box;
        }
        
        /* Regular Team Images - Increased size */
        .team-img {
            width: 220px;
            height: 220px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 25px;
            border: 4px solid transparent;
            background: linear-gradient(135deg, var(--primary), var(--accent)) border-box;
            box-shadow: var(--shadow);
            transition: all 0.4s ease;
            position: relative;
            z-index: 2;
            display: block;
        }
        
        .team-member:hover .team-img {
            transform: scale(1.1);
            border-color: var(--accent);
        }
        
        .team-name {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.3rem;
            color: var(--text);
            position: relative;
            z-index: 2;
        }
        
        .team-designation {
            font-weight: 500;
            color: var(--primary);
            margin-bottom: 5px;
            position: relative;
            z-index: 2;
            font-size: 1rem;
        }
        
        /* Fallback for missing team images */
        .team-img:before {
            content: "No Image";
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: var(--dark-secondary);
            color: var(--text-secondary);
            font-size: 0.8rem;
            border-radius: 50%;
        }
        
        /* Responsive adjustments for team images */
        @media (max-width: 768px) {
            .team-category:first-child .team-img {
                width: 240px;
                height: 240px;
            }
            
            .team-img {
                width: 200px;
                height: 200px;
            }
        }
        
        @media (max-width: 576px) {
            .team-category:first-child .team-img {
                width: 220px;
                height: 220px;
            }
            
            .team-img {
                width: 180px;
                height: 180px;
            }
            
            .section-title {
                font-size: 2.5rem;
            }
            
            .team-category-title {
                font-size: 1.8rem;
            }
        }
        
        /* Grid layout compatibility */
        .team-container {
            position: relative;
            z-index: 1;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -1rem;
        }
        
        .col-md-6, .col-lg-3, .col-lg-4, .col-lg-6 {
            padding: 0 1rem;
            margin-bottom: 2rem;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .col-lg-3 {
            flex: 0 0 25%;
            max-width: 25%;
        }
        
        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        
        .col-lg-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .justify-content-center {
            justify-content: center;
        }
        
        /* Animation classes for scroll effects */
        .team-category {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        
        .team-category.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .team-member {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease, transform 0.8s ease, all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .team-member.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .section-caption {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease 0.2s, transform 0.8s ease 0.2s;
        }
        
        .section-caption.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Contact Section */
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            margin-top: 3rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start; /* Align to top for better text flow */
            gap: 1.5rem;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 15px;
            transition: var(--transition);
            border: var(--border);
            backdrop-filter: blur(5px);
            opacity: 0;
            transform: translateY(30px);
            position: relative;
            overflow: hidden;
            min-height: 120px;
        }
        
        .contact-item.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .contact-details {
            flex: 1;
            min-width: 0; /* This is crucial for text overflow */
        }
        
        .contact-details h4 {
            color: var(--text);
            margin-bottom: 0.8rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .contact-details p, 
        .contact-details a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 1rem;
            word-break: break-word;
            overflow-wrap: break-word;
            display: block;
            width: 100%;
        }
        
        .email {
            font-size: 0.95rem;
            line-height: 1.4;
            word-break: break-all; /* Force break if needed */
            overflow-wrap: anywhere;
        }
        
        .contact-details a:hover {
            color: var(--primary);
        }
        
        /* For very small screens */
        @media (max-width: 480px) {
            .contact-info {
                grid-template-columns: 1fr;
            }
            
            .contact-item {
                min-height: auto;
                padding: 1.5rem;
            }
            
            .email {
                font-size: 0.9rem;
            }
        }
        
        .contact-item:hover {
            transform: translateY(-8px);
            background: var(--card-hover);
            border-color: rgba(67, 97, 238, 0.3);
            box-shadow: var(--shadow-hover);
        }
        
        .contact-item:hover::before {
            transform: scaleY(1);
        }
        
        .contact-icon {
            font-size: 2rem;
            color: var(--primary);
            min-width: 60px;
            height: 60px;
            background: rgba(67, 97, 238, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .contact-item:hover .contact-icon {
            background: rgba(67, 97, 238, 0.3);
            transform: scale(1.1);
        }
        
        .contact-details h4 {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .contact-details p, 
        .contact-details a {
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.3s;
            font-size: 1.1rem;
        }
        
        .contact-details a:hover {
            color: var(--primary);
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 4rem 0;
            background-color: var(--dark);
            color: var(--text-secondary);
            border-top: var(--border);
            position: relative;
            overflow: hidden;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 0%, rgba(67, 97, 238, 0.05), transparent 70%);
            z-index: 0;
        }
        
        .footer-logo {
            margin-bottom: 1.5rem;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text);
            position: relative;
            z-index: 1;
        }
        
        .footer-logo span {
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer-tagline {
            margin-bottom: 2.5rem;
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
            position: relative;
            z-index: 1;
        }
        
        .social-link {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: var(--transition);
            font-size: 1.2rem;
            border: var(--border);
        }
        
        .social-link:hover {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .copyright {
            font-size: 1rem;
            position: relative;
            z-index: 1;
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
            
            .carousel-slides {
                height: 500px;
            }
        }
        
        @media (max-width: 992px) {
            .hero-title {
                font-size: 3.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.3rem;
            }
            
            .about {
                grid-template-columns: 1fr;
                gap: 3rem;
            }
            
            .about-image {
                order: -1;
            }
            
            .col-lg-3, .col-lg-4, .col-lg-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }
            
            .services-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
            
            .carousel-slides {
                height: 450px;
            }
            
            .carousel-content {
                padding: 2rem;
            }
            
            .carousel-title {
                font-size: 2rem;
            }
            
            .carousel-description {
                font-size: 1.1rem;
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
                height: 35px; /* Smaller logo for mobile */
            }
            
            .nav-links {
                top: 70px;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .col-md-6, .col-lg-3, .col-lg-4, .col-lg-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .contact-info {
                grid-template-columns: 1fr;
            }
            
            .email {
                font-size: 15px;
            }
            
            .team-img {
                width: 140px;
                height: 140px;
            }
            
            .carousel-slides {
                height: 400px;
            }
            
            .carousel-content {
                padding: 1.5rem;
            }
            
            .carousel-title {
                font-size: 1.8rem;
            }
            
            .carousel-description {
                font-size: 1rem;
            }
            
            .carousel-controls {
                bottom: 1rem;
                right: 1rem;
            }
            
            .carousel-indicators {
                bottom: 1rem;
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
                height: 30px; /* Even smaller logo for very small screens */
            }
            
            .nav-links {
                top: 70px;
            }
            
            .service-card, .team-member, .contact-item {
                padding: 2rem 1.5rem;
            }
            
            .email {
                font-size: 14px;
            }
            
            .footer-logo {
                font-size: 2rem;
            }
            
            .team-img {
                width: 120px;
                height: 120px;
            }
            
            .carousel-slides {
                height: 350px;
            }
            
            .carousel-content {
                padding: 1rem;
            }
            
            .carousel-title {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .carousel-description {
                font-size: 0.9rem;
            }
        }


        /* //preloader */
        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        
        #preloader.fade-out {
            opacity: 0;
            visibility: hidden;
        }
        
        .preloader-content {
            text-align: center;
            max-width: 300px;
            width: 100%;
        }
        
        .preloader-logo {
            margin-bottom: 2rem;
        }
        
        .preloader-logo span {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .preloader-spinner {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 2rem;
        }
        
        .spinner-circle {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            animation: bounce 1.4s infinite ease-in-out both;
        }
        
        .spinner-circle:nth-child(1) {
            animation-delay: -0.32s;
        }
        
        .spinner-circle:nth-child(2) {
            animation-delay: -0.16s;
        }
        
        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
                opacity: 0.5;
            }
            40% {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .preloader-text {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background: linear-gradient(to right, var(--primary), var(--accent));
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        /* Alternative Spinner Style */
        .alternative-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 2rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Pulse Animation */
        .pulse-loader {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 50%;
            margin: 0 auto 2rem;
            animation: pulse 1.5s ease-in-out infinite both;
        }
        
        @keyframes pulse {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.1);
                opacity: 1;
            }
            100% {
                transform: scale(0.8);
                opacity: 0.8;
            }
        }

        /* Enquiry Section */
/* Enquiry Section */
.enquiry-section {
    background: var(--dark-secondary);
    padding: 8rem 0;
    position: relative;
}

.enquiry-content {
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.enquiry-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 3rem 2rem;
    border: var(--border);
    box-shadow: var(--shadow);
    transition: var(--transition);
    backdrop-filter: blur(10px);
}

.enquiry-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-hover);
    background: var(--card-hover);
}

.enquiry-icon {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 1.5rem;
}

.enquiry-card h3 {
    color: var(--text);
    font-size: 1.8rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.enquiry-card p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

.enquiry-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 1rem 2rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white !important;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    font-size: 1.1rem;
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.enquiry-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(67, 97, 238, 0.5);
    color: white !important;
}
/* For Option 1 */
.card-subtitle {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

/* For Option 2 */
.benefits-list {
    margin: 1rem 0;
    text-align: left;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

.benefit-item i {
    color: var(--accent);
    font-size: 0.9rem;
}

/* For Option 3 */
.process-steps {
    color: var(--primary);
    font-weight: 500;
    margin-bottom: 1rem;
    font-size: 1rem;
    background: rgba(67, 97, 238, 0.1);
    padding: 0.8rem;
    border-radius: 10px;
    border-left: 3px solid var(--accent);
}
.enquiry-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 1.5rem 2rem 2.5rem 2rem; /* Even less top padding */
    border: var(--border);
    box-shadow: var(--shadow);
    transition: var(--transition);
    backdrop-filter: blur(10px);
    text-align: center;
}

.enquiry-icon {
    font-size: 2.5rem; /* Slightly smaller icon */
    color: var(--primary);
    margin-bottom: 0.8rem; /* Reduced spacing */
}

.enquiry-card h3 {
    color: var(--text);
    font-size: 1.8rem;
    margin-bottom: 1rem;
    font-weight: 700;
    margin-top: 0;
    padding-top: 0;
}
.explore-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.8rem 1.8rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    font-size: 0.9rem;
    margin-top: 1rem;
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
}

.explore-more-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(67, 97, 238, 0.5);
}
/* ===== ENQUIRY SECTION ===== */
.enquiry-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
    position: relative;
}

.enquiry-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" opacity="0.03"><path fill="%236c63ff" d="M500,200c165,0,300,135,300,300S665,800,500,800S200,665,200,500S335,200,500,200z"/></svg>') repeat; */
    background: linear-gradient(to bottom, var(--dark), var(--dark-secondary));

}

.enquiry-section .container {
    position: relative;
    z-index: 1;
}

.section-title {
    font-size: 42px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 15px;
    /* background: linear-gradient(135deg, #6c63ff, #ff6584); */
    background: #fff;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    text-align: center;
    color: #fff;
    font-size: 18px;
    max-width: 600px;
    margin: 0 auto 50px;
    line-height: 1.6;
}

/* ===== ALERT MESSAGES ===== */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 500;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert.success {
    background: #d4ffdc;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert.error {
    background: #ffd4d4;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* ===== ENQUIRY CARD ===== */
.enquiry-content {
    max-width: 900px;
    margin: 0 auto;
}

.enquiry-card {
    background: var(--card-hover);
    border-radius: 20px;
    padding: 50px;
    box-shadow: 0 20px 60px rgba(108, 99, 255, 0.15);
    border: 1px solid rgba(108, 99, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.enquiry-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 5px;
    background: linear-gradient(to right, #6c63ff, #ff6584);
}

.enquiry-card h3 {
    font-size: 32px;
    color: #fff;
    text-align: center;
    margin-bottom: 10px;
    font-weight: 700;
}

.process-steps {
    text-align: center;
    color: #6c63ff;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

/* ===== FORM STYLES ===== */
.enquiry-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.enquiry-form input,
.enquiry-form select,
.enquiry-form textarea {
    width: 100%;
    padding: 16px 20px;
    border: 2px solid #e6e9f0;
    border-radius: 12px;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
    transition: all 0.3s;
    background: white;
    color: #333;
}

.enquiry-form input:focus,
.enquiry-form select:focus,
.enquiry-form textarea:focus {
    outline: none;
    border-color: #6c63ff;
    box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.15);
}

.enquiry-form input::placeholder,
.enquiry-form textarea::placeholder {
    color: #999;
}

.enquiry-form input[type="date"],
.enquiry-form input[type="time"] {
    cursor: pointer;
}

.enquiry-form input[type="date"]::-webkit-calendar-picker-indicator,
.enquiry-form input[type="time"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: 0.6;
    transition: opacity 0.3s;
}

.enquiry-form input[type="date"]::-webkit-calendar-picker-indicator:hover,
.enquiry-form input[type="time"]::-webkit-calendar-picker-indicator:hover {
    opacity: 1;
}

.enquiry-form select {
    appearance: none;
    background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c63ff' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 20px center/16px;
    cursor: pointer;
    padding-right: 50px;
}

.enquiry-form select[multiple] {
    padding: 16px 20px;
    height: 150px;
    background-image: none;
}

.enquiry-form select[multiple] option {
    padding: 10px;
    margin: 5px 0;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
}

.enquiry-form select[multiple] option:hover {
    background: rgba(108, 99, 255, 0.1);
}

.enquiry-form select[multiple] option:checked {
    background: #6c63ff;
    color: white;
}

.enquiry-form textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.5;
}

/* ===== LABELS ===== */
.enquiry-form label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #fff;
    font-size: 16px;
}

.enquiry-form label:not(.checkbox-group label) {
    grid-column: 1 / -1;
}

/* ===== CHECKBOX GROUP ===== */
.checkbox-group {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    grid-column: 1 / -1;
    background: #f8f9ff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e6e9f0;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    padding: 10px 20px;
    border-radius: 8px;
    background: white;
    transition: all 0.3s;
    border: 1px solid #e6e9f0;
    margin: 0;
}

.checkbox-group label:hover {
    border-color: #6c63ff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 99, 255, 0.1);
}

.checkbox-group input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #6c63ff;
    cursor: pointer;
}

/* ===== FILE INPUT ===== */
.enquiry-form input[type="file"] {
    padding: 14px 20px;
    cursor: pointer;
    background: #f8f9ff;
    border: 2px dashed #6c63ff;
}

.enquiry-form input[type="file"]::file-selector-button {
    background: #6c63ff;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    margin-right: 20px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s;
}

.enquiry-form input[type="file"]::file-selector-button:hover {
    background: #5a52e0;
}

/* ===== SUBMIT BUTTON ===== */
.enquiry-btn {
    grid-column: 1 / -1;
    justify-self: center;
    background: linear-gradient(135deg, #6c63ff, #ff6584);
    color: white;
    border: none;
    padding: 18px 50px;
    font-size: 18px;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.4s;
    margin-top: 20px;
    position: relative;
    overflow: hidden;
    letter-spacing: 0.5px;
    min-width: 200px;
}

.enquiry-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
}

.enquiry-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(108, 99, 255, 0.3);
}

.enquiry-btn:hover::before {
    left: 100%;
}

.enquiry-btn:active {
    transform: translateY(-1px);
}

/* ===== FORM VALIDATION STYLES ===== */
.enquiry-form input:required,
.enquiry-form select:required,
.enquiry-form textarea:required {
    border-left: 4px solid #6c63ff;
}

.enquiry-form input:invalid:not(:focus):not(:placeholder-shown),
.enquiry-form select:invalid:not(:focus),
.enquiry-form textarea:invalid:not(:focus):not(:placeholder-shown) {
    border-color: #ff4757;
    background: #fff5f5;
}

.enquiry-form input:valid:not(:placeholder-shown),
.enquiry-form select:valid,
.enquiry-form textarea:valid:not(:placeholder-shown) {
    border-color: #27ae60;
    background: #f5fff8;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 992px) {
    .enquiry-form {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .enquiry-card {
        padding: 40px 30px;
    }
    
    .section-title {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .enquiry-section {
        padding: 60px 0;
    }
    
    .enquiry-card {
        padding: 30px 20px;
        border-radius: 15px;
    }
    
    .enquiry-card h3 {
        font-size: 28px;
    }
    
    .section-title {
        font-size: 32px;
    }
    
    .section-subtitle {
        font-size: 16px;
        padding: 0 20px;
    }
    
    .checkbox-group {
        flex-direction: column;
        gap: 15px;
    }
    
    .checkbox-group label {
        width: 100%;
    }
    
    .enquiry-btn {
        width: 100%;
        max-width: 300px;
    }
}

@media (max-width: 480px) {
    .enquiry-form input,
    .enquiry-form select,
    .enquiry-form textarea {
        padding: 14px 16px;
        font-size: 15px;
    }
    
    .enquiry-card {
        padding: 25px 15px;
    }
    
    .enquiry-card h3 {
        font-size: 24px;
    }
    
    .process-steps {
        font-size: 14px;
    }
    
    .section-title {
        font-size: 28px;
    }
    
    .enquiry-btn {
        padding: 16px 30px;
        font-size: 16px;
    }
}

/* ===== ANIMATIONS ===== */
.enquiry-form input,
.enquiry-form select,
.enquiry-form textarea {
    animation: fadeInUp 0.5s ease backwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.enquiry-card {
    animation: scaleIn 0.6s ease;
}

@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* ===== FOCUS VISIBLE ===== */
.enquiry-form input:focus-visible,
.enquiry-form select:focus-visible,
.enquiry-form textarea:focus-visible {
    outline: 2px solid #6c63ff;
    outline-offset: 2px;
}


 /* Brochure Download Button Styles */
 .brochure-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: transparent;
            color: var(--text);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            border: 2px solid var(--primary);
            cursor: pointer;
            font-size: 1rem;
            margin-left: 1rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1s ease 0.8s forwards;
            position: relative;
            overflow: hidden;
        }
        
        .brochure-button:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .brochure-button i {
            margin-right: 8px;
        }
        
        /* Responsive adjustments for buttons */
        @media (max-width: 768px) {
            .hero-buttons {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .brochure-button {
                margin-left: 0;
            }
        }
/* Make sure Font Awesome icons are loaded */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
 </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-content">
            <div class="preloader-logo">
                <!-- Add your logo here or use text -->
                <span><img src="images/visual vibe logo.svg" class="img-fluid" style="width:300px;"></span>
            </div>
            <div class="preloader-spinner">
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
                <div class="spinner-circle"></div>
            </div>
            <div class="preloader-text">Loading...</div>
            <div class="progress-bar">
                <div class="progress"></div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <img src="images/visual vibe logo.svg" class="img-fluid" style="width:300px; height:150px  !important; width:220px !important;">

            <button class="mobile-menu-btn">
                <span class="hamburger"></span>
            </button>
            <div class="nav-links">
                <a href="#home" class="active">Home</a>
                <a href="#carousel">Showcase</a>
                <a href="#about">About</a>
                <a href="#services">Services</a>
                <a href="#team">Our Team</a>
                <a href="#enquiry">Enquiry</a> 
                <a href="#contact">Contact</a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <header id="home">
        <div class="hero-bg"></div>
        <div class="hero-content">
            
            <h1 class="hero-title">Transform Your <span>Vision</span> Into Reality</h1>
            <p class="hero-subtitle">We craft stunning visual experiences that elevate brands and captivate audiences.</p>
            <a href="#contact" class="cta-button">Get Started</a>
        </div>
    </header>
    
    <!-- Carousel Section -->
    <section id="carousel" class="carousel-section">
    <div class="container">
        <h2 class="section-title">The Digital Workspace</h2>
        <p class="section-subtitle">Showcasing creativity, strategy, and teamwork behind every digital success.</p>
        
        <?php if (!empty($slides)): ?>
        <div class="carousel-container">
            <div class="carousel-slides">
                <?php foreach ($slides as $index => $slide): ?>
                <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($slide['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($slide['title']); ?>">
                    <div class="carousel-content">
                        <h3 class="carousel-title"><?php echo htmlspecialchars($slide['title']); ?></h3>
                        <p class="carousel-description"><?php echo htmlspecialchars($slide['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="carousel-controls">
                <button class="carousel-btn prev-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="carousel-btn next-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="carousel-indicators">
                <?php foreach ($slides as $index => $slide): ?>
                <span class="carousel-indicator <?php echo $index === 0 ? 'active' : ''; ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="no-slides-message">
            <p>No carousel slides available at the moment.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
    
    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <h2 class="section-title">About Us</h2>
            <p class="section-subtitle">Your Idea, Our Vision - Bringing creative concepts to life through exceptional design and technology.</p>
            
            <div class="about">
                <div class="about-content">
                    <h3>Who We Are</h3>
                    <p>Visual Vibe is a premier creative agency specializing in visual design and digital solutions. We combine artistic vision with technical expertise to deliver exceptional results for our clients.</p>
                    <p>Our team of passionate designers and developers work collaboratively to transform your ideas into visually stunning realities that resonate with your audience and elevate your brand.</p>
                    <p>With a focus on innovation and quality, we've helped businesses of all sizes establish powerful visual identities and digital presences.</p>
                </div>
                <div class="about-image">
                    <!-- Replace with your actual about image -->
                    <!-- <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Creative team working"> -->
                    <img src="images/visual vibes png white.png" alt="Creative team working">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Comprehensive creative solutions tailored to your unique needs and goals.</p>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3>Video Editing & Motion Graphics</h3>
                    <p>Professional post-production and dynamic animations that bring your content to life with cinematic quality.</p>
                    <a href="video_service.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                    <!-- <a href="video_service.html" class="cta-button1">Explore Samples</a> -->
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Logo & Brand Design</h3>
                    <p>Strategic branding solutions that capture your essence and communicate your values visually.</p>
                    <a href="logos.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3>Website Development</h3>
                    <p>Custom, responsive websites built for performance, SEO, and exceptional user experiences.</p>
                    <a href="websites.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3>Posters & Flyers Designing</h3>
                    <p>Attention-grabbing print materials that effectively communicate your message.</p>
                    <a href="flyers.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <h3>2D & 3D Graphic Design</h3>
                    <p>Stunning visual assets for all media, from print to digital to environmental displays.</p>
                    <a href="graphics.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
               
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Android App Development</h3>
                    <p>Native Android applications designed for scalability, security, and seamless functionality.</p>
                    <a href="appli.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <h3>Custom Merchandise Printing</h3>
                    <p>Premium quality branded merchandise that extends your visual identity into physical spaces.</p>
                    <a href="prin.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-pencil-ruler"></i>
                    </div>
                    <h3>UI/UX Design</h3>
                    <p>Intuitive interfaces and user journeys that enhance engagement and conversion rates.</p>
                    <a href="uiux.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Digital Marketing</h3>
                    <p>Strategic campaigns and compelling content designed to increase visibility, engagement, and conversions.</p>
                    <a href="marketing.php" class="explore-more-btn">
                        Explore More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
               
            </div>
        </div>
    </section>


   <!-- Client Enquiry Section -->
<section id="enquiry" class="enquiry-section">
    <div class="container">

        <h2 class="section-title">Get In Touch</h2>
        <p class="section-subtitle">
            Have a project in mind? Let's discuss how we can bring your vision to life.
        </p>

        <!-- SUCCESS / ERROR MESSAGE -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?= $_SESSION['message_type']; ?>">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="enquiry-content">
            <div class="enquiry-card">

                <h3>Project Enquiry Form</h3>
                <p class="process-steps">
                    1. Fill Form → 2. We Review → 3. Get Quote → 4. Start Project
                </p>

                <form 
                    action="submit_enquiry.php"
                    method="POST"
                    enctype="multipart/form-data"
                    class="enquiry-form"
                >

                    <!-- BASIC DETAILS -->
                    <input type="text" name="full_name" placeholder="Full Name *" required>
                    <input type="email" name="email" placeholder="Email Address *" required>
                    <input type="tel" name="phone" placeholder="Phone Number *" required>

                    <input type="text" name="company" placeholder="Company Name">
                    <input type="text" name="location" placeholder="Location">

                    <!-- SERVICES -->
                    <label>Service Type *</label>
                    <select name="service_type[]" multiple required>
                        <option value="Logo Design">Logo Design</option>
                        <option value="Website Development">Website Development</option>
                        <option value="App Development">App Development</option>
                        <option value="UI/UX Design">UI/UX Design</option>
                        <option value="Video Editing">Video Editing</option>
                        <option value="Digital Marketing">Digital Marketing</option>
                        <option value="Other">Other</option>
                    </select>

                    <input type="text" name="other_service" placeholder="If other, specify">

                    <!-- PROJECT TYPE -->
                    <label>Project Type *</label>
                    <select name="project_type" required>
                        <option value="">Select</option>
                        <option value="new">New Project</option>
                        <option value="redesign">Redesign</option>
                    </select>

                    <!-- DESCRIPTION -->
                    <textarea 
                        name="project_description" 
                        placeholder="Describe your project *"
                        required
                    ></textarea>

                    <!-- DESIGN STYLE -->
                    <input type="text" name="design_style" placeholder="Preferred design style">

                    <!-- FILE -->
                    <label>Reference File (optional)</label>
                    <input type="file" name="reference_file">

                    <!-- DEADLINE -->
                    <label>Expected Deadline</label>
                    <input type="date" name="deadline">

                    <!-- BUDGET -->
                    <select name="budget_range">
                        <option value="">Budget Range</option>
                        <option value="Under 10K">Under ₹10K</option>
                        <option value="10K–25K">₹10K – ₹25K</option>
                        <option value="25K–50K">₹25K – ₹50K</option>
                        <option value="50K+">₹50K+</option>
                    </select>

                    <!-- CONTACT PREFERENCE -->
                    <label>Preferred Contact *</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="contact_preference[]" value="whatsapp"> WhatsApp</label>
                        <label><input type="checkbox" name="contact_preference[]" value="call"> Call</label>
                        <label><input type="checkbox" name="contact_preference[]" value="email"> Email</label>
                    </div>

                    <!-- BEST TIME -->
                    <label>Best Time to Contact</label>
                    <input type="time" name="best_time">

                    <!-- SOURCE -->
                    <select name="hear_about">
                        <option value="">How did you hear about us?</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Google">Google</option>
                        <option value="Reference">Reference</option>
                        <option value="Other">Other</option>
                    </select>

                    <input type="text" name="other_source" placeholder="If other source">

                    <!-- NOTES -->
                    <textarea name="additional_notes" placeholder="Additional notes"></textarea>

                    <button type="submit" class="enquiry-btn">
                        Submit Enquiry
                    </button>

                </form>

            </div>
        </div>
    </div>
</section>
<script>
    setTimeout(function () {
        const alertBox = document.querySelector('.alert');
        if (alertBox) {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";

            setTimeout(() => {
                alertBox.remove();
            }, 500);
        }
    }, 5000); // 5 seconds
</script>


    
    <section class="team-section" id="team">
    <div class="container">

        <h2 class="section-title">Our Team</h2>
        <p class="section-caption">
            "Driven by passion and precision, our team delivers excellence every month."
        </p>

        <?php foreach ($teams as $category => $members): ?>

            <div class="team-category">
                <h3 class="team-category-title">
                    <?= htmlspecialchars($category) ?>
                </h3>

                <div class="row justify-content-center">

                    <?php foreach ($members as $member): ?>

                        <div class="col-md-6 col-lg-4">
                            <div class="team-member">

                                <img 
                                    src="<?= htmlspecialchars($member['image_url']) ?>" 
                                    alt="<?= htmlspecialchars($member['name']) ?>" 
                                    class="team-img"
                                >

                                <h4 class="team-name">
                                    <?= htmlspecialchars($member['name']) ?>
                                </h4>

                                <?php if (!empty($member['designation'])): ?>
                                    <p class="team-designation">
                                        <?= nl2br(htmlspecialchars($member['designation'])) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>

                    <?php endforeach; ?>

                </div>
            </div>

        <?php endforeach; ?>

    </div>
</section>

  
    
    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="contact-container container">
            <h2 class="section-title">Let's Create Together</h2>
            <p class="section-subtitle">Ready to bring your vision to life? Reach out to discuss your project.</p>
            
            <div class="contact-info">
               <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Call Us</h4>
                        <a href="tel:+919601982190">+91 96019 82190</a>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Follow Us</h4>
                        <a href="https://www.instagram.com/visualvibe.space?igsh=YjByN2djdWM4bmU2" >@visualvibe.space</a>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details" style="width:200px !important;">
                        <h4>Email Us</h4>
                        <a href="mailto:visualvibe.space@gmail.com" class="email">visualvibe.space@gmail.com</a>
                    </div>
                </div>
            </div>

            <!-- Add this to your contact section -->
<div class="contact-download" style="text-align: center; margin-top: 3rem;">
    <p style="margin-bottom: 1.5rem; color: var(--text-secondary);">Want to learn more about us?</p>
    <a href="brochures/visual vibe brochure.pdf" class="brochure-button" download>
        <i class="fas fa-download"></i> Download Our Brochure
    </a>
</div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <!-- <div class="footer-logo"><img src="images/visual vibes png white.png" class="img-fluid" style="width:300px;"></div> -->
            <p class="footer-tagline">Your Idea, Our Vision &trade;</p>
            
            <div class="social-links">
                <a href="https://www.facebook.com/share/1AEhnoYA92/?mibextid=wwXIfr" class="social-link"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.instagram.com/visualvibe.space?igsh=YjByN2djdWM4bmU2" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="https://x.com/spacevisualvibe?s=11" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="https://www.linkedin.com/in/visual-vibe-71a24b393?utm_source=share_via&utm_content=profile&utm_medium=member_ios" class="social-link"><i class="fab fa-linkedin-in"></i></a>
            </div>
            
            <p class="copyright">&copy; 2025 Visual Vibe &trade;. All Rights Reserved.</p>
        </div>
    </footer>

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
            const elements = document.querySelectorAll('.section-title, .section-subtitle, .section-caption, .about-image, .about-content, .service-card, .team-category, .team-member, .contact-item');
            
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
        
        // Carousel functionality
        const carouselSlides = document.querySelector('.carousel-slides');
        const slides = document.querySelectorAll('.carousel-slide');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        const indicators = document.querySelectorAll('.carousel-indicator');
        
        let currentSlide = 0;
        const slideCount = slides.length;
        
        // Function to update carousel
        function updateCarousel() {
            carouselSlides.style.transform = `translateX(-${currentSlide * 100}%)`;
            
            // Update active slide
            slides.forEach((slide, index) => {
                slide.classList.toggle('active', index === currentSlide);
            });
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === currentSlide);
            });
        }
        
        // Next slide
        function nextSlide() {
            currentSlide = (currentSlide + 1) % slideCount;
            updateCarousel();
        }
        
        // Previous slide
        function prevSlide() {
            currentSlide = (currentSlide - 1 + slideCount) % slideCount;
            updateCarousel();
        }
        
        // Event listeners for buttons
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);
        
        // Event listeners for indicators
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                currentSlide = index;
                updateCarousel();
            });
        });
        
        // Auto slide
        let autoSlide = setInterval(nextSlide, 5000);
        
        // Pause auto slide on hover
        const carouselContainer = document.querySelector('.carousel-container');
        carouselContainer.addEventListener('mouseenter', () => {
            clearInterval(autoSlide);
        });
        
        carouselContainer.addEventListener('mouseleave', () => {
            autoSlide = setInterval(nextSlide, 5000);
        });
    </script>
    <script>
        // Preloader functionality
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            const progress = document.querySelector('.progress');
            const preloaderText = document.querySelector('.preloader-text');
            
            let progressValue = 0;
            const progressInterval = setInterval(() => {
                progressValue += Math.random() * 15;
                if (progressValue > 100) {
                    progressValue = 100;
                }
                progress.style.width = progressValue + '%';
                
                // Update loading text based on progress
                if (progressValue < 30) {
                    preloaderText.textContent = 'Loading assets...';
                } else if (progressValue < 60) {
                    preloaderText.textContent = 'Initializing...';
                } else if (progressValue < 90) {
                    preloaderText.textContent = 'Almost ready...';
                } else {
                    preloaderText.textContent = 'Ready!';
                }
                
                if (progressValue >= 100) {
                    clearInterval(progressInterval);
                    
                    // Wait a bit more for everything to load
                    setTimeout(() => {
                        preloader.classList.add('fade-out');
                        
                        // Remove preloader from DOM after fade out
                        setTimeout(() => {
                            preloader.remove();
                        }, 600);
                    }, 500);
                }
            }, 100);
            
            // Fallback: if something goes wrong, hide preloader after 4 seconds
            setTimeout(() => {
                if (preloader && !preloader.classList.contains('fade-out')) {
                    preloader.classList.add('fade-out');
                    setTimeout(() => {
                        preloader.remove();
                    }, 600);
                }
            }, 4000);
        });
        
        // Wait for window load as well
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader && !preloader.classList.contains('fade-out')) {
                const progress = document.querySelector('.progress');
                progress.style.width = '100%';
                
                setTimeout(() => {
                    preloader.classList.add('fade-out');
                    setTimeout(() => {
                        preloader.remove();
                    }, 600);
                }, 500);
            }
        });
    </script>
</body>
</html>