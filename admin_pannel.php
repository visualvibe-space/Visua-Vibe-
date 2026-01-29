<?php
/* ==================================================
   LOAD CONFIG (SESSION + DB)
================================================== */

require_once 'config.php';

/* ==================================================
   LOGIN CHECK
================================================== */

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

/* ==================================================
   OPTIONAL ROLE CHECK (SAFE)
================================================== */

/*
Only enable this if your database has a role column
and you set $_SESSION['admin_role'] at login
*/

$allowed_roles = ['admin', 'editor'];

if (isset($_SESSION['admin_role'])) {
    if (!in_array($_SESSION['admin_role'], $allowed_roles)) {
        die('Access denied. You do not have permission to access this page.');
    }
}

/* ==================================================
   CSRF TOKEN
================================================== */

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ==================================================
   AUTO LOGOUT (30 MIN)
================================================== */

$inactive = 1800; // 30 minutes

if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > $inactive) {

    session_unset();
    session_destroy();

    header("Location: admin_login.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

/* ==================================================
   VARIABLES
================================================== */

$message = '';
$messageType = '';
$currentSlide = null;

/* ==================================================
   DATABASE READY
================================================== */

/*
$pdo is already available from config.php
Do NOT reconnect again.
*/


/* ===========================
   FORM ACTIONS
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    switch ($action) {

        /* ================= ADD SLIDE ================= */

        case 'add_slide':
            try {

                $imageUrl = 'images/default.jpg';

                if (!empty($_FILES['image']['name'])) {

                    $uploadDir = 'uploads/carousel/';

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                    $imageUrl = $targetFile;
                }

                $stmt = $pdo->prepare("
                    INSERT INTO carousel_slides 
                    (title, subtitle, description, image_url, display_order, is_active)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $_POST['title'],
                    $_POST['subtitle'] ?? '',
                    $_POST['description'],
                    $imageUrl,
                    $_POST['display_order'] ?? 0,
                    $_POST['is_active'] ?? 1
                ]);

                $message = "Slide added successfully!";
                $messageType = "success";

            } catch (PDOException $e) {
                $message = $e->getMessage();
                $messageType = "error";
            }
            break;


        /* ================= EDIT SLIDE ================= */

        case 'edit_slide':
            try {
                $stmt = $pdo->prepare("SELECT * FROM carousel_slides WHERE id = ?");
                $stmt->execute([$_POST['slide_id']]);
                $currentSlide = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $message = $e->getMessage();
                $messageType = "error";
            }
            break;


        /* ================= UPDATE SLIDE ================= */

        case 'update_slide':
            try {

                $slideId = $_POST['slide_id'];

                if (!empty($_FILES['image']['name'])) {

                    $uploadDir = 'uploads/carousel/';
                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

                    $stmt = $pdo->prepare("
                        UPDATE carousel_slides SET
                        title = ?, subtitle = ?, description = ?, 
                        image_url = ?, display_order = ?, is_active = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $_POST['title'],
                        $_POST['subtitle'] ?? '',
                        $_POST['description'],
                        $targetFile,
                        $_POST['display_order'] ?? 0,
                        $_POST['is_active'] ?? 1,
                        $slideId
                    ]);

                } else {

                    $stmt = $pdo->prepare("
                        UPDATE carousel_slides SET
                        title = ?, subtitle = ?, description = ?, 
                        display_order = ?, is_active = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $_POST['title'],
                        $_POST['subtitle'] ?? '',
                        $_POST['description'],
                        $_POST['display_order'] ?? 0,
                        $_POST['is_active'] ?? 1,
                        $slideId
                    ]);
                }

                $message = "Slide updated successfully!";
                $messageType = "success";

            } catch (PDOException $e) {
                $message = $e->getMessage();
                $messageType = "error";
            }
            break;


        /* ================= DELETE SLIDE ================= */

        case 'delete_slide':
            try {
                $stmt = $pdo->prepare("DELETE FROM carousel_slides WHERE id = ?");
                $stmt->execute([$_POST['slide_id']]);

                $message = "Slide deleted successfully!";
                $messageType = "success";

            } catch (PDOException $e) {
                $message = $e->getMessage();
                $messageType = "error";
            }
            break;
    }
}


/* ===========================
   FETCH SLIDES
=========================== */

$slides = [];

