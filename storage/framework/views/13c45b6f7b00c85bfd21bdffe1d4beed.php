<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'Laravel')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/custom-tables.css')); ?>">
    <!-- SimpleBar for reliable in-card scrolling -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css" />
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }
        body {
            background-color: #f7f7ff;
        }
        
        /* Modern Navbar Styles */
        .navbar-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            backdrop-filter: blur(10px);
        }
        
        .navbar-modern .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .navbar-modern .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .navbar-modern .nav-link {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 0 0.25rem;
        }
        
        .navbar-modern .nav-link:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .navbar-modern .dropdown-menu {
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            border-radius: 12px;
            margin-top: 0.5rem;
            animation: fadeInDown 0.3s ease;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .navbar-modern .dropdown-item {
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0.25rem 0.5rem;
        }
        
        .navbar-modern .dropdown-item:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: translateX(5px);
        }
        
        .navbar-modern .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }
        
        /* Notification Badge */
        .notification-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
        
        .admin-container {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .admin-sidebar {
            width: 280px;
            background: white;
            border-radius: 16px;
            padding: 1.5rem 0;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
            height: fit-content;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            animation: slideInLeft 0.4s ease-out;
        }
        /* Custom Scrollbar for Sidebar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .admin-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        .admin-sidebar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 10px;
        }
        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        }
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .admin-sidebar .nav-link {
            padding: 0.875rem 1.5rem;
            color: #4a5568;
            border-left: 3px solid transparent;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .admin-sidebar .nav-link i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }
        .admin-sidebar .nav-link:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, transparent 100%);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateX(4px);
            padding-left: 1.75rem;
        }
        .admin-sidebar .nav-link:hover i {
            transform: scale(1.15) rotate(5deg);
        }
        .admin-sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.12) 0%, rgba(102, 126, 234, 0.05) 100%);
            border-left-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(102, 126, 234, 0.1);
        }
        .admin-sidebar .nav-link.active i {
            transform: scale(1.1);
        }
        .sidebar-section-title {
            padding: 1.25rem 1.5rem 0.75rem;
            font-weight: 700;
            font-size: 0.75rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-section-title i {
            font-size: 1rem;
            color: var(--primary-color);
        }
        .sidebar-divider {
            border: 0;
            border-top: 2px solid;
            border-image: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent) 1;
            margin: 1rem 1.5rem;
        }
        .admin-sidebar .nav-link.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        .admin-sidebar .alert {
            font-size: 0.85rem;
            margin: 0 1rem;
            border-radius: 12px;
        }
        .admin-content {
            flex: 1;
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
            border: 1px solid rgba(102, 126, 234, 0.1);
            animation: fadeInUp 0.4s ease-out;
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
        
        /* Modern H2 Headings */
        .admin-content h2 {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            padding-bottom: 1rem;
        }
        .admin-content h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        .admin-content h2 i {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.75rem;
        }
        
        /* Modern Card Headers */
        .card-header, .modern-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            color: white !important;
            font-weight: 600;
            border: none !important;
            border-radius: 12px 12px 0 0 !important;
            padding: 1rem 1.25rem !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .card {
            border-radius: 12px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.08);
        }
        
        /* Modern Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            font-weight: 600;
            padding: 0.625rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        
        /* Modern Badges */
        .badge {
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        /* Smooth transitions for all interactive elements */
        a, button, .btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-modern">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo e(route('dashboard')); ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>MOPA</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <?php if(auth()->guard()->check()): ?>
                        <li class="nav-item me-2">
                            <?php echo $__env->make('partials.active_school', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </li>
                        <li class="nav-item dropdown me-2">
                            <a class="nav-link position-relative d-flex align-items-center" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell-fill fs-5"></i>
                                <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span><strong>Pranešimai</strong></span>
                                    <form method="POST" action="<?php echo e(route('notifications.read-all')); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;">
                                            Pažymėti visus
                                        </button>
                                    </form>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationsList">
                                    <li class="text-center py-3 text-muted">
                                        <small>Kraunama...</small>
                                    </li>
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-center small" href="<?php echo e(route('notifications.index')); ?>">
                                        Peržiūrėti visus pranešimus
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5 me-2"></i>
                                <span><?php echo e(Auth::user()->name); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown" style="min-width: 220px;">
                                <li class="px-3 py-2 border-bottom">
                                    <small class="text-muted d-block">Prisijungęs kaip</small>
                                    <strong><?php echo e(Auth::user()->name); ?></strong>
                                </li>
                                <li><a class="dropdown-item" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('profile.my-schools')); ?>"><i class="bi bi-building"></i> Mano mokyklos</a></li>
                                <li><a class="dropdown-item" href="<?php echo e(route('profile.activate-key')); ?>"><i class="bi bi-key"></i> Suaktyvinti raktą</a></li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li>
                                    <form method="POST" action="<?php echo e(route('logout')); ?>" style="display: inline;">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="dropdown-item" style="border: none; background: none; cursor: pointer;">
                                            <i class="bi bi-box-arrow-right"></i> Atsijungti
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <?php if(auth()->guard()->check()): ?>
            <?php
                // Get current school from multiple sources
                $currentSchool = $school ?? $activeSchool ?? null;
                
                // If not set, try to get from session (for supervisor)
                if (!$currentSchool && session('active_school_id')) {
                    $currentSchool = \App\Models\School::find(session('active_school_id'));
                }
            ?>
            <?php if($currentSchool): ?>
            <div class="admin-container">
                <!-- Sidebar -->
                <div class="admin-sidebar">
                    <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <!-- Content -->
                <div class="admin-content">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
            <?php else: ?>
                <div class="mt-4">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mt-4">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
    
    <?php if(auth()->guard()->check()): ?>
    <script>
        // Fetch unread notifications
        function fetchUnreadNotifications() {
            fetch('<?php echo e(route('notifications.unread')); ?>')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    const list = document.getElementById('notificationsList');
                    
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                        
                        let html = '';
                        data.notifications.forEach(notification => {
                            const message = notification.data.message || 'Naujas pranešimas';
                            html += `
                                <li>
                                    <a class="dropdown-item" href="<?php echo e(route('notifications.index')); ?>" style="white-space: normal;">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1 me-2" style="overflow-wrap: break-word; word-wrap: break-word; word-break: break-word;">
                                                <div class="fw-bold small">${notification.type}</div>
                                                <div class="text-muted small">${message}</div>
                                            </div>
                                            <span class="badge bg-primary flex-shrink-0">Naujas</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.7rem;">${notification.created_at}</div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            `;
                        });
                        list.innerHTML = html;
                    } else {
                        badge.style.display = 'none';
                        list.innerHTML = `
                            <li class="text-center py-3 text-muted">
                                <small>Naujų pranešimų nėra</small>
                            </li>
                        `;
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }
        
        // Load notifications on page load
        fetchUnreadNotifications();
        
        // Poll every 30 seconds
        setInterval(fetchUnreadNotifications, 30000);
    </script>
    <?php endif; ?>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\Users\herom\Desktop\Projektai\mopa\resources\views/layouts/admin.blade.php ENDPATH**/ ?>