try {
    $stmt = $pdo->query("
        SELECT * FROM carousel_slides
        ORDER BY display_order ASC, created_at DESC
    ");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // silent
}




/* ===========================
   FETCH TEAM MEMBERS
=========================== */

$teamMembers = [];
$teamCategories = [];

try {
    $stmt = $pdo->query("SELECT * FROM team_members ORDER BY category, display_order, name");
    $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract unique categories
    $teamCategories = array_unique(array_column($teamMembers, 'category'));
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE TEAM FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_team'])) {
    try {
        $memberId = $_POST['member_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? 'images/default.jpg';
        
        // Handle image upload
        if (!empty($_FILES['team_image']['name'])) {
            $uploadDir = 'uploads/team/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['team_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['team_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        if (!empty($memberId)) {
            // Update existing team member
            $stmt = $pdo->prepare("
                UPDATE team_members SET
                name = ?, designation = ?, image_url = ?, 
                category = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['designation'],
                $imageUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $memberId
            ]);
            
            $message = "Team member updated successfully!";
        } else {
            // Insert new team member
            $stmt = $pdo->prepare("
                INSERT INTO team_members 
                (name, designation, image_url, category, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['name'],
                $_POST['designation'],
                $imageUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Team member added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh team data
        $stmt = $pdo->query("SELECT * FROM team_members ORDER BY category, display_order, name");
        $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $teamCategories = array_unique(array_column($teamMembers, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE TEAM MEMBER DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team_member'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->execute([$_POST['member_id']]);
        
        $message = "Team member deleted successfully!";
        $messageType = "success";
        
        // Refresh team data
        $stmt = $pdo->query("SELECT * FROM team_members ORDER BY category, display_order, name");
        $teamMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $teamCategories = array_unique(array_column($teamMembers, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE TEAM EDIT
=========================== */

$editingMember = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_team_member'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM team_members WHERE id = ?");
        $stmt->execute([$_POST['member_id']]);
        $editingMember = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* =====================================================
   FETCH PORTFOLIO VIDEOS
===================================================== */

$portfolioVideos = [];

try {
    $stmt = $pdo->query("
        SELECT * 
        FROM portfolio_videos 
        ORDER BY display_order ASC, created_at DESC
    ");
    $portfolioVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // silent
}


/* =====================================================
   HANDLE ADD / UPDATE PORTFOLIO VIDEO
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_portfolio'])) {

    try {

        $videoId = $_POST['video_id'] ?? null;

        /* ======================
           VALIDATION
        ====================== */

        $maxVideoSize = 800 * 1024 * 1024; // 800 MB
        $allowedVideoTypes = ['mp4','mov','mkv','avi'];
        $allowedImageTypes = ['jpg','jpeg','png','webp'];

        /* ======================
           THUMBNAIL UPLOAD
        ====================== */

        $thumbnailFile = $_POST['current_thumbnail'] ?? '';

        if (!empty($_FILES['thumbnail']['name'])) {

            $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedImageTypes)) {
                throw new Exception("Invalid thumbnail format.");
            }

            $thumbDir = "uploads/portfolio/thumbnails/";

            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0777, true);
            }

            $thumbnailFile = $thumbDir . time() . '_' . uniqid() . '.' . $ext;

            move_uploaded_file(
                $_FILES['thumbnail']['tmp_name'],
                $thumbnailFile
            );
        }

        /* ======================
           VIDEO UPLOAD
        ====================== */

        $videoFile = $_POST['current_video'] ?? '';

        if (!empty($_FILES['video']['name'])) {

            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedVideoTypes)) {
                throw new Exception("Invalid video format.");
            }

            if ($_FILES['video']['size'] > $maxVideoSize) {
                throw new Exception("Video must be under 800 MB.");
            }

            $videoDir = "uploads/portfolio/videos/";

            if (!is_dir($videoDir)) {
                mkdir($videoDir, 0777, true);
            }

            $videoFile = $videoDir . time() . '_' . uniqid() . '.' . $ext;

            move_uploaded_file(
                $_FILES['video']['tmp_name'],
                $videoFile
            );
        }

        /* ======================
           INSERT / UPDATE
        ====================== */

        if ($videoId) {

            // UPDATE
            $stmt = $pdo->prepare("
                UPDATE portfolio_videos SET
                    title = ?,
                    description = ?,
                    video_file = ?,
                    thumbnail_file = ?,
                    display_order = ?,
                    is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $videoFile,
                $thumbnailFile,
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $videoId
            ]);

            $message = "✅ Portfolio video updated successfully!";

        } else {

            // INSERT
            $stmt = $pdo->prepare("
                INSERT INTO portfolio_videos
                (title, description, video_file, thumbnail_file, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $videoFile,
                $thumbnailFile,
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);

            $message = "✅ Portfolio video added successfully!";
        }

        $messageType = "success";

        // Refresh list
        $stmt = $pdo->query("
            SELECT * 
            FROM portfolio_videos 
            ORDER BY display_order ASC, created_at DESC
        ");
        $portfolioVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {

        $message = "❌ " . $e->getMessage();
        $messageType = "error";
    }
}


/* =====================================================
   DELETE PORTFOLIO VIDEO
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_portfolio'])) {

    try {

        $stmt = $pdo->prepare("DELETE FROM portfolio_videos WHERE id = ?");
        $stmt->execute([$_POST['video_id']]);

        $message = "🗑 Portfolio video deleted successfully!";
        $messageType = "success";

        $stmt = $pdo->query("
            SELECT * 
            FROM portfolio_videos 
            ORDER BY display_order ASC, created_at DESC
        ");
        $portfolioVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {

        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}


/* =====================================================
   EDIT VIDEO FETCH
===================================================== */

$editingVideo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_portfolio'])) {

    try {
        $stmt = $pdo->prepare("SELECT * FROM portfolio_videos WHERE id = ?");
        $stmt->execute([$_POST['video_id']]);
        $editingVideo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH LOGO PORTFOLIO
=========================== */

$logoPortfolio = [];

try {
    $stmt = $pdo->query("SELECT * FROM logo_portfolio ORDER BY display_order, created_at DESC");
    $logoPortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE LOGO FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_logo'])) {
    try {
        $logoId = $_POST['logo_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? '';
        
        // Handle image upload
        if (!empty($_FILES['logo_image']['name'])) {
            $uploadDir = 'uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['logo_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        if (!empty($logoId)) {
            // Update existing logo
            $stmt = $pdo->prepare("
                UPDATE logo_portfolio SET
                title = ?, description = ?, image_url = ?, 
                category = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $logoId
            ]);
            
            $message = "Logo updated successfully!";
        } else {
            // Insert new logo
            $stmt = $pdo->prepare("
                INSERT INTO logo_portfolio 
                (title, description, image_url, category, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Logo added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh logo data
        $stmt = $pdo->query("SELECT * FROM logo_portfolio ORDER BY display_order, created_at DESC");
        $logoPortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE LOGO DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logo'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM logo_portfolio WHERE id = ?");
        $stmt->execute([$_POST['logo_id']]);
        
        $message = "Logo deleted successfully!";
        $messageType = "success";
        
        // Refresh logo data
        $stmt = $pdo->query("SELECT * FROM logo_portfolio ORDER BY display_order, created_at DESC");
        $logoPortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE LOGO EDIT
=========================== */

$editingLogo = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_logo'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM logo_portfolio WHERE id = ?");
        $stmt->execute([$_POST['logo_id']]);
        $editingLogo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH WEBSITE PORTFOLIO
=========================== */

$websitePortfolio = [];

try {
    $stmt = $pdo->query("SELECT * FROM website_portfolio ORDER BY display_order, created_at DESC");
    $websitePortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE WEBSITE FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_website'])) {
    try {
        $websiteId = $_POST['website_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? '';
        
        // Handle image upload
        if (!empty($_FILES['website_image']['name'])) {
            $uploadDir = 'uploads/websites/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['website_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['website_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        if (!empty($websiteId)) {
            // Update existing website
            $stmt = $pdo->prepare("
                UPDATE website_portfolio SET
                title = ?, description = ?, image_url = ?, 
                website_url = ?, category = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $_POST['website_url'],
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $websiteId
            ]);
            
            $message = "Website portfolio updated successfully!";
        } else {
            // Insert new website
            $stmt = $pdo->prepare("
                INSERT INTO website_portfolio 
                (title, description, image_url, website_url, category, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $_POST['website_url'],
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Website portfolio added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh website data
        $stmt = $pdo->query("SELECT * FROM website_portfolio ORDER BY display_order, created_at DESC");
        $websitePortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE WEBSITE DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_website'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM website_portfolio WHERE id = ?");
        $stmt->execute([$_POST['website_id']]);
        
        $message = "Website portfolio deleted successfully!";
        $messageType = "success";
        
        // Refresh website data
        $stmt = $pdo->query("SELECT * FROM website_portfolio ORDER BY display_order, created_at DESC");
        $websitePortfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE WEBSITE EDIT
=========================== */

$editingWebsite = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_website'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM website_portfolio WHERE id = ?");
        $stmt->execute([$_POST['website_id']]);
        $editingWebsite = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH FLYERS & POSTERS
=========================== */

$flyersPosters = [];
$flyerCategories = [];

try {
    $stmt = $pdo->query("SELECT * FROM flyers_posters ORDER BY category, display_order, created_at DESC");
    $flyersPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract unique categories
    $flyerCategories = array_unique(array_column($flyersPosters, 'category'));
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE FLYER/POSTER FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_flyer'])) {
    try {
        $flyerId = $_POST['flyer_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? '';
        $thumbnailUrl = $_POST['current_thumbnail'] ?? '';
        
        // Handle main image upload
        if (!empty($_FILES['flyer_image']['name'])) {
            $uploadDir = 'uploads/flyers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['flyer_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['flyer_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        // Handle thumbnail upload (optional)
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploadDir = 'uploads/flyers/thumbnails/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
                $thumbnailUrl = $targetFile;
            }
        }
        
        if (!empty($flyerId)) {
            // Update existing flyer/poster
            $stmt = $pdo->prepare("
                UPDATE flyers_posters SET
                title = ?, description = ?, image_url = ?, 
                thumbnail_url = ?, category = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $flyerId
            ]);
            
            $message = "Flyer/Poster updated successfully!";
        } else {
            // Insert new flyer/poster
            $stmt = $pdo->prepare("
                INSERT INTO flyers_posters 
                (title, description, image_url, thumbnail_url, category, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Flyer/Poster added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh flyers data
        $stmt = $pdo->query("SELECT * FROM flyers_posters ORDER BY category, display_order, created_at DESC");
        $flyersPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $flyerCategories = array_unique(array_column($flyersPosters, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE FLYER/POSTER DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_flyer'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM flyers_posters WHERE id = ?");
        $stmt->execute([$_POST['flyer_id']]);
        
        $message = "Flyer/Poster deleted successfully!";
        $messageType = "success";
        
        // Refresh flyers data
        $stmt = $pdo->query("SELECT * FROM flyers_posters ORDER BY category, display_order, created_at DESC");
        $flyersPosters = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $flyerCategories = array_unique(array_column($flyersPosters, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE FLYER/POSTER EDIT
=========================== */

$editingFlyer = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_flyer'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM flyers_posters WHERE id = ?");
        $stmt->execute([$_POST['flyer_id']]);
        $editingFlyer = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH 2D & 3D GRAPHIC DESIGNS
=========================== */

$graphicDesigns = [];
$graphicCategories = [];
$designTypes = [];

try {
    $stmt = $pdo->query("SELECT * FROM graphic_designs ORDER BY design_type, category, display_order, created_at DESC");
    $graphicDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract unique categories
    $graphicCategories = array_unique(array_column($graphicDesigns, 'category'));
    $designTypes = array_unique(array_column($graphicDesigns, 'design_type'));
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE GRAPHIC DESIGN FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_graphic'])) {
    try {
        $graphicId = $_POST['graphic_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? '';
        $thumbnailUrl = $_POST['current_thumbnail'] ?? '';
        
        // Handle main image upload
        if (!empty($_FILES['graphic_image']['name'])) {
            $uploadDir = 'uploads/graphics/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['graphic_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['graphic_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        // Handle thumbnail upload (optional)
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploadDir = 'uploads/graphics/thumbnails/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
                $thumbnailUrl = $targetFile;
            }
        }
        
        if (!empty($graphicId)) {
            // Update existing graphic design
            $stmt = $pdo->prepare("
                UPDATE graphic_designs SET
                title = ?, description = ?, image_url = ?, 
                thumbnail_url = ?, category = ?, design_type = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['design_type'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $graphicId
            ]);
            
            $message = "Graphic design updated successfully!";
        } else {
            // Insert new graphic design
            $stmt = $pdo->prepare("
                INSERT INTO graphic_designs 
                (title, description, image_url, thumbnail_url, category, design_type, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['design_type'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Graphic design added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh graphic designs data
        $stmt = $pdo->query("SELECT * FROM graphic_designs ORDER BY design_type, category, display_order, created_at DESC");
        $graphicDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $graphicCategories = array_unique(array_column($graphicDesigns, 'category'));
        $designTypes = array_unique(array_column($graphicDesigns, 'design_type'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE GRAPHIC DESIGN DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_graphic'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM graphic_designs WHERE id = ?");
        $stmt->execute([$_POST['graphic_id']]);
        
        $message = "Graphic design deleted successfully!";
        $messageType = "success";
        
        // Refresh graphic designs data
        $stmt = $pdo->query("SELECT * FROM graphic_designs ORDER BY design_type, category, display_order, created_at DESC");
        $graphicDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $graphicCategories = array_unique(array_column($graphicDesigns, 'category'));
        $designTypes = array_unique(array_column($graphicDesigns, 'design_type'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE GRAPHIC DESIGN EDIT
=========================== */

$editingGraphic = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_graphic'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM graphic_designs WHERE id = ?");
        $stmt->execute([$_POST['graphic_id']]);
        $editingGraphic = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH UI/UX DESIGNS
=========================== */

$uiuxDesigns = [];
$uiuxCategories = [];
$uiuxTypes = [];

try {
    $stmt = $pdo->query("SELECT * FROM uiux_designs ORDER BY design_type, category, display_order, created_at DESC");
    $uiuxDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract unique categories and types
    $uiuxCategories = array_unique(array_column($uiuxDesigns, 'category'));
    $uiuxTypes = array_unique(array_column($uiuxDesigns, 'design_type'));
} catch (PDOException $e) {
    // silent
}

/* ===========================
   HANDLE UI/UX FORM SUBMISSION
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_uiux'])) {
    try {
        $uiuxId = $_POST['uiux_id'] ?? '';
        $imageUrl = $_POST['current_image'] ?? '';
        $thumbnailUrl = $_POST['current_thumbnail'] ?? '';
        
        // Handle main image upload
        if (!empty($_FILES['uiux_image']['name'])) {
            $uploadDir = 'uploads/uiux/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['uiux_image']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['uiux_image']['tmp_name'], $targetFile)) {
                $imageUrl = $targetFile;
            }
        }
        
        // Handle thumbnail upload (optional)
        if (!empty($_FILES['thumbnail']['name'])) {
            $uploadDir = 'uploads/uiux/thumbnails/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['thumbnail']['name']);
            $targetFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $targetFile)) {
                $thumbnailUrl = $targetFile;
            }
        }
        
        if (!empty($uiuxId)) {
            // Update existing UI/UX design
            $stmt = $pdo->prepare("
                UPDATE uiux_designs SET
                title = ?, description = ?, prototype_url = ?, image_url = ?, 
                thumbnail_url = ?, category = ?, design_type = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['prototype_url'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['design_type'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $uiuxId
            ]);
            
            $message = "UI/UX design updated successfully!";
        } else {
            // Insert new UI/UX design
            $stmt = $pdo->prepare("
                INSERT INTO uiux_designs 
                (title, description, prototype_url, image_url, thumbnail_url, category, design_type, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['prototype_url'],
                $imageUrl,
                $thumbnailUrl,
                $_POST['category'],
                $_POST['design_type'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "UI/UX design added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh UI/UX designs data
        $stmt = $pdo->query("SELECT * FROM uiux_designs ORDER BY design_type, category, display_order, created_at DESC");
        $uiuxDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uiuxCategories = array_unique(array_column($uiuxDesigns, 'category'));
        $uiuxTypes = array_unique(array_column($uiuxDesigns, 'design_type'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE UI/UX DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_uiux'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM uiux_designs WHERE id = ?");
        $stmt->execute([$_POST['uiux_id']]);
        
        $message = "UI/UX design deleted successfully!";
        $messageType = "success";
        
        // Refresh UI/UX designs data
        $stmt = $pdo->query("SELECT * FROM uiux_designs ORDER BY design_type, category, display_order, created_at DESC");
        $uiuxDesigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uiuxCategories = array_unique(array_column($uiuxDesigns, 'category'));
        $uiuxTypes = array_unique(array_column($uiuxDesigns, 'design_type'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE UI/UX EDIT
=========================== */

$editingUiux = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_uiux'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM uiux_designs WHERE id = ?");
        $stmt->execute([$_POST['uiux_id']]);
        $editingUiux = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}
/* ===========================
   FETCH ENQUIRIES
=========================== */

$enquiries = [];
$enquiriesStats = [
    'total' => 0,
    'pending' => 0,
    'reviewed' => 0,
    'contacted' => 0,
    'archived' => 0
];

try {
    // Get statistics
    $statsQuery = "SELECT 
        COUNT(*) as total,
        SUM(status = 'pending') as pending,
        SUM(status = 'reviewed') as reviewed,
        SUM(status = 'contacted') as contacted,
        SUM(status = 'archived') as archived
    FROM enquiries";
    
    $statsStmt = $pdo->query($statsQuery);
    $enquiriesStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get all enquiries with optional filters
    $whereClause = "";
    $params = [];
    
    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $whereClause = " WHERE status = ?";
        $params[] = $_GET['status'];
    }
    
    if (isset($_GET['service']) && $_GET['service'] !== 'all') {
        $whereClause = $whereClause ? $whereClause . " AND service_type = ?" : " WHERE service_type = ?";
        $params[] = $_GET['service'];
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = "%" . $_GET['search'] . "%";
        if ($whereClause) {
            $whereClause .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR service_type LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        } else {
            $whereClause = " WHERE (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR service_type LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
    }
    
    $query = "SELECT * FROM enquiries $whereClause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $enquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique service types for filter
    $serviceStmt = $pdo->query("SELECT DISTINCT service_type FROM enquiries WHERE service_type IS NOT NULL AND service_type != ''");
    $serviceTypes = $serviceStmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $message = "Error fetching enquiries: " . $e->getMessage();
    $messageType = "error";
}

/* ===========================
   HANDLE ENQUIRY STATUS UPDATE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_enquiry_status'])) {
    try {
        $enquiryId = $_POST['enquiry_id'];
        $newStatus = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE enquiries SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $enquiryId]);
        
        $message = "Enquiry status updated successfully!";
        $messageType = "success";
        
        // Refresh data
        header("Location: " . $_SERVER['PHP_SELF'] . "?section=enquiries");
        exit();
        
    } catch (PDOException $e) {
        $message = "Error updating status: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   HANDLE ENQUIRY DELETE
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_enquiry'])) {
    try {
        $enquiryId = $_POST['enquiry_id'];
        
        // Check if enquiry has uploaded file and delete it
        $stmt = $pdo->prepare("SELECT file_path FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
        $enquiry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($enquiry && !empty($enquiry['file_path']) && file_exists($enquiry['file_path'])) {
            unlink($enquiry['file_path']);
        }
        
        // Delete enquiry from database
        $stmt = $pdo->prepare("DELETE FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
        
        $message = "Enquiry deleted successfully!";
        $messageType = "success";
        
        // Refresh data
        header("Location: " . $_SERVER['PHP_SELF'] . "?section=enquiries");
        exit();
        
    } catch (PDOException $e) {
        $message = "Error deleting enquiry: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   EXPORT ENQUIRIES TO CSV
=========================== */

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export_enquiries'])) {
    try {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=enquiries_' . date('Y-m-d_H-i-s') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Column headers
        fputcsv($output, [
            'ID', 'Full Name', 'Email', 'Phone', 'Company', 'Location',
            'Service Type', 'Other Service', 'Project Type', 'Project Description',
            'Design Style', 'File Path', 'Deadline', 'Budget Range',
            'Contact Preference', 'Best Time', 'Hear About', 'Other Source',
            'Additional Notes', 'Status', 'Created At'
        ]);
        
        // Get all enquiries for export
        $exportStmt = $pdo->query("SELECT * FROM enquiries ORDER BY created_at DESC");
        while ($enquiry = $exportStmt->fetch(PDO::FETCH_ASSOC)) {
            // Clean up data for CSV
            $row = [
                $enquiry['id'],
                $enquiry['full_name'],
                $enquiry['email'],
                $enquiry['phone'],
                $enquiry['company'] ?? '',
                $enquiry['location'] ?? '',
                $enquiry['service_type'],
                $enquiry['other_service'] ?? '',
                $enquiry['project_type'],
                str_replace(["\r\n", "\r", "\n"], ' ', $enquiry['project_description']),
                $enquiry['design_style'] ?? '',
                $enquiry['file_path'] ?? '',
                $enquiry['deadline'] ?? '',
                $enquiry['budget_range'] ?? '',
                $enquiry['contact_preference'],
                $enquiry['best_time'] ?? '',
                $enquiry['hear_about'] ?? '',
                $enquiry['other_source'] ?? '',
                str_replace(["\r\n", "\r", "\n"], ' ', $enquiry['additional_notes'] ?? ''),
                $enquiry['status'],
                $enquiry['created_at']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit();
        
    } catch (PDOException $e) {
        $message = "Error exporting enquiries: " . $e->getMessage();
        $messageType = "error";
    }
}

/* ===========================
   VIEW ENQUIRY DETAILS
=========================== */

$viewingEnquiry = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['view_enquiry'])) {
    try {
        $enquiryId = $_GET['view_enquiry'];
        $stmt = $pdo->prepare("SELECT * FROM enquiries WHERE id = ?");
        $stmt->execute([$enquiryId]);
        $viewingEnquiry = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error loading enquiry details: " . $e->getMessage();
        $messageType = "error";
    }
}
/* ===========================
   ADMIN LINKS & DOCS SECTION
=========================== */

$adminLinks = [];
$linkCategories = [];
$editingLink = null;

// Fetch all admin links
try {
    $stmt = $pdo->query("SELECT * FROM admin_links ORDER BY category, display_order, title");
    $adminLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique categories
    $linkCategories = array_unique(array_column($adminLinks, 'category'));
} catch (PDOException $e) {
    // silent
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_admin_link'])) {
    try {
        $linkId = $_POST['link_id'] ?? '';
        
        if (!empty($linkId)) {
            // Update existing link
            $stmt = $pdo->prepare("
                UPDATE admin_links SET
                title = ?, url = ?, description = ?, 
                category = ?, icon = ?, display_order = ?, is_active = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['url'],
                $_POST['description'],
                $_POST['category'],
                $_POST['icon'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1,
                $linkId
            ]);
            
            $message = "Link updated successfully!";
        } else {
            // Insert new link
            $stmt = $pdo->prepare("
                INSERT INTO admin_links 
                (title, url, description, category, icon, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $_POST['title'],
                $_POST['url'],
                $_POST['description'],
                $_POST['category'],
                $_POST['icon'],
                $_POST['display_order'] ?? 0,
                $_POST['is_active'] ?? 1
            ]);
            
            $message = "Link added successfully!";
        }
        
        $messageType = "success";
        
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM admin_links ORDER BY category, display_order, title");
        $adminLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $linkCategories = array_unique(array_column($adminLinks, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_admin_link'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM admin_links WHERE id = ?");
        $stmt->execute([$_POST['link_id']]);
        
        $message = "Link deleted successfully!";
        $messageType = "success";
        
        // Refresh data
        $stmt = $pdo->query("SELECT * FROM admin_links ORDER BY category, display_order, title");
        $adminLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $linkCategories = array_unique(array_column($adminLinks, 'category'));
        
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin_link'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_links WHERE id = ?");
        $stmt->execute([$_POST['link_id']]);
        $editingLink = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // silent
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <title>Visual Vibe Admin Panel</title>
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
        --success: #4cc9f0;
        --warning: #f8961e;
        --danger: #f94144;
        --info: #4895ef;
        --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --shadow: 0 4px 12px rgba(0,0,0,0.15);
        --shadow-hover: 0 8px 24px rgba(0,0,0,0.25);
        --border: 1px solid rgba(255,255,255,0.1);
        --border-radius: 12px;
        --sidebar-width: 250px;
        --sidebar-collapsed: 70px;
    }
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Montserrat', sans-serif;
        -webkit-tap-highlight-color: transparent;
    }
    
    body {
        background-color: var(--dark);
        color: var(--text);
        line-height: 1.6;
        overflow-x: hidden;
        overflow-y: auto;
        font-size: 14px;
    }
    
    /* Admin Layout */
    .admin-container {
        display: flex;
        min-height: 100vh;
        position: relative;
    }
    
    /* Sidebar */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--dark-secondary);
        border-right: var(--border);
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        z-index: 1000;
        transition: var(--transition);
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        -webkit-overflow-scrolling: touch;
    }
    
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }
    
    .sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    
    .sidebar-header {
        padding: 1rem;
        border-bottom: var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 70px;
    }
    
    .admin-logo {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }
    
    .admin-logo i {
        color: var(--primary);
        font-size: 1.5rem;
        min-width: 24px;
    }
    
    .logo-text {
        transition: var(--transition);
        overflow: hidden;
    }
    
    .sidebar-nav {
        padding: 1rem 0;
    }
    
    .nav-section {
        margin-bottom: 1rem;
    }
    
    .nav-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-secondary);
        padding: 0 1rem;
        margin-bottom: 0.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .nav-links {
        list-style: none;
    }
    
    .nav-item {
        margin-bottom: 0.25rem;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0.8rem 1rem;
        color: var(--text-secondary);
        text-decoration: none;
        transition: var(--transition);
        border-left: 3px solid transparent;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .nav-link:hover,
    .nav-link.active {
        background: rgba(67, 97, 238, 0.1);
        color: var(--text);
        border-left-color: var(--primary);
    }
    
    .nav-link i {
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
        min-width: 24px;
    }
    
    .badge {
        background: var(--accent);
        color: white;
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: auto;
        min-width: 20px;
        text-align: center;
    }
    
    /* Main Content */
    .main-content {
        flex: 1;
        margin-left: var(--sidebar-width);
        transition: var(--transition);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    
    /* Top Bar */
    .top-bar {
        background: var(--dark-secondary);
        border-bottom: var(--border);
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 999;
        backdrop-filter: blur(10px);
        min-height: 70px;
    }
    
    .menu-toggle {
        display: none;
        background: none;
        border: none;
        color: var(--text);
        font-size: 1.3rem;
        cursor: pointer;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }
    
    .menu-toggle:hover {
        background: rgba(255,255,255,0.1);
    }
    
    .page-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
    }
    
    .user-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .notification-icon {
        position: relative;
        color: var(--text-secondary);
        font-size: 1.2rem;
        cursor: pointer;
        transition: var(--transition);
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    .notification-icon:hover {
        color: var(--text);
        background: rgba(255,255,255,0.05);
    }
    
    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--accent);
        color: white;
        font-size: 0.6rem;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .user-info {
        display: flex;
        flex-direction: column;
    }
    
    .user-name {
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .user-role {
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    
    /* Content Area */
    .content-area {
        padding: 1.5rem;
        flex: 1;
        overflow-x: hidden;
    }
    
    .content-section {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .content-section.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Dashboard Cards */
    .dashboard-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.25rem;
        border: var(--border);
        transition: var(--transition);
        backdrop-filter: blur(5px);
    }
    
    .card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-hover);
        background: var(--card-hover);
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    
    .card-title {
        font-size: 0.8rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .card-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    
    .card-icon.primary {
        background: rgba(67, 97, 238, 0.2);
        color: var(--primary);
    }
    
    .card-icon.success {
        background: rgba(76, 201, 240, 0.2);
        color: var(--success);
    }
    
    .card-icon.warning {
        background: rgba(248, 150, 30, 0.2);
        color: var(--warning);
    }
    
    .card-icon.danger {
        background: rgba(249, 65, 68, 0.2);
        color: var(--danger);
    }
    
    .card-content {
        margin-top: 0.75rem;
    }
    
    .card-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .card-trend {
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .trend-up {
        color: var(--success);
    }
    
    .trend-down {
        color: var(--danger);
    }
    
    /* Tables */
    .table-container {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        border: var(--border);
        overflow: hidden;
        margin-bottom: 1.5rem;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-container::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    
    .table-header {
        padding: 1.25rem;
        border-bottom: var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .table-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text);
    }
    
    .table-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .btn {
        padding: 0.6rem 1rem;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        user-select: none;
    }
    
    .btn:active {
        transform: scale(0.98);
    }
    
    .btn-primary {
        background: var(--primary);
        color: white;
    }
    
    .btn-primary:hover {
        background: #3a56d4;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background: var(--card-bg);
        color: var(--text);
        border: var(--border);
    }
    
    .btn-secondary:hover {
        background: var(--card-hover);
        transform: translateY(-2px);
    }
    
    .btn-success {
        background: var(--success);
        color: white;
    }
    
    .btn-danger {
        background: var(--danger);
        color: white;
    }
    
    .btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
    }
    
    .btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    
    th {
        background: rgba(67, 97, 238, 0.1);
        padding: 0.9rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--text-secondary);
        border-bottom: var(--border);
        white-space: nowrap;
    }
    
    td {
        padding: 0.9rem;
        border-bottom: var(--border);
        font-size: 0.85rem;
    }
    
    tr:hover {
        background: var(--card-hover);
    }
    
    .status {
        padding: 0.3rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-pending {
        background: rgba(248, 150, 30, 0.2);
        color: var(--warning);
    }
    
    .status-approved {
        background: rgba(76, 201, 240, 0.2);
        color: var(--success);
    }
    
    .status-rejected {
        background: rgba(249, 65, 68, 0.2);
        color: var(--danger);
    }
    
    /* Forms */
    .form-section {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        border: var(--border);
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1.25rem;
    }
    
    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text);
        font-size: 0.9rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 0.9rem;
        transition: var(--transition);
        appearance: none;
        -webkit-appearance: none;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
        background: rgba(255, 255, 255, 0.08);
    }
    
    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
    
    select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 12px;
        padding-right: 2.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
    
    /* File Upload - UPDATED */
    .file-upload {
        position: relative;
        overflow: hidden;
        border: 2px dashed rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: var(--transition);
        min-height: 200px;
        max-width: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    
    /* Make it square */
    .file-upload::before {
        content: '';
        display: block;
        padding-top: 100%; /* This creates a 1:1 aspect ratio */
    }
    
    .file-upload-content {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    
    /* Ensure the content fits within the square */
    .file-upload i {
        font-size: 2rem;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
    }
    
    .file-upload .upload-text {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        word-break: break-word;
        max-width: 100%;
    }
    
    .file-upload .file-name {
        font-size: 0.8rem;
        color: var(--primary);
        margin-top: 0.5rem;
        word-break: break-all;
        max-width: 100%;
        padding: 0 0.5rem;
    }
    
    /* For when an image is selected/preview */
    .file-upload.has-image {
        padding: 0;
        background: rgba(255, 255, 255, 0.05);
    }
    
    .file-upload.has-image .file-upload-content {
        padding: 0;
    }
    
    .file-upload-preview {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
    }
    
    /* Fix for the form group containing file upload */
    .form-group:has(.file-upload) {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    
    .file-upload:hover {
        border-color: var(--primary);
        background: rgba(67, 97, 238, 0.05);
    }
    
    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
        padding: 1rem;
    }
    
    .modal.active {
        display: flex;
    }
    
    .modal-content {
        background: var(--dark-secondary);
        border-radius: var(--border-radius);
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        border: var(--border);
        box-shadow: var(--shadow-hover);
        display: flex;
        flex-direction: column;
    }
    
    .modal-header {
        padding: 1.25rem;
        border-bottom: var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    
    .modal-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text);
    }
    
    .modal-close {
        background: none;
        border: none;
        color: var(--text-secondary);
        font-size: 1.5rem;
        cursor: pointer;
        transition: var(--transition);
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    
    .modal-close:hover {
        color: var(--text);
        background: rgba(255,255,255,0.1);
    }
    
    .modal-body {
        padding: 1.25rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        flex: 1;
    }
    
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .modal-body::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
    }
    
    .modal-footer {
        padding: 1.25rem;
        border-top: var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    
    /* Alert */
    .alert {
        margin: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .alert-success {
        background: rgba(76, 201, 240, 0.2);
        color: var(--success);
        border-left: 4px solid var(--success);
    }
    
    .alert-error {
        background: rgba(249, 65, 68, 0.2);
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }
    
    /* Grid for portfolio items */
    .row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        margin: 0 -0.5rem;
    }
    
    .col-md-3, .col-sm-6 {
        padding: 0.5rem;
    }
    
    /* Image preview */
    img {
        max-width: 100%;
        height: auto;
    }
    
    /* Mobile Overlay */
    .mobile-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        backdrop-filter: blur(2px);
    }
    
    .mobile-overlay.active {
        display: block;
    }
    
    /* Media Queries */
    @media (max-width: 1200px) {
        .row {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
        }
        
        .sidebar.active {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .mobile-overlay.active {
            display: block;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .user-info {
            display: none;
        }
        
        .table-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .table-actions {
            width: 100%;
            justify-content: flex-start;
        }
        
        /* File upload responsive for tablet */
        .file-upload {
            max-width: 250px;
            min-height: 250px;
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-cards {
            grid-template-columns: 1fr;
        }
        
        .row {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
        
        .content-area {
            padding: 1rem;
        }
        
        .modal-content {
            max-height: 85vh;
            margin: 0.5rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .form-section {
            padding: 1.25rem;
        }
        
        .card {
            padding: 1rem;
        }
        
        /* File upload responsive for mobile */
        .file-upload {
            max-width: 250px;
            min-height: 250px;
        }
        
        .file-upload i {
            font-size: 1.75rem;
        }
        
        .file-upload .upload-text {
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 576px) {
        .top-bar {
            padding: 0.75rem;
        }
        
        .page-title {
            font-size: 1.1rem;
        }
        
        .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }
        
        .btn-icon {
            width: 32px;
            height: 32px;
        }
        
        .modal-footer {
            flex-direction: column;
        }
        
        .modal-footer .btn {
            width: 100%;
            justify-content: center;
        }
        
        .row {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.75rem;
        }
        
        th, td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* File upload responsive for small screens */
        .file-upload {
            max-width: 200px;
            min-height: 200px;
            padding: 1rem;
        }
        
        .file-upload i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .file-upload .upload-text {
            font-size: 0.8rem;
        }
        
        /* Ensure it doesn't overflow the modal or form section */
        .modal-body .file-upload,
        .form-section .file-upload {
            max-width: 200px;
        }
    }
    
    @media (max-width: 480px) {
        .sidebar {
            width: 100%;
        }
        
        .row {
            grid-template-columns: 1fr;
        }
        
        .dashboard-cards {
            gap: 0.75rem;
        }
        
        .card-value {
            font-size: 1.5rem;
        }
        
        .table-container {
            border-radius: 8px;
        }
        
        .table-header {
            padding: 1rem;
        }
        
        /* File upload responsive for extra small screens */
        .file-upload {
            max-width: 180px;
            min-height: 180px;
        }
    }
    
    
    /* Fix for long filenames in mobile */
    @media (max-width: 400px) {
        .file-upload {
            max-width: 160px;
            min-height: 160px;
        }
        
        .file-upload .file-name {
            font-size: 0.7rem;
            max-height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            /* -webkit-line-clamp: 2; */
            -webkit-box-orient: vertical;
        }
    }
    
    /* Print styles */
    @media print {
        .sidebar,
        .top-bar,
        .btn,
        .modal-close,
        .notification-icon,
        .user-menu {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .content-area {
            padding: 0;
        }
        
        .modal.active {
            display: block;
            position: static;
            background: white;
        }
        
        .modal-content {
            box-shadow: none;
            border: 1px solid #ddd;
            max-height: none;
        }
        
        .file-upload {
            border: 1px solid #ddd !important;
            color: #000 !important;
        }
    }
    
    /* Accessibility */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
    
    /* Focus styles for keyboard navigation */
    .nav-link:focus,
    .btn:focus,
    .form-control:focus,
    .modal-close:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }
    
    /* Loading skeleton */
    .skeleton {
        background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    /* ================================
   MODAL SCREEN OVERFLOW FIX
================================ */

.modal {
    padding: 15px;
}

.modal-content {
    max-height: calc(100vh - 30px);
    width: 100%;
    max-width: 620px;
    display: flex;
    flex-direction: column;
}

/* Header stays fixed */
.modal-header {
    flex-shrink: 0;
}

/* Footer stays fixed */
.modal-footer {
    flex-shrink: 0;
}

/* BODY SCROLL ONLY */
.modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: calc(100vh - 220px);
    padding-right: 8px;
}

/* Prevent form elements from pushing width */
.modal-body * {
    max-width: 100%;
    box-sizing: border-box;
}

/* Fix textarea stretching modal */
.modal textarea {
    max-width: 100%;
}

/* Mobile optimization */
@media (max-width: 768px) {

    .modal-content {
        max-height: calc(100vh - 20px);
        margin: 10px;
    }

    .modal-body {
        max-height: calc(100vh - 200px);
        padding: 1rem;
    }
}

/* Small screen safety */
@media (max-width: 480px) {

    .modal-body {
        max-height: calc(100vh - 180px);
    }
}
.choices {
    background: #2b2f3e;
    border-radius: 8px;
}

.choices__inner {
    background: #2b2f3e;
    border: 1px solid rgba(255,255,255,0.15);
    color: white;
}

.choices__list--dropdown {
    background: #252a38;
    border: 1px solid rgba(255,255,255,0.15);
}

.choices__item--selectable {
    color: white;
}

.choices__item--selectable.is-highlighted {
    background: #4361ee;
}
/* ===========================
   ENQUIRIES SECTION STYLES
=========================== */

/* Status Badges for Enquiries */
.status-pending {
    background: rgba(248, 150, 30, 0.2);
    color: var(--warning);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.status-reviewed {
    background: rgba(72, 149, 239, 0.2);
    color: var(--info);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.status-contacted {
    background: rgba(76, 201, 240, 0.2);
    color: var(--success);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.status-archived {
    background: rgba(108, 117, 125, 0.2);
    color: var(--text-secondary);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Project Type Badges */
.badge-new {
    background: rgba(67, 97, 238, 0.2);
    color: var(--primary);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-redesign {
    background: rgba(247, 37, 133, 0.2);
    color: var(--accent);
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

/* Contact Preferences */
.contact-badges {
    display: flex;
    gap: 0.25rem;
    flex-wrap: wrap;
    margin-top: 0.25rem;
}

.contact-badge {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text);
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    display: inline-block;
}

/* Table cell truncation */
.table-cell-truncate {
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

/* Quick actions */
.quick-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: var(--border);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.85rem;
    text-decoration: none;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.action-btn.view {
    color: var(--info);
    border-color: rgba(72, 149, 239, 0.3);
}

.action-btn.view:hover {
    background: var(--info);
    color: white;
    border-color: var(--info);
}

.action-btn.email {
    color: var(--success);
    border-color: rgba(76, 201, 240, 0.3);
}

.action-btn.email:hover {
    background: var(--success);
    color: white;
    border-color: var(--success);
}

.action-btn.delete {
    color: var(--danger);
    border-color: rgba(249, 65, 68, 0.3);
}

.action-btn.delete:hover {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

.action-btn[href^="tel:"] {
    color: var(--primary);
    border-color: rgba(67, 97, 238, 0.3);
}

.action-btn[href^="tel:"]:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* Status dropdown in table */
.status-select {
    background: transparent;
    border: var(--border);
    border-radius: 20px;
    padding: 0.3rem 1.5rem 0.3rem 0.8rem;
    color: var(--text);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 10px;
}

.status-select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

.status-select option {
    background: var(--dark-secondary);
    color: var(--text);
    padding: 0.5rem;
}

/* Enquiry Details Modal */
.enquiry-details-modal .modal-content {
    max-width: 900px;
    max-height: 85vh;
}

.detail-group {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: var(--border);
}

.detail-group:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.85rem;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-label:before {
    content: '';
    display: inline-block;
    width: 4px;
    height: 16px;
    background: var(--primary);
    border-radius: 2px;
}

.detail-value {
    color: var(--text);
    font-size: 0.95rem;
    line-height: 1.6;
}

.detail-value.multiline {
    white-space: pre-wrap;
    background: rgba(255, 255, 255, 0.05);
    padding: 1rem;
    border-radius: 8px;
    border: var(--border);
    font-size: 0.9rem;
    line-height: 1.7;
    max-height: 200px;
    overflow-y: auto;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.25rem;
    margin-top: 0.5rem;
}

.detail-item {
    margin-bottom: 0.75rem;
}

.detail-item .detail-label {
    font-size: 0.8rem;
    margin-bottom: 0.4rem;
    color: var(--text-secondary);
    opacity: 0.9;
    text-transform: none;
    letter-spacing: 0;
}

.detail-item .detail-label:before {
    display: none;
}

/* Table Filters */
.table-actions form {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.table-actions .form-group {
    margin: 0;
}

.table-actions .form-control {
    min-width: 120px;
    height: 38px;
}

.table-actions .btn {
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Stats cards specific */
.card-icon.secondary {
    background: rgba(108, 117, 125, 0.2);
    color: var(--text-secondary);
}

/* File download button */
.btn-download {
    background: rgba(67, 97, 238, 0.2);
    color: var(--primary);
    border: 1px solid rgba(67, 97, 238, 0.3);
}

.btn-download:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

/* No data state */
.no-data {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.no-data h3 {
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.no-data p {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Table footer */
.table-footer {
    padding: 1rem;
    border-top: var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255, 255, 255, 0.02);
}

.table-info {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .detail-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 992px) {
    .table-actions form {
        width: 100%;
        justify-content: flex-start;
    }
    
    .table-actions .form-control {
        min-width: 100px;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .quick-actions {
        gap: 0.25rem;
    }
    
    .action-btn {
        width: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
    
    .status-select {
        padding: 0.25rem 1.2rem 0.25rem 0.6rem;
        font-size: 0.75rem;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .detail-group {
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    
    .detail-value.multiline {
        max-height: 150px;
        padding: 0.75rem;
    }
    
    .enquiry-details-modal .modal-content {
        max-height: 90vh;
        margin: 0.5rem;
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-footer .btn {
        width: 100%;
        justify-content: center;
    }
    
    .table-footer {
        flex-direction: column;
        gap: 0.75rem;
        text-align: center;
    }
}

@media (max-width: 576px) {
    .dashboard-cards {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .card-value {
        font-size: 1.5rem;
    }
    
    .table-actions form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .table-actions .form-control,
    .table-actions .btn {
        width: 100%;
        min-width: unset;
    }
    
    th, td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .table-cell-truncate {
        max-width: 120px;
    }
    
    .contact-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
    }
}

@media (max-width: 480px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .action-btn {
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }
    
    .no-data {
        padding: 2rem 1rem;
    }
    
    .no-data i {
        font-size: 2.5rem;
    }
    
    .detail-label {
        font-size: 0.8rem;
    }
    
    .detail-value {
        font-size: 0.9rem;
    }
}

/* Loading states */
.skeleton-loading {
    background: linear-gradient(90deg, 
        rgba(255,255,255,0.05) 25%, 
        rgba(255,255,255,0.1) 50%, 
        rgba(255,255,255,0.05) 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 4px;
    height: 20px;
    margin: 0.5rem 0;
}

.skeleton-loading.short {
    width: 60%;
}

.skeleton-loading.medium {
    width: 80%;
}

.skeleton-loading.long {
    width: 100%;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Print styles for enquiries */
@media print {
    .table-actions,
    .action-btn,
    .status-select,
    .quick-actions,
    .modal-footer {
        display: none !important;
    }
    
    .detail-group {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .detail-value.multiline {
        max-height: none;
        overflow: visible;
    }
}

/* Focus styles for accessibility */
.action-btn:focus,
.status-select:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}
/* =====================================
   HORIZONTAL SCROLL — MAIN CONTENT ONLY
===================================== */

/* Prevent full page horizontal scroll */
body {
    overflow-x: hidden;
}

/* Main enquiries section */
#enquiries {
    margin-left: 260px;               /* sidebar width */
    width: calc(100% - 260px);
    height: calc(100vh - 70px);
    overflow-x: auto;                 /* ✅ horizontal scrollbar */
    overflow-y: auto;                 /* vertical scroll */
    padding: 25px;
    box-sizing: border-box;
}

/* Table wrapper */
#enquiries .table-container {
    min-width: 1200px;                /* forces horizontal scroll */
}

/* Smooth scrollbar (Chrome / Edge) */
#enquiries::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}

#enquiries::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
}

#enquiries::-webkit-scrollbar-thumb {
    background: rgba(67,97,238,0.6);
    border-radius: 10px;
}

#enquiries::-webkit-scrollbar-thumb:hover {
    background: rgba(67,97,238,0.9);
}

/* Firefox */
#enquiries {
    scrollbar-width: thin;
    scrollbar-color: rgba(67,97,238,0.7) transparent;
}
/* ======================================
   FIX ENQUIRIES POSITION AT TOP
====================================== */

#enquiries.content-section {
    position: fixed;
    top: 70px;                 /* height of admin header */
    /* left: 260px;             */
    right: 0;
    bottom: 0;

    padding: 25px;

    overflow-x: auto;
    overflow-y: auto;

    background: #0f1320;
    box-sizing: border-box;
}

/* prevent body scrolling */
body {
    overflow: hidden;
}

/* keep table wide */
#enquiries .table-container {
    min-width: 1200px;
}
/* ===========================
   ADMIN LINKS SECTION STYLES
=========================== */

/* Links Grid */
.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

/* Link Card */
.link-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    border: var(--border);
    padding: 1.25rem;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.link-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
    background: var(--card-hover);
}

.link-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.link-icon {
    width: 50px;
    height: 50px;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--primary);
}

.link-actions {
    display: flex;
    gap: 0.5rem;
}

.link-actions .action-btn {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: var(--border);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    font-size: 0.9rem;
}

.link-actions .action-btn.edit {
    color: var(--info);
    border-color: rgba(72, 149, 239, 0.3);
}

.link-actions .action-btn.edit:hover {
    background: var(--info);
    color: white;
    border-color: var(--info);
}

.link-actions .action-btn.delete {
    color: var(--danger);
    border-color: rgba(249, 65, 68, 0.3);
}

.link-actions .action-btn.delete:hover {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

.link-card-body {
    flex: 1;
    margin-bottom: 1rem;
}

.link-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.badge-inactive {
    background: rgba(249, 65, 68, 0.2);
    color: var(--danger);
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.link-description {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 1rem;
    display: -webkit-box;
    /* -webkit-line-clamp: 3; */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.link-url .btn-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    background: rgba(67, 97, 238, 0.1);
    border-radius: 6px;
    transition: var(--transition);
}

.link-url .btn-link:hover {
    background: rgba(67, 97, 238, 0.2);
    text-decoration: none;
}

.link-card-footer {
    border-top: var(--border);
    padding-top: 0.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.link-meta {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    font-size: 0.8rem;
}

.link-category, .link-order {
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.link-category i, .link-order i {
    font-size: 0.7rem;
}

.link-id {
    color: var(--text-secondary);
    font-size: 0.75rem;
    font-family: monospace;
    background: rgba(255, 255, 255, 0.05);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

/* Card icon variations */
.card-icon.info {
    background: rgba(72, 149, 239, 0.2);
    color: var(--info);
}

.card-icon.accent {
    background: rgba(247, 37, 133, 0.2);
    color: var(--accent);
}

/* Quick access buttons */
.quick-access {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.quick-access-btn {
    padding: 0.6rem 1rem;
    background: var(--card-bg);
    border: var(--border);
    border-radius: 8px;
    color: var(--text);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.quick-access-btn:hover {
    background: var(--card-hover);
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 1200px) {
    .links-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
}

@media (max-width: 992px) {
    .links-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

@media (max-width: 768px) {
    .links-grid {
        grid-template-columns: 1fr;
    }
    
    .link-card {
        padding: 1rem;
    }
    
    .quick-access {
        justify-content: center;
    }
    
    .quick-access-btn {
        flex: 1;
        min-width: 150px;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .link-card-header {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .link-actions {
        align-self: flex-end;
    }
    
    .link-title {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .link-card-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .link-id {
        align-self: flex-end;
    }
}

/* Filter highlight */
.filtered-out {
    display: none;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.empty-state p {
    font-size: 0.9rem;
    opacity: 0.8;
}

</style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="#" class="admin-logo">
                    <!-- <i class="fas fa-palette"></i>
                    <span class="logo-text">Visual Vibe Admin</span> -->
                    
                </a>
                <img src="images/visual vibe logo.svg" class="img-fluid" style="width:300px; height:100px  !important; width:190px !important;">
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h3 class="nav-title">Main</h3>
                    <ul class="nav-links">
                        <li class="nav-item">
                            <a href="#dashboard" class="nav-link active">
                                <i class="fas fa-tachometer-alt"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="nav-section">
                    <h3 class="nav-title">Content Management</h3>
                    <ul class="nav-links">
                        <li class="nav-item">
                            <a href="#carousel-management" class="nav-link">
                                <i class="fas fa-images"></i>
                                <span>Carousel Slides</span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="#team-management" class="nav-link">
                                <i class="fas fa-users"></i>
                                <span>Team Management</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#portfolio-management" class="nav-link">
                                <i class="fas fa-video"></i>
                                <span>Portfolio Videos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#logo-management" class="nav-link">
                                <i class="fas fa-drafting-compass"></i>
                                <span>Logo Portfolio</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#website-management" class="nav-link">
                                <i class="fas fa-laptop-code"></i>
                                <span>Website Portfolio</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#flyers-management" class="nav-link">
                                <i class="fas fa-scroll"></i>
                                <span>Flyers & Posters</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#graphic-designs-management" class="nav-link">
                                <i class="fas fa-cube"></i>
                                <span>2D & 3D Works</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#uiux-management" class="nav-link">
                                <i class="fas fa-pencil-ruler"></i>
                                <span>UI/UX Designs</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="nav-section">
                    <h3 class="nav-title">Content Management</h3>
                    <ul class="nav-links">
                    <li class="nav-item">
                            <a href="#enquiries" class="nav-link">
                                <i class="fas fa-pencil-ruler"></i>
                                <span>Enquiries Management</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="nav-section">
                    <!-- <h3 class="nav-title">Content Management</h3> -->
                    <ul class="nav-links">
                    <!-- In your sidebar navigation -->
                        <li class="nav-item">
                        <a href="logout.php" class="nav-link logout-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="nav-text">Logout</span>
                        </a>


                        </li>
                    </ul>
                </div>
            </nav>
        </aside>
        <script>
document.querySelectorAll('.logout-link').forEach(link => {
    link.addEventListener('click', function () {
        window.location.href = this.getAttribute('href');
    });
});
</script>


        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title" id="pageTitle">Dashboard</h1>
                <div class="user-menu">
                    <!-- <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count">5</span>
                    </div> -->
                    <div class="user-profile">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <span class="user-name">Admin User</span>
                            <span class="user-role">Administrator</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content Area -->
            <div class="content-area">
                <!-- Dashboard Section -->
                <section id="dashboard" class="content-section active">
                    <div class="dashboard-cards">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Total Slides</h3>
                                <div class="card-icon primary">
                                    <i class="fas fa-images"></i>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-value"><?php echo count($slides); ?></div>
                                <div class="card-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Active</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Team Members</h3>
                                <div class="card-icon success">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-value"><?php echo count($teamMembers); ?></div>
                                <div class="card-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Active</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Portfolio Items</h3>
                                <div class="card-icon warning">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-value"><?php echo count($portfolioVideos) + count($logoPortfolio) + count($websitePortfolio) + count($flyersPosters) + count($graphicDesigns) + count($uiuxDesigns); ?></div>
                                <div class="card-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Total</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Active Content</h3>
                                <div class="card-icon info">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="card-content">
                                <div class="card-value">100%</div>
                                <div class="card-trend trend-up">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Optimized</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 style="margin-bottom: 1rem; color: var(--text);">Quick Stats</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Carousel Slides</label>
                                <div class="form-control" style="background: transparent; border: none; padding: 0.5rem 0;">
                                    <span style="color: var(--primary); font-weight: 600; font-size: 1.2rem;"><?php echo count($slides); ?></span> active slides
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Team Members</label>
                                <div class="form-control" style="background: transparent; border: none; padding: 0.5rem 0;">
                                    <span style="color: var(--success); font-weight: 600; font-size: 1.2rem;"><?php echo count($teamMembers); ?></span> team members
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Portfolio Videos</label>
                                <div class="form-control" style="background: transparent; border: none; padding: 0.5rem 0;">
                                    <span style="color: var(--warning); font-weight: 600; font-size: 1.2rem;"><?php echo count($portfolioVideos); ?></span> videos
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Design Works</label>
                                <div class="form-control" style="background: transparent; border: none; padding: 0.5rem 0;">
                                    <span style="color: var(--accent); font-weight: 600; font-size: 1.2rem;"><?php echo count($graphicDesigns) + count($uiuxDesigns); ?></span> designs
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                
                <!-- Carousel Management Section -->
                <section id="carousel-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Carousel Slides Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openSlideModal()">
                                    <i class="fas fa-plus"></i> Add Slide
                                </button>
                            </div>
                        </div>
                        
                        <?php if ($message && ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['add_slide', 'update_slide', 'delete_slide']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($slides as $slide): ?>
                                <tr>
                                    <td><?php echo $slide['display_order']; ?></td>
                                    <td>
                                        <img src="<?php echo $slide['image_url']; ?>" alt="<?php echo htmlspecialchars($slide['title']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($slide['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($slide['description'], 0, 50)) . '...'; ?></td>
                                    <td>
                                        <span class="status <?php echo $slide['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $slide['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="edit_slide">
                                            <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this slide?');">
                                            <input type="hidden" name="action" value="delete_slide">
                                            <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                
                
                <!-- Team Management Section -->
                <section id="team-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Team Members Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openTeamModal()">
                                    <i class="fas fa-user-plus"></i> Add Team Member
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_team']) || isset($_POST['delete_team_member']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($teamCategories)): ?>
                            <?php foreach ($teamCategories as $category): ?>
                            <div class="team-category-section" style="margin-bottom: 2rem;">
                                <h4 style="color: var(--primary); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: var(--border); font-size: 1rem;"><?php echo htmlspecialchars($category); ?></h4>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Designation</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($teamMembers as $member): ?>
                                            <?php if ($member['category'] === $category): ?>
                                            <tr>
                                                <td><?php echo $member['display_order']; ?></td>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($member['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($member['name']); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: var(--border);">
                                                </td>
                                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['designation']); ?></td>
                                                <td>
                                                    <span class="status <?php echo $member['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                                        <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="edit_team_member" value="1">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this team member?');">
                                                        <input type="hidden" name="delete_team_member" value="1">
                                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p>No team members added yet. Click "Add Team Member" to get started.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Portfolio Videos Section -->
                <section id="portfolio-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Portfolio Videos Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openPortfolioModal()">
                                    <i class="fas fa-video"></i> Add Video
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_portfolio']) || isset($_POST['delete_portfolio']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($portfolioVideos)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Thumbnail</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($portfolioVideos as $video): ?>
                                <tr>
                                    <td><?php echo $video['display_order']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($video['thumbnail_file']); ?>" 
                                             alt="<?php echo htmlspecialchars($video['title']); ?>" 
                                             style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: var(--border);">
                                    </td>
                                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($video['description'] ?? '', 0, 50)) . '...'; ?></td>
                                    <td>
                                        <span class="status <?php echo $video['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $video['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary btn-icon" onclick="previewVideo('<?php echo htmlspecialchars($video['video_file']); ?>', '<?php echo htmlspecialchars($video['title']); ?>')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="edit_portfolio" value="1">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this video?');">
                                            <input type="hidden" name="delete_portfolio" value="1">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No portfolio videos added yet. Click "Add Video" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Logo Portfolio Section -->
                <section id="logo-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Logo Portfolio Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openLogoModal()">
                                    <i class="fas fa-plus"></i> Add Logo
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_logo']) || isset($_POST['delete_logo']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($logoPortfolio)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Logo</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logoPortfolio as $logo): ?>
                                <tr>
                                    <td><?php echo $logo['display_order']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($logo['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($logo['title']); ?>" 
                                             style="width: 80px; height: 80px; object-fit: contain; background: var(--dark-secondary); border-radius: 8px; border: var(--border); padding: 10px;">
                                    </td>
                                    <td><?php echo htmlspecialchars($logo['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($logo['description'] ?? '', 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($logo['category']); ?></td>
                                    <td>
                                        <span class="status <?php echo $logo['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $logo['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary btn-icon" onclick="previewLogo('<?php echo htmlspecialchars($logo['image_url']); ?>', '<?php echo htmlspecialchars($logo['title']); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="edit_logo" value="1">
                                            <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this logo?');">
                                            <input type="hidden" name="delete_logo" value="1">
                                            <input type="hidden" name="logo_id" value="<?php echo $logo['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-drafting-compass" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No logo designs added yet. Click "Add Logo" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Website Portfolio Section -->
                <section id="website-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Website Portfolio Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openWebsiteModal()">
                                    <i class="fas fa-plus"></i> Add Website
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_website']) || isset($_POST['delete_website']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($websitePortfolio)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Preview</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>URL</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($websitePortfolio as $website): ?>
                                <tr>
                                    <td><?php echo $website['display_order']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($website['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($website['title']); ?>" 
                                             style="width: 100px; height: 70px; object-fit: cover; border-radius: 6px; border: var(--border);">
                                    </td>
                                    <td><?php echo htmlspecialchars($website['title']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($website['description'] ?? '', 0, 50)) . '...'; ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($website['website_url']); ?>" 
                                           target="_blank" 
                                           style="color: var(--primary); font-size: 0.8rem;">
                                            View Site
                                        </a>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $website['is_active'] ? 'status-approved' : 'status-pending'; ?>">
                                            <?php echo $website['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($website['website_url']); ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-secondary btn-icon"
                                           title="Visit Website">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="edit_website" value="1">
                                            <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this website?');">
                                            <input type="hidden" name="delete_website" value="1">
                                            <input type="hidden" name="website_id" value="<?php echo $website['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-laptop-code" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No website portfolio added yet. Click "Add Website" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- Flyers & Posters Section -->
                <section id="flyers-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">Flyers & Posters Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openFlyerModal()">
                                    <i class="fas fa-plus"></i> Add Flyer/Poster
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_flyer']) || isset($_POST['delete_flyer']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($flyerCategories)): ?>
                            <?php foreach ($flyerCategories as $category): ?>
                            <div class="flyer-category-section" style="margin-bottom: 2rem;">
                                <h4 style="color: var(--primary); margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: var(--border); font-size: 1rem;">
                                    <?php echo htmlspecialchars($category); ?>
                                </h4>
                                <div class="row">
                                    <?php foreach ($flyersPosters as $flyer): ?>
                                        <?php if ($flyer['category'] === $category): ?>
                                        <div class="col-md-3 col-sm-6">
                                            <div class="card" style="height: 100%; margin: 0;">
                                                <div style="position: relative; overflow: hidden; border-radius: 8px 8px 0 0;">
                                                    <img src="<?php echo htmlspecialchars($flyer['image_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($flyer['title']); ?>" 
                                                         style="width: 100%; height: 180px; object-fit: cover;">
                                                </div>
                                                <div class="card-content" style="padding: 1rem;">
                                                    <h5 style="margin-bottom: 0.5rem; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?php echo htmlspecialchars($flyer['title']); ?>
                                                    </h5>
                                                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-box-orient: vertical; height: 40px;">
                                                        <?php echo htmlspecialchars($flyer['description'] ?? ''); ?>
                                                    </p>
                                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                                        <span class="status <?php echo $flyer['is_active'] ? 'status-approved' : 'status-pending'; ?>" style="font-size: 0.7rem;">
                                                            <?php echo $flyer['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        </span>
                                                        <div>
                                                            <button class="btn btn-sm btn-secondary btn-icon" 
                                                                    onclick="previewFlyer('<?php echo htmlspecialchars($flyer['image_url']); ?>', '<?php echo htmlspecialchars($flyer['title']); ?>')"
                                                                    style="margin-right: 5px;">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="edit_flyer" value="1">
                                                                <input type="hidden" name="flyer_id" value="<?php echo $flyer['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                            </form>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this flyer/poster?');">
                                                                <input type="hidden" name="delete_flyer" value="1">
                                                                <input type="hidden" name="flyer_id" value="<?php echo $flyer['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-scroll" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No flyers or posters added yet. Click "Add Flyer/Poster" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- 2D & 3D Works Section -->
                <section id="graphic-designs-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">2D & 3D Works Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openGraphicModal()">
                                    <i class="fas fa-plus"></i> Add 2D/3D Work
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_graphic']) || isset($_POST['delete_graphic']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($designTypes)): ?>
                            <?php foreach ($designTypes as $designType): ?>
                            <div class="design-type-section" style="margin-bottom: 3rem;">
                                <h3 style="color: var(--primary); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary); font-size: 1.1rem;">
                                    <i class="fas <?php echo $designType === '2D' ? 'fa-square' : 'fa-cube'; ?>"></i>
                                    <?php echo htmlspecialchars($designType); ?> Works
                                </h3>
                                
                                <?php if (!empty($graphicCategories)): ?>
                                    <?php foreach ($graphicCategories as $category): ?>
                                        <?php 
                                        $categoryDesigns = array_filter($graphicDesigns, function($design) use ($designType, $category) {
                                            return $design['design_type'] === $designType && $design['category'] === $category;
                                        });
                                        ?>
                                        
                                        <?php if (!empty($categoryDesigns)): ?>
                                        <div class="category-subsection" style="margin-bottom: 2rem;">
                                            <h4 style="color: var(--accent); margin-bottom: 1rem; font-size: 1rem;">
                                                <?php echo htmlspecialchars($category); ?>
                                            </h4>
                                            <div class="row">
                                                <?php foreach ($categoryDesigns as $graphic): ?>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card" style="height: 100%; margin: 0;">
                                                        <div style="position: relative; overflow: hidden; border-radius: 8px 8px 0 0;">
                                                            <img src="<?php echo htmlspecialchars($graphic['image_url']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($graphic['title']); ?>" 
                                                                 style="width: 100%; height: 180px; object-fit: cover;">
                                                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;">
                                                                <?php echo htmlspecialchars($graphic['design_type']); ?>
                                                            </div>
                                                        </div>
                                                        <div class="card-content" style="padding: 1rem;">
                                                            <h5 style="margin-bottom: 0.5rem; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                <?php echo htmlspecialchars($graphic['title']); ?>
                                                            </h5>
                                                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-box-orient: vertical; height: 40px;">
                                                                <?php echo htmlspecialchars($graphic['description'] ?? ''); ?>
                                                            </p>
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <span class="status <?php echo $graphic['is_active'] ? 'status-approved' : 'status-pending'; ?>" style="font-size: 0.7rem;">
                                                                    <?php echo $graphic['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                                <div>
                                                                    <button class="btn btn-sm btn-secondary btn-icon" 
                                                                            onclick="previewGraphic('<?php echo htmlspecialchars($graphic['image_url']); ?>', '<?php echo htmlspecialchars($graphic['title']); ?>')"
                                                                            style="margin-right: 5px;">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="edit_graphic" value="1">
                                                                        <input type="hidden" name="graphic_id" value="<?php echo $graphic['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this graphic design?');">
                                                                        <input type="hidden" name="delete_graphic" value="1">
                                                                        <input type="hidden" name="graphic_id" value="<?php echo $graphic['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    <i class="fas <?php echo $designType === '2D' ? 'fa-square' : 'fa-cube'; ?>" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                    <p>No <?php echo htmlspecialchars($designType); ?> works added yet.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-cube" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No 2D or 3D works added yet. Click "Add 2D/3D Work" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- UI/UX Designs Section -->
                <section id="uiux-management" class="content-section">
                    <div class="table-container">
                        <div class="table-header">
                            <h3 class="table-title">UI/UX Designs Management</h3>
                            <div class="table-actions">
                                <button class="btn btn-primary" onclick="openUiuxModal()">
                                    <i class="fas fa-plus"></i> Add UI/UX Design
                                </button>
                            </div>
                        </div>
                        
                        <?php if (isset($message) && (isset($_POST['submit_uiux']) || isset($_POST['delete_uiux']))): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($uiuxTypes)): ?>
                            <?php foreach ($uiuxTypes as $type): ?>
                            <div class="uiux-type-section" style="margin-bottom: 3rem;">
                                <h3 style="color: var(--primary); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary); font-size: 1.1rem;">
                                    <i class="fas <?php echo $type === 'Web UI' ? 'fa-desktop' : ($type === 'Mobile UI' ? 'fa-mobile-alt' : 'fa-pencil-ruler'); ?>"></i>
                                    <?php echo htmlspecialchars($type); ?>
                                </h3>
                                
                                <?php if (!empty($uiuxCategories)): ?>
                                    <?php foreach ($uiuxCategories as $category): ?>
                                        <?php 
                                        $categoryDesigns = array_filter($uiuxDesigns, function($design) use ($type, $category) {
                                            return $design['design_type'] === $type && $design['category'] === $category;
                                        });
                                        ?>
                                        
                                        <?php if (!empty($categoryDesigns)): ?>
                                        <div class="category-subsection" style="margin-bottom: 2rem;">
                                            <h4 style="color: var(--accent); margin-bottom: 1rem; font-size: 1rem;">
                                                <?php echo htmlspecialchars($category); ?>
                                            </h4>
                                            <div class="row">
                                                <?php foreach ($categoryDesigns as $design): ?>
                                                <div class="col-md-3 col-sm-6">
                                                    <div class="card" style="height: 100%; margin: 0;">
                                                        <div style="position: relative; overflow: hidden; border-radius: 8px 8px 0 0;">
                                                            <img src="<?php echo htmlspecialchars($design['image_url']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($design['title']); ?>" 
                                                                 style="width: 100%; height: 180px; object-fit: cover;">
                                                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;">
                                                                <?php echo htmlspecialchars($design['design_type']); ?>
                                                            </div>
                                                        </div>
                                                        <div class="card-content" style="padding: 1rem;">
                                                            <h5 style="margin-bottom: 0.5rem; font-size: 0.9rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                <?php echo htmlspecialchars($design['title']); ?>
                                                            </h5>
                                                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.5rem; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-box-orient: vertical; height: 40px;">
                                                                <?php echo htmlspecialchars($design['description'] ?? ''); ?>
                                                            </p>
                                                            <?php if (!empty($design['prototype_url'])): ?>
                                                            <a href="<?php echo htmlspecialchars($design['prototype_url']); ?>" 
                                                               target="_blank" 
                                                               style="font-size: 0.7rem; color: var(--primary); display: block; margin-bottom: 0.5rem;"
                                                               title="View Prototype">
                                                                <i class="fas fa-external-link-alt"></i> View Prototype
                                                            </a>
                                                            <?php endif; ?>
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <span class="status <?php echo $design['is_active'] ? 'status-approved' : 'status-pending'; ?>" style="font-size: 0.7rem;">
                                                                    <?php echo $design['is_active'] ? 'Active' : 'Inactive'; ?>
                                                                </span>
                                                                <div>
                                                                    <button class="btn btn-sm btn-secondary btn-icon" 
                                                                            onclick="previewUiux('<?php echo htmlspecialchars($design['image_url']); ?>', '<?php echo htmlspecialchars($design['title']); ?>')"
                                                                            style="margin-right: 5px;">
                                                                        <i class="fas fa-eye"></i>
                                                                    </button>
                                                                    <form method="POST" style="display: inline;">
                                                                        <input type="hidden" name="edit_uiux" value="1">
                                                                        <input type="hidden" name="uiux_id" value="<?php echo $design['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-secondary btn-icon">
                                                                            <i class="fas fa-edit"></i>
                                                                        </button>
                                                                    </form>
                                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this UI/UX design?');">
                                                                        <input type="hidden" name="delete_uiux" value="1">
                                                                        <input type="hidden" name="uiux_id" value="<?php echo $design['id']; ?>">
                                                                        <button type="submit" class="btn btn-sm btn-danger btn-icon">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                                    <i class="fas <?php echo $type === 'Web UI' ? 'fa-desktop' : ($type === 'Mobile UI' ? 'fa-mobile-alt' : 'fa-pencil-ruler'); ?>" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                                    <p>No <?php echo htmlspecialchars($type); ?> designs added yet.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class="fas fa-pencil-ruler" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No UI/UX designs added yet. Click "Add UI/UX Design" to get started.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>
    
    <!-- All Modals -->
    <!-- Carousel Modal -->
    <div class="modal" id="carouselModal" <?php echo $currentSlide ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $currentSlide ? 'Edit Slide' : 'Add New Slide'; ?></h3>
                <button class="modal-close" onclick="closeSlideModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="carouselForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="<?php echo $currentSlide ? 'update_slide' : 'add_slide'; ?>">
                    <input type="hidden" name="slide_id" value="<?php echo $currentSlide['id'] ?? ''; ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" value="<?php echo $currentSlide['title'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subtitle</label>
                        <input type="text" class="form-control" name="subtitle" value="<?php echo $currentSlide['subtitle'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" required><?php echo $currentSlide['description'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" value="<?php echo $currentSlide['display_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active" required>
                                <option value="1" <?php echo ($currentSlide['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($currentSlide['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Image <?php echo $currentSlide ? '(Leave empty to keep current)' : '*'; ?></label>
                        <input type="file" class="form-control" name="image" accept="image/*" <?php echo !$currentSlide ? 'required' : ''; ?>>
                        
                        <?php if ($currentSlide && $currentSlide['image_url']): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Image:</p>
                            <img src="<?php echo $currentSlide['image_url']; ?>" alt="Current" style="max-width: 200px; border-radius: 8px;">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeSlideModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $currentSlide ? 'Update' : 'Save'; ?> Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Team Modal -->
    <div class="modal" id="teamModal" <?php echo $editingMember ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingMember ? 'Edit Team Member' : 'Add New Team Member'; ?></h3>
                <button class="modal-close" onclick="closeTeamModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="teamForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_team" value="1">
                    <input type="hidden" name="member_id" value="<?php echo $editingMember['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingMember['image_url'] ?? ''); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Name *</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($editingMember['name'] ?? ''); ?>" 
                                   placeholder="Enter team member name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Founders & CEO" <?php echo ($editingMember['category'] ?? '') === 'Founders & CEO' ? 'selected' : ''; ?>>Founders & CEO</option>
                                <option value="Head of Operations" <?php echo ($editingMember['category'] ?? '') === 'Head of Operations' ? 'selected' : ''; ?>>Head of Operations</option>
                                <option value="Research and Development" <?php echo ($editingMember['category'] ?? '') === 'Research and Development' ? 'selected' : ''; ?>>Research and Development</option>
                                <option value="Graphics Team" <?php echo ($editingMember['category'] ?? '') === 'Graphics Team' ? 'selected' : ''; ?>>Graphics Team</option>
                                <option value="Developers" <?php echo ($editingMember['category'] ?? '') === 'Developers' ? 'selected' : ''; ?>>Developers</option>
                                <option value="Marketing Team" <?php echo ($editingMember['category'] ?? '') === 'Marketing Team' ? 'selected' : ''; ?>>Marketing Team</option>
                                <option value="HR Team" <?php echo ($editingMember['category'] ?? '') === 'HR Team' ? 'selected' : ''; ?>>HR Team</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Designation *</label>
                        <input type="text" class="form-control" name="designation" 
                               value="<?php echo htmlspecialchars($editingMember['designation'] ?? ''); ?>" 
                               placeholder="Enter designation (e.g., CEO, Developer, Designer)" required>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Use | for line breaks</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingMember['display_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingMember['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingMember['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Profile Image</label>
                        <input type="file" class="form-control" name="team_image" accept="image/*">
                        
                        <?php if (!empty($editingMember['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($editingMember['image_url']); ?>" 
                                 alt="Profile Image" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeTeamModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingMember ? 'Update' : 'Save'; ?> Team Member
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Portfolio Video Modal -->
    <div class="modal" id="portfolioModal" <?php echo $editingVideo ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingVideo ? 'Edit Video' : 'Add New Video'; ?></h3>
                <button class="modal-close" onclick="closePortfolioModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="portfolioForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_portfolio" value="1">
                    <input type="hidden" name="video_id" value="<?php echo $editingVideo['id'] ?? ''; ?>">
                    <input type="hidden" name="current_thumbnail" value="<?php echo htmlspecialchars($editingVideo['thumbnail_file'] ?? ''); ?>">
                    <input type="hidden" name="current_video" value="<?php echo htmlspecialchars($editingVideo['video_file'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingVideo['title'] ?? ''); ?>" 
                               placeholder="Enter video title" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter video description..." required><?php echo htmlspecialchars($editingVideo['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingVideo['display_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingVideo['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingVideo['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Video File *</label>
                        <input type="file" class="form-control" name="video" accept="video/*" <?php echo empty($editingVideo) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Accepted formats: MP4, MOV, AVI, MKV</small>
                        
                        <?php if (!empty($editingVideo['video_file'])): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Video:</p>
                            <video controls style="max-width: 100%; border-radius: 8px; background: #000;">
                                <source src="<?php echo htmlspecialchars($editingVideo['video_file']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 5px;">
                                Leave empty to keep current video
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thumbnail Image *</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*" <?php echo empty($editingVideo) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 800x450px, Max size: 2MB</small>
                        
                        <?php if (!empty($editingVideo['thumbnail_file'])): ?>
                        <div style="margin-top: 10px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Thumbnail:</p>
                            <img src="<?php echo htmlspecialchars($editingVideo['thumbnail_file']); ?>" 
                                 alt="Current Thumbnail" 
                                 style="max-width: 200px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closePortfolioModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingVideo ? 'Update' : 'Save'; ?> Video
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Logo Modal -->
    <div class="modal" id="logoModal" <?php echo $editingLogo ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingLogo ? 'Edit Logo' : 'Add New Logo Design'; ?></h3>
                <button class="modal-close" onclick="closeLogoModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="logoForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_logo" value="1">
                    <input type="hidden" name="logo_id" value="<?php echo $editingLogo['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingLogo['image_url'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Logo Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingLogo['title'] ?? ''); ?>" 
                               placeholder="Enter logo title (e.g., Visual Vibes Logo)" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter logo description..." required><?php echo htmlspecialchars($editingLogo['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category">
                                <option value="Logo" <?php echo ($editingLogo['category'] ?? 'Logo') === 'Logo' ? 'selected' : ''; ?>>Logo Design</option>
                                <option value="Brand Identity" <?php echo ($editingLogo['category'] ?? '') === 'Brand Identity' ? 'selected' : ''; ?>>Brand Identity</option>
                                <option value="Modern Logo" <?php echo ($editingLogo['category'] ?? '') === 'Modern Logo' ? 'selected' : ''; ?>>Modern Logo</option>
                                <option value="Minimalist" <?php echo ($editingLogo['category'] ?? '') === 'Minimalist' ? 'selected' : ''; ?>>Minimalist</option>
                                <option value="Corporate" <?php echo ($editingLogo['category'] ?? '') === 'Corporate' ? 'selected' : ''; ?>>Corporate</option>
                                <option value="Creative" <?php echo ($editingLogo['category'] ?? '') === 'Creative' ? 'selected' : ''; ?>>Creative</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingLogo['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingLogo['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingLogo['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Logo Image *</label>
                        <input type="file" class="form-control" name="logo_image" accept="image/*" <?php echo empty($editingLogo) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Accepted formats: PNG, JPG, SVG, GIF</small>
                        
                        <?php if (!empty($editingLogo['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Logo:</p>
                            <img src="<?php echo htmlspecialchars($editingLogo['image_url']); ?>" 
                                 alt="Current Logo" 
                                 style="max-width: 200px; background: var(--dark-secondary); padding: 10px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeLogoModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingLogo ? 'Update' : 'Save'; ?> Logo
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Website Modal -->
    <div class="modal" id="websiteModal" <?php echo $editingWebsite ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingWebsite ? 'Edit Website' : 'Add New Website Portfolio'; ?></h3>
                <button class="modal-close" onclick="closeWebsiteModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="websiteForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_website" value="1">
                    <input type="hidden" name="website_id" value="<?php echo $editingWebsite['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingWebsite['image_url'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Website Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingWebsite['title'] ?? ''); ?>" 
                               placeholder="Enter website title (e.g., Blog Website)" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter website description..." required><?php echo htmlspecialchars($editingWebsite['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Website URL *</label>
                        <input type="url" class="form-control" name="website_url" 
                               value="<?php echo htmlspecialchars($editingWebsite['website_url'] ?? ''); ?>" 
                               placeholder="https://example.com" required>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Include https:// or http://</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select class="form-control" name="category">
                                <option value="Website" <?php echo ($editingWebsite['category'] ?? 'Website') === 'Website' ? 'selected' : ''; ?>>Website</option>
                                <option value="Portfolio" <?php echo ($editingWebsite['category'] ?? '') === 'Portfolio' ? 'selected' : ''; ?>>Portfolio</option>
                                <option value="E-commerce" <?php echo ($editingWebsite['category'] ?? '') === 'E-commerce' ? 'selected' : ''; ?>>E-commerce</option>
                                <option value="Blog" <?php echo ($editingWebsite['category'] ?? '') === 'Blog' ? 'selected' : ''; ?>>Blog</option>
                                <option value="Corporate" <?php echo ($editingWebsite['category'] ?? '') === 'Corporate' ? 'selected' : ''; ?>>Corporate</option>
                                <option value="Restaurant" <?php echo ($editingWebsite['category'] ?? '') === 'Restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                                <option value="Landing Page" <?php echo ($editingWebsite['category'] ?? '') === 'Landing Page' ? 'selected' : ''; ?>>Landing Page</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingWebsite['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingWebsite['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingWebsite['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Website Screenshot *</label>
                        <input type="file" class="form-control" name="website_image" accept="image/*" <?php echo empty($editingWebsite) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 800x600px, Max size: 2MB</small>
                        
                        <?php if (!empty($editingWebsite['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Screenshot:</p>
                            <img src="<?php echo htmlspecialchars($editingWebsite['image_url']); ?>" 
                                 alt="Current Screenshot" 
                                 style="max-width: 200px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeWebsiteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingWebsite ? 'Update' : 'Save'; ?> Website
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Flyer Modal -->
    <div class="modal" id="flyerModal" <?php echo $editingFlyer ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingFlyer ? 'Edit Flyer/Poster' : 'Add New Flyer/Poster'; ?></h3>
                <button class="modal-close" onclick="closeFlyerModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="flyerForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_flyer" value="1">
                    <input type="hidden" name="flyer_id" value="<?php echo $editingFlyer['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingFlyer['image_url'] ?? ''); ?>">
                    <input type="hidden" name="current_thumbnail" value="<?php echo htmlspecialchars($editingFlyer['thumbnail_url'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingFlyer['title'] ?? ''); ?>" 
                               placeholder="Enter title (e.g., Andaman Package Flyer)" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter description..." required><?php echo htmlspecialchars($editingFlyer['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Flyer" <?php echo ($editingFlyer['category'] ?? '') === 'Flyer' ? 'selected' : ''; ?>>Flyer</option>
                                <option value="Poster" <?php echo ($editingFlyer['category'] ?? '') === 'Poster' ? 'selected' : ''; ?>>Poster</option>
                                <option value="Travel" <?php echo ($editingFlyer['category'] ?? '') === 'Travel' ? 'selected' : ''; ?>>Travel Package</option>
                                <option value="Event" <?php echo ($editingFlyer['category'] ?? '') === 'Event' ? 'selected' : ''; ?>>Event</option>
                                <option value="Promotional" <?php echo ($editingFlyer['category'] ?? '') === 'Promotional' ? 'selected' : ''; ?>>Promotional</option>
                                <option value="Business" <?php echo ($editingFlyer['category'] ?? '') === 'Business' ? 'selected' : ''; ?>>Business</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingFlyer['display_order'] ?? 0; ?>" min="0">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingFlyer['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingFlyer['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Flyer/Poster Image *</label>
                        <input type="file" class="form-control" name="flyer_image" accept="image/*" <?php echo empty($editingFlyer) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 800x1000px, Max size: 5MB</small>
                        
                        <?php if (!empty($editingFlyer['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($editingFlyer['image_url']); ?>" 
                                 alt="Current Flyer" 
                                 style="max-width: 200px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thumbnail Image (Optional)</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 300x400px, Max size: 2MB</small>
                        
                        <?php if (!empty($editingFlyer['thumbnail_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Thumbnail:</p>
                            <img src="<?php echo htmlspecialchars($editingFlyer['thumbnail_url']); ?>" 
                                 alt="Current Thumbnail" 
                                 style="max-width: 150px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeFlyerModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingFlyer ? 'Update' : 'Save'; ?> Flyer/Poster
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Graphic Design Modal -->
    <div class="modal" id="graphicModal" <?php echo $editingGraphic ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingGraphic ? 'Edit Graphic Design' : 'Add New 2D/3D Work'; ?></h3>
                <button class="modal-close" onclick="closeGraphicModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="graphicForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_graphic" value="1">
                    <input type="hidden" name="graphic_id" value="<?php echo $editingGraphic['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingGraphic['image_url'] ?? ''); ?>">
                    <input type="hidden" name="current_thumbnail" value="<?php echo htmlspecialchars($editingGraphic['thumbnail_url'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingGraphic['title'] ?? ''); ?>" 
                               placeholder="Enter title (e.g., Kitchen 3D Model)" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter description..." required><?php echo htmlspecialchars($editingGraphic['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Design Type *</label>
                            <select class="form-control" name="design_type" required>
                                <option value="">Select Type</option>
                                <option value="2D" <?php echo ($editingGraphic['design_type'] ?? '') === '2D' ? 'selected' : ''; ?>>2D Design</option>
                                <option value="3D" <?php echo ($editingGraphic['design_type'] ?? '') === '3D' ? 'selected' : ''; ?>>3D Design</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="Architecture" <?php echo ($editingGraphic['category'] ?? '') === 'Architecture' ? 'selected' : ''; ?>>Architecture</option>
                                <option value="Interior Design" <?php echo ($editingGraphic['category'] ?? '') === 'Interior Design' ? 'selected' : ''; ?>>Interior Design</option>
                                <option value="Product Design" <?php echo ($editingGraphic['category'] ?? '') === 'Product Design' ? 'selected' : ''; ?>>Product Design</option>
                                <option value="Character Design" <?php echo ($editingGraphic['category'] ?? '') === 'Character Design' ? 'selected' : ''; ?>>Character Design</option>
                                <option value="Industrial Design" <?php echo ($editingGraphic['category'] ?? '') === 'Industrial Design' ? 'selected' : ''; ?>>Industrial Design</option>
                                <option value="Graphic Design" <?php echo ($editingGraphic['category'] ?? '') === 'Graphic Design' ? 'selected' : ''; ?>>Graphic Design</option>
                                <option value="Animation" <?php echo ($editingGraphic['category'] ?? '') === 'Animation' ? 'selected' : ''; ?>>Animation</option>
                                <option value="Mechanical" <?php echo ($editingGraphic['category'] ?? '') === 'Mechanical' ? 'selected' : ''; ?>>Mechanical</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingGraphic['display_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingGraphic['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingGraphic['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Design Image *</label>
                        <input type="file" class="form-control" name="graphic_image" accept="image/*" <?php echo empty($editingGraphic) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 1200x800px, Max size: 5MB</small>
                        
                        <?php if (!empty($editingGraphic['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($editingGraphic['image_url']); ?>" 
                                 alt="Current Design" 
                                 style="max-width: 200px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thumbnail Image (Optional)</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 300x200px, Max size: 2MB</small>
                        
                        <?php if (!empty($editingGraphic['thumbnail_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Thumbnail:</p>
                            <img src="<?php echo htmlspecialchars($editingGraphic['thumbnail_url']); ?>" 
                                 alt="Current Thumbnail" 
                                 style="max-width: 150px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeGraphicModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingGraphic ? 'Update' : 'Save'; ?> Design
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- UI/UX Design Modal -->
    <div class="modal" id="uiuxModal" <?php echo $editingUiux ? 'style="display: flex;"' : ''; ?>>
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo $editingUiux ? 'Edit UI/UX Design' : 'Add New UI/UX Design'; ?></h3>
                <button class="modal-close" onclick="closeUiuxModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="uiuxForm">
                <div class="modal-body">
                    <input type="hidden" name="submit_uiux" value="1">
                    <input type="hidden" name="uiux_id" value="<?php echo $editingUiux['id'] ?? ''; ?>">
                    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editingUiux['image_url'] ?? ''); ?>">
                    <input type="hidden" name="current_thumbnail" value="<?php echo htmlspecialchars($editingUiux['thumbnail_url'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" 
                               value="<?php echo htmlspecialchars($editingUiux['title'] ?? ''); ?>" 
                               placeholder="Enter design title (e.g., E-commerce Dashboard UI)" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter design description..." required><?php echo htmlspecialchars($editingUiux['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Prototype URL</label>
                        <input type="url" class="form-control" name="prototype_url" 
                               value="<?php echo htmlspecialchars($editingUiux['prototype_url'] ?? ''); ?>" 
                               placeholder="https://figma.com/file/... or https://xd.adobe.com/...">
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Link to Figma, Adobe XD, or other prototyping tool</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Design Type *</label>
                            <select class="form-control" name="design_type" required>
                                <option value="">Select Type</option>
                                <option value="Web UI" <?php echo ($editingUiux['design_type'] ?? '') === 'Web UI' ? 'selected' : ''; ?>>Web UI</option>
                                <option value="Mobile UI" <?php echo ($editingUiux['design_type'] ?? '') === 'Mobile UI' ? 'selected' : ''; ?>>Mobile UI</option>
                                <option value="Dashboard" <?php echo ($editingUiux['design_type'] ?? '') === 'Dashboard' ? 'selected' : ''; ?>>Dashboard</option>
                                <option value="Landing Page" <?php echo ($editingUiux['design_type'] ?? '') === 'Landing Page' ? 'selected' : ''; ?>>Landing Page</option>
                                <option value="App Design" <?php echo ($editingUiux['design_type'] ?? '') === 'App Design' ? 'selected' : ''; ?>>App Design</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option value="UI Design" <?php echo ($editingUiux['category'] ?? '') === 'UI Design' ? 'selected' : ''; ?>>UI Design</option>
                                <option value="UX Design" <?php echo ($editingUiux['category'] ?? '') === 'UX Design' ? 'selected' : ''; ?>>UX Design</option>
                                <option value="Wireframe" <?php echo ($editingUiux['category'] ?? '') === 'Wireframe' ? 'selected' : ''; ?>>Wireframe</option>
                                <option value="Mockup" <?php echo ($editingUiux['category'] ?? '') === 'Mockup' ? 'selected' : ''; ?>>Mockup</option>
                                <option value="Prototype" <?php echo ($editingUiux['category'] ?? '') === 'Prototype' ? 'selected' : ''; ?>>Prototype</option>
                                <option value="E-commerce" <?php echo ($editingUiux['category'] ?? '') === 'E-commerce' ? 'selected' : ''; ?>>E-commerce</option>
                                <option value="SaaS" <?php echo ($editingUiux['category'] ?? '') === 'SaaS' ? 'selected' : ''; ?>>SaaS</option>
                                <option value="Dashboard" <?php echo ($editingUiux['category'] ?? '') === 'Dashboard' ? 'selected' : ''; ?>>Dashboard</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Display Order</label>
                            <input type="number" class="form-control" name="display_order" 
                                   value="<?php echo $editingUiux['display_order'] ?? 0; ?>" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="is_active">
                                <option value="1" <?php echo ($editingUiux['is_active'] ?? 1) == 1 ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo ($editingUiux['is_active'] ?? 1) == 0 ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Design Image *</label>
                        <input type="file" class="form-control" name="uiux_image" accept="image/*" <?php echo empty($editingUiux) ? 'required' : ''; ?>>
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 1200x800px, Max size: 5MB</small>
                        
                        <?php if (!empty($editingUiux['image_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Image:</p>
                            <img src="<?php echo htmlspecialchars($editingUiux['image_url']); ?>" 
                                 alt="Current Design" 
                                 style="max-width: 200px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Thumbnail Image (Optional)</label>
                        <input type="file" class="form-control" name="thumbnail" accept="image/*">
                        <small style="color: var(--text-secondary); font-size: 0.8rem;">Recommended: 300x200px, Max size: 2MB</small>
                        
                        <?php if (!empty($editingUiux['thumbnail_url'])): ?>
                        <div style="margin-top: 15px;">
                            <p style="margin-bottom: 5px; font-size: 0.9rem;">Current Thumbnail:</p>
                            <img src="<?php echo htmlspecialchars($editingUiux['thumbnail_url']); ?>" 
                                 alt="Current Thumbnail" 
                                 style="max-width: 150px; border-radius: 8px; border: var(--border);">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeUiuxModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?php echo $editingUiux ? 'Update' : 'Save'; ?> Design
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Video Preview Modal -->
    <div class="modal" id="videoPreviewModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="previewVideoTitle">Video Preview</h3>
                <button class="modal-close" onclick="closeVideoPreview()">&times;</button>
            </div>
            <div class="modal-body">
                <video id="previewVideoPlayer" controls style="width: 100%; border-radius: 8px; background: #000;">
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>
    
    <!-- Logo Preview Modal -->
    <div class="modal" id="logoPreviewModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title" id="previewLogoTitle">Logo Preview</h3>
                <button class="modal-close" onclick="closeLogoPreview()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img id="previewLogoImage" src="" alt="Logo Preview" 
                     style="max-width: 100%; max-height: 400px; background: var(--dark-secondary); padding: 20px; border-radius: 12px; border: var(--border);">
            </div>
        </div>
    </div>
    
    <!-- Flyer Preview Modal -->
    <div class="modal" id="flyerPreviewModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="previewFlyerTitle">Flyer Preview</h3>
                <button class="modal-close" onclick="closeFlyerPreview()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img id="previewFlyerImage" src="" alt="Flyer Preview" 
                     style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: var(--border);">
            </div>
        </div>
    </div>
    
    <!-- Graphic Preview Modal -->
    <div class="modal" id="graphicPreviewModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="previewGraphicTitle">Design Preview</h3>
                <button class="modal-close" onclick="closeGraphicPreview()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img id="previewGraphicImage" src="" alt="Graphic Preview" 
                     style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: var(--border);">
            </div>
        </div>
    </div>
    
    <!-- UI/UX Preview Modal -->
    <div class="modal" id="uiuxPreviewModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3 class="modal-title" id="previewUiuxTitle">UI/UX Design Preview</h3>
                <button class="modal-close" onclick="closeUiuxPreview()">&times;</button>
            </div>
            <div class="modal-body" style="text-align: center;">
                <img id="previewUiuxImage" src="" alt="UI/UX Preview" 
                     style="max-width: 100%; max-height: 70vh; border-radius: 8px; border: var(--border);">
            </div>
        </div>
    </div>


<!-- Enquiries Section -->
<div id="enquiries" class="content-section">
    <div class="top-bar">
        <!-- <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div> -->
        <!-- <h1 class="page-title">Enquiry Management</h1> -->
        <!-- <div class="user-menu">
           
        </div> -->
    </div>
    
    <div class="content-area">
        <!-- Stats Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total Enquiries</h3>
                    <div class="card-icon primary">
                        <i class="fas fa-inbox"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-value"><?php echo $enquiriesStats['total'] ?? 0; ?></div>
                    <div class="card-trend">
                        <span>All time enquiries</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending</h3>
                    <div class="card-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-value"><?php echo $enquiriesStats['pending'] ?? 0; ?></div>
                    <div class="card-trend">
                        <span>Awaiting review</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contacted</h3>
                    <div class="card-icon success">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-value"><?php echo $enquiriesStats['contacted'] ?? 0; ?></div>
                    <div class="card-trend">
                        <span>Follow-ups needed</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Archived</h3>
                    <div class="card-icon secondary">
                        <i class="fas fa-archive"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-value"><?php echo $enquiriesStats['archived'] ?? 0; ?></div>
                    <div class="card-trend">
                        <span>Completed/Archived</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters and Actions -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">All Enquiries</h2>
                <div class="table-actions">
                    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        <input type="hidden" name="section" value="enquiries">
                        
                        <div class="form-group" style="margin: 0;">
                            <select name="status" class="form-control" style="width: auto;" onchange="this.form.submit()">
                                <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="reviewed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'reviewed') ? 'selected' : ''; ?>>Reviewed</option>
                                <option value="contacted" <?php echo (isset($_GET['status']) && $_GET['status'] === 'contacted') ? 'selected' : ''; ?>>Contacted</option>
                                <option value="archived" <?php echo (isset($_GET['status']) && $_GET['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin: 0;">
                            <select name="service" class="form-control" style="width: auto;" onchange="this.form.submit()">
                                <option value="all" <?php echo (!isset($_GET['service']) || $_GET['service'] === 'all') ? 'selected' : ''; ?>>All Services</option>
                                <?php if (!empty($serviceTypes)): ?>
                                    <?php foreach ($serviceTypes as $service): ?>
                                        <option value="<?php echo htmlspecialchars($service); ?>" 
                                            <?php echo (isset($_GET['service']) && $_GET['service'] === $service) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <input type="text" name="search" class="form-control" placeholder="Search enquiries..." 
                               style="width: 200px;" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        
                        <a href="?section=enquiries" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                        
                        <a href="?section=enquiries&export_enquiries=1" class="btn btn-primary">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    </form>
                </div>
            </div>
            
            <?php if (empty($enquiries)): ?>
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <h3 style="color: var(--text-secondary); margin-bottom: 0.5rem;">No enquiries found</h3>
                    <p style="color: var(--text-secondary);">Try changing your filters or search term</p>
                </div>
            <?php else: ?>
                <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Service</th>
            <th>Project Type</th>
            <th>Budget</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($enquiries as $enquiry): ?>
            <tr>
                <td><?php echo $enquiry['id']; ?></td>
                <td>
                    <div class="table-cell-truncate" title="<?php echo htmlspecialchars($enquiry['full_name']); ?>">
                        <?php echo htmlspecialchars($enquiry['full_name']); ?>
                    </div>
                </td>
                <td>
                    <div class="table-cell-truncate" title="<?php echo htmlspecialchars($enquiry['email']); ?>">
                        <?php echo htmlspecialchars($enquiry['email']); ?>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($enquiry['phone'] ?? '-'); ?></td>
                <td>
                    <span class="badge-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; background: rgba(67, 97, 238, 0.1); color: var(--primary);">
                        <?php echo htmlspecialchars($enquiry['service_type'] ?? '-'); ?>
                    </span>
                </td>
                <td>
                    <span class="badge-<?php echo $enquiry['project_type'] === 'redesign' ? 'redesign' : 'new'; ?>">
                        <?php echo $enquiry['project_type'] === 'redesign' ? 'Redesign' : 'New Project'; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($enquiry['budget_range'] ?? '-'); ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                        <select name="status" class="status-select" 
                                style="width: auto; padding: 0.3rem 0.5rem; font-size: 0.8rem;"
                                onchange="this.form.submit()">
                            <option value="pending" <?php echo $enquiry['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="reviewed" <?php echo $enquiry['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                            <option value="contacted" <?php echo $enquiry['status'] === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                            <option value="archived" <?php echo $enquiry['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                        <input type="hidden" name="update_enquiry_status" value="1">
                    </form>
                </td>
                <td><?php echo date('M d, Y', strtotime($enquiry['created_at'])); ?></td>
                <td>
                    <div class="quick-actions">
                        <a href="?section=enquiries&view_enquiry=<?php echo $enquiry['id']; ?>" 
                           class="action-btn view" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($enquiry['email']); ?>" 
                           class="action-btn email" title="Send Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <?php if (!empty($enquiry['phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($enquiry['phone']); ?>" 
                               class="action-btn" title="Call">
                                <i class="fas fa-phone"></i>
                            </a>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this enquiry?');">
                            <input type="hidden" name="enquiry_id" value="<?php echo $enquiry['id']; ?>">
                            <input type="hidden" name="delete_enquiry" value="1">
                            <button type="submit" class="action-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
                
                <div class="table-footer" style="padding: 1rem; border-top: var(--border); display: flex; justify-content: space-between; align-items: center;">
                    <div class="table-info">
                        Showing <?php echo count($enquiries); ?> of <?php echo $enquiriesStats['total'] ?? 0; ?> enquiries
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Enquiry Details Modal -->
<?php if ($viewingEnquiry): ?>
<div class="modal active" id="enquiryDetailsModal">
    <div class="modal-content enquiry-details-modal">
        <div class="modal-header">
            <h3 class="modal-title">Enquiry Details #<?php echo $viewingEnquiry['id']; ?></h3>
            <button class="modal-close" onclick="window.location.href='?section=enquiries'">&times;</button>
        </div>
        <div class="modal-body">
            <div class="detail-group">
                <h4 class="detail-label">Client Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['full_name']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['email']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['phone'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Company</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['company'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Location</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['location'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="detail-group">
                <h4 class="detail-label">Project Details</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Service Type</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['service_type']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Other Service</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['other_service'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Project Type</div>
                        <div class="detail-value">
                            <span class="badge-<?php echo $viewingEnquiry['project_type'] === 'redesign' ? 'redesign' : 'new'; ?>">
                                <?php echo $viewingEnquiry['project_type'] === 'redesign' ? 'Redesign' : 'New Project'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Design Style</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['design_style'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Budget Range</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['budget_range'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Deadline</div>
                        <div class="detail-value"><?php echo $viewingEnquiry['deadline'] ? date('M d, Y', strtotime($viewingEnquiry['deadline'])) : '-'; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="detail-group">
                <h4 class="detail-label">Project Description</h4>
                <div class="detail-value multiline"><?php echo nl2br(htmlspecialchars($viewingEnquiry['project_description'] ?? 'No description provided')); ?></div>
            </div>
            
            <?php if (!empty($viewingEnquiry['file_path'])): ?>
            <div class="detail-group">
                <h4 class="detail-label">Attached File</h4>
                <div class="detail-value">
                    <a href="<?php echo htmlspecialchars($viewingEnquiry['file_path']); ?>" 
                       target="_blank" class="btn btn-secondary btn-sm">
                        <i class="fas fa-download"></i> Download File
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="detail-group">
                <h4 class="detail-label">Contact Preferences</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Preferred Contact</div>
                        <div class="contact-badges">
                            <?php if ($viewingEnquiry['contact_preference']): ?>
                                <?php $preferences = explode(',', $viewingEnquiry['contact_preference']); ?>
                                <?php foreach ($preferences as $pref): ?>
                                    <span class="contact-badge"><?php echo trim($pref); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Best Time to Contact</div>
                        <div class="detail-value"><?php echo $viewingEnquiry['best_time'] ?? '-'; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="detail-group">
                <h4 class="detail-label">Additional Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">How did you hear about us?</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['hear_about'] ?? '-'); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Other Source</div>
                        <div class="detail-value"><?php echo htmlspecialchars($viewingEnquiry['other_source'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($viewingEnquiry['additional_notes'])): ?>
            <div class="detail-group">
                <h4 class="detail-label">Additional Notes</h4>
                <div class="detail-value multiline"><?php echo nl2br(htmlspecialchars($viewingEnquiry['additional_notes'])); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="detail-group">
                <h4 class="detail-label">Enquiry Status & Timeline</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Current Status</div>
                        <div class="detail-value">
                            <span class="status-<?php echo $viewingEnquiry['status']; ?>">
                                <?php echo ucfirst($viewingEnquiry['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Submitted On</div>
                        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($viewingEnquiry['created_at'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Updated</div>
                        <div class="detail-value"><?php echo date('M d, Y h:i A', strtotime($viewingEnquiry['updated_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="mailto:<?php echo htmlspecialchars($viewingEnquiry['email']); ?>" class="btn btn-primary">
                <i class="fas fa-envelope"></i> Send Email
            </a>
            <?php if (!empty($viewingEnquiry['phone'])): ?>
                <a href="tel:<?php echo htmlspecialchars($viewingEnquiry['phone']); ?>" class="btn btn-success">
                    <i class="fas fa-phone"></i> Call Client
                </a>
            <?php endif; ?>
            <a href="?section=enquiries" class="btn btn-secondary">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>
    <script>
        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
        
        menuToggle.addEventListener('click', toggleSidebar);
        mobileOverlay.addEventListener('click', toggleSidebar);
        
        // Navigation System
        const navLinks = document.querySelectorAll('.nav-link');
        const contentSections = document.querySelectorAll('.content-section');
        const pageTitle = document.getElementById('pageTitle');
        
        function navigateToSection(sectionId) {
            // Update active nav link
            navLinks.forEach(nav => nav.classList.remove('active'));
            document.querySelector(`.nav-link[href="#${sectionId}"]`)?.classList.add('active');
            
            // Update content section
            contentSections.forEach(section => {
                section.classList.remove('active');
                if (section.id === sectionId) {
                    section.classList.add('active');
                }
            });
            
            // Update page title
            const activeLink = document.querySelector(`.nav-link[href="#${sectionId}"] span`);
            if (activeLink) {
                pageTitle.textContent = activeLink.textContent;
            }
            
            // Close sidebar on mobile
            if (window.innerWidth <= 992) {
                toggleSidebar();
            }
            
            // Scroll to top of content
            document.querySelector('.content-area').scrollTop = 0;
            
            // Update URL hash without scrolling
            history.pushState(null, null, `#${sectionId}`);
        }
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                navigateToSection(targetId);
            });
        });
        
        // Handle initial hash on page load
        document.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                navigateToSection(hash);
            }
        });
        
        // Modal Functions
        function openSlideModal() {
            document.getElementById('carouselModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeSlideModal() {
            document.getElementById('carouselModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openTeamModal() {
            document.getElementById('teamModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeTeamModal() {
            document.getElementById('teamModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openPortfolioModal() {
            document.getElementById('portfolioModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closePortfolioModal() {
            document.getElementById('portfolioModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openLogoModal() {
            document.getElementById('logoModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeLogoModal() {
            document.getElementById('logoModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openWebsiteModal() {
            document.getElementById('websiteModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeWebsiteModal() {
            document.getElementById('websiteModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openFlyerModal() {
            document.getElementById('flyerModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeFlyerModal() {
            document.getElementById('flyerModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openGraphicModal() {
            document.getElementById('graphicModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeGraphicModal() {
            document.getElementById('graphicModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function openUiuxModal() {
            document.getElementById('uiuxModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeUiuxModal() {
            document.getElementById('uiuxModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        // Preview Functions
        function previewVideo(videoUrl, title) {
            document.getElementById('previewVideoTitle').textContent = title;
            const videoPlayer = document.getElementById('previewVideoPlayer');
            videoPlayer.src = videoUrl;
            document.getElementById('videoPreviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeVideoPreview() {
            const videoPlayer = document.getElementById('previewVideoPlayer');
            videoPlayer.pause();
            videoPlayer.src = '';
            document.getElementById('videoPreviewModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        
        function previewLogo(imageUrl, title) {
            document.getElementById('previewLogoTitle').textContent = title;
            document.getElementById('previewLogoImage').src = imageUrl;
            document.getElementById('logoPreviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeLogoPreview() {
            document.getElementById('logoPreviewModal').style.display = 'none';
            document.getElementById('previewLogoImage').src = '';
            document.body.style.overflow = '';
        }
        
        function previewFlyer(imageUrl, title) {
            document.getElementById('previewFlyerTitle').textContent = title;
            document.getElementById('previewFlyerImage').src = imageUrl;
            document.getElementById('flyerPreviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeFlyerPreview() {
            document.getElementById('flyerPreviewModal').style.display = 'none';
            document.getElementById('previewFlyerImage').src = '';
            document.body.style.overflow = '';
        }
        
        function previewGraphic(imageUrl, title) {
            document.getElementById('previewGraphicTitle').textContent = title;
            document.getElementById('previewGraphicImage').src = imageUrl;
            document.getElementById('graphicPreviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeGraphicPreview() {
            document.getElementById('graphicPreviewModal').style.display = 'none';
            document.getElementById('previewGraphicImage').src = '';
            document.body.style.overflow = '';
        }
        
        function previewUiux(imageUrl, title) {
            document.getElementById('previewUiuxTitle').textContent = title;
            document.getElementById('previewUiuxImage').src = imageUrl;
            document.getElementById('uiuxPreviewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeUiuxPreview() {
            document.getElementById('uiuxPreviewModal').style.display = 'none';
            document.getElementById('previewUiuxImage').src = '';
            document.body.style.overflow = '';
        }
        
        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    const modalId = modal.id;
                    switch(modalId) {
                        case 'carouselModal': closeSlideModal(); break;
                        case 'teamModal': closeTeamModal(); break;
                        case 'portfolioModal': closePortfolioModal(); break;
                        case 'logoModal': closeLogoModal(); break;
                        case 'websiteModal': closeWebsiteModal(); break;
                        case 'flyerModal': closeFlyerModal(); break;
                        case 'graphicModal': closeGraphicModal(); break;
                        case 'uiuxModal': closeUiuxModal(); break;
                        case 'videoPreviewModal': closeVideoPreview(); break;
                        case 'logoPreviewModal': closeLogoPreview(); break;
                        case 'flyerPreviewModal': closeFlyerPreview(); break;
                        case 'graphicPreviewModal': closeGraphicPreview(); break;
                        case 'uiuxPreviewModal': closeUiuxPreview(); break;
                    }
                }
            });
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="display: flex"]');
                if (openModal) {
                    const modalId = openModal.id;
                    switch(modalId) {
                        case 'carouselModal': closeSlideModal(); break;
                        case 'teamModal': closeTeamModal(); break;
                        case 'portfolioModal': closePortfolioModal(); break;
                        case 'logoModal': closeLogoModal(); break;
                        case 'websiteModal': closeWebsiteModal(); break;
                        case 'flyerModal': closeFlyerModal(); break;
                        case 'graphicModal': closeGraphicModal(); break;
                        case 'uiuxModal': closeUiuxModal(); break;
                        case 'videoPreviewModal': closeVideoPreview(); break;
                        case 'logoPreviewModal': closeLogoPreview(); break;
                        case 'flyerPreviewModal': closeFlyerPreview(); break;
                        case 'graphicPreviewModal': closeGraphicPreview(); break;
                        case 'uiuxPreviewModal': closeUiuxPreview(); break;
                    }
                }
            }
        });
        
        // Responsive adjustments
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Handle form submissions to show modals if editing
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($currentSlide): ?>
            openSlideModal();
            <?php elseif ($editingMember): ?>
            openTeamModal();
            <?php elseif ($editingVideo): ?>
            openPortfolioModal();
            <?php elseif ($editingLogo): ?>
            openLogoModal();
            <?php elseif ($editingWebsite): ?>
            openWebsiteModal();
            <?php elseif ($editingFlyer): ?>
            openFlyerModal();
            <?php elseif ($editingGraphic): ?>
            openGraphicModal();
            <?php elseif ($editingUiux): ?>
            openUiuxModal();
            <?php endif; ?>
        });
        
        // Prevent form submission on Enter key in non-submit inputs
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && 
                    e.target.type !== 'submit' && e.target.type !== 'button') {
                    e.preventDefault();
                }
            });
        });
        
        // Add touch support for better mobile experience
        document.addEventListener('touchstart', () => {}, {passive: true});
        
        // Handle orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                    mobileOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 100);
        });
        new Choices('#category', {
    searchEnabled: false,
    itemSelectText: '',
});

    </script>
    
</body>
</html>