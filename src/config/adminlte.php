<?php

use ColorlibHQ\AdminLte\Menu\Filters\ActiveFilter;
use ColorlibHQ\AdminLte\Menu\Filters\GateFilter;
use ColorlibHQ\AdminLte\Menu\Filters\HrefFilter;
use ColorlibHQ\AdminLte\Menu\Filters\SearchFilter;

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The default page title, and an optional prefix/postfix applied to every
    | page title set with @section('title', ...).
    |
    */

    'title' => 'AdminLTE 4',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | AdminLTE 4 uses Source Sans 3. Set to false to self-host or skip.
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | The brand logo shown in the sidebar. `logo` accepts HTML and is
    | rendered UNESCAPED ({!! !!}) — only ever put trusted, hardcoded
    | markup here, never user-supplied or database-driven content.
    |
    */

    'logo' => '<b>Admin</b>LTE',
    'logo_img' => 'vendor/adminlte/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image opacity-75 shadow',
    'logo_img_alt' => 'AdminLTE Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User menu (topbar dropdown)
    |--------------------------------------------------------------------------
    |
    | `usermenu_profile_url` is passed through `url()`, so set it to a path or
    | an absolute URL — not a route name. `adminlte:scaffold` prefixes its
    | routes with `admin`, so use 'admin/profile' once the profile section is
    | scaffolded. `false` hides the "Profile" button and lets "Sign out" fill
    | the footer.
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'text-bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Body-level layout switches. These map directly to AdminLTE 4 body classes.
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,   // .layout-fixed
    'layout_fixed_navbar' => true,    // .fixed-header
    'layout_fixed_footer' => null,    // .fixed-footer
    'layout_dark_mode' => null,       // null = respect system / user toggle
    'layout_rtl' => false,            // Enable right-to-left layout

    /*
    |--------------------------------------------------------------------------
    | Footer & Preloader
    |--------------------------------------------------------------------------
    |
    | `footer_left` / `footer_right` accept HTML and are rendered UNESCAPED
    | ({!! !!}) — only ever put trusted, hardcoded markup here, never
    | user-supplied or database-driven content.
    |
    */

    'footer_left' => 'Copyright &copy; 2014-'.date('Y').' <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>. All rights reserved.',
    'footer_right' => 'Anything you want',
    'preloader' => false,
    'control_sidebar' => false,
    'control_sidebar_theme' => 'dark',

    // Documentation URL used by the navbar "Documentation" link and the sidebar
    // "View documentation" CTA (false to hide the CTA). Defaults to the in-app
    // docs viewer served at /docs (see the `docs` keys below).
    'sidebar_docs_url' => '/docs',

    // Bundled demo/showcase pages (Dashboard v2/v3, Widgets, UI, Forms, Tables,
    // Layout Options, Theme Generate, auth variants, error pages). Set false to
    // skip registering their routes in production.
    'demo' => true,
    'demo_middleware' => ['web', 'auth'],

    // In-app documentation viewer: renders this package's docs/*.md files at
    // /docs and /docs/{page}. Set 'docs' => false to disable the route.
    'docs' => true,
    'docs_middleware' => ['web'],

    // Activity log (`adminlte:scaffold activity-log`). ActivityLogger never stores
    // property keys that look like credentials (password, *token*, *secret*,
    // api_key, private_key, recovery_codes) or that are in the subject model's
    // $hidden list. Add any other keys that must never be logged here.
    'activity_log' => [
        'redact' => [],
    ],

    'sidebar_breakpoint' => 'lg',     // sidebar-expand-{breakpoint}
    'sidebar_mini' => true,           // .sidebar-mini
    'sidebar_collapse' => false,      // start collapsed
    'sidebar_collapse_auto_size' => false,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'leave',

    /*
    |--------------------------------------------------------------------------
    | Color theme
    |--------------------------------------------------------------------------
    |
    | The sidebar uses data-bs-theme="dark" by default (dark sidebar on a
    | light page, matching the AdminLTE 4 demos). Set to 'light' for a light
    | sidebar.
    |
    | The four *_color keys repaint the chrome without touching SCSS: each one
    | is injected into the layout <head> as a CSS custom-property override
    | (see ColorlibHQ\AdminLte\Support\ThemeColors). Leave a key null to keep
    | the stock AdminLTE colour. `primary_color` also recolours links and the
    | primary button variants. Values must be hex — '#rgb' or '#rrggbb';
    | anything else is ignored. The /demo/theme-generator page previews these
    | live and writes the snippet for you.
    |
    */

    'sidebar_theme' => 'dark',  // 'dark' | 'light'

    'primary_color' => null,    // brand colour: links, .btn-primary, --bs-primary
    'sidebar_color' => null,    // .app-sidebar background
    'navbar_color' => null,     // .app-header background
    'footer_color' => null,     // .app-footer background

    /*
    |--------------------------------------------------------------------------
    | Custom body / element classes
    |--------------------------------------------------------------------------
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => 'fw-light',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'bg-body-secondary shadow',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-expand bg-body',
    'classes_topnav_nav' => 'navbar',
    'classes_topnav_container' => 'container-fluid',

    /*
    |--------------------------------------------------------------------------
    | Color mode toggle
    |--------------------------------------------------------------------------
    |
    | Shows the Light/Dark/Auto dropdown in the topbar (AdminLTE 4 feature).
    |
    */

    'color_mode_toggle' => true,

    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    |
    | The sidebar (and optional top-nav) menu. Each item is an array. Supported
    | keys:
    |
    |   'header'      => 'SECTION LABEL'            // a section header
    |   'text'        => 'Dashboard'               // link label (required for links)
    |   'route'       => 'dashboard'               // named route  -> url
    |   'url'         => 'admin/users'             // raw url (relative or absolute)
    |   'icon'        => 'bi bi-speedometer'       // Bootstrap Icons class
    |   'icon_color'  => 'primary'                 // optional text-{color}
    |   'label'       => 5                         // badge value
    |   'label_color' => 'primary'                 // badge color
    |   'active'      => ['admin/users*']          // url patterns that mark active
    |   'target'      => '_blank'                  // anchor target
    |   'can'         => 'view-users'              // gate/permission to show item
    |   'submenu'     => [ ...child items... ]     // nested items (treeview)
    |
    */

    'menu' => [
        // ---- Sidebar: mirrors the AdminLTE 4 demo sidebar ----
        [
            'text' => 'Dashboard',
            'icon' => 'bi bi-speedometer',
            'text' => 'Dashboard',
            'route' => 'dashboard.index',
            'active' => ['dashboard*'],
            'can' => 'dashboard.view',
        ],
         ['header' => 'ACADEMIC'],
        [
        'text' => 'Departments',
        'route' => 'departments.index',
        'icon' => 'bi bi-building',
        'active' => ['departments*'],
        'can' => 'departments.view',
        ],
        [
            'text' => 'Configuration',
            'icon' => 'bi bi-gear-fill  ',
            'submenu' => [
                ['text' => 'Course Types','route' => 'course-types.index','icon' => 'bi bi-tags','active' => ['configuration/course-types*'],'can' => 'coursetypes.view',],
                ['text' => 'Semester List','route' => 'semesters.index','icon' => 'bi bi-calendar3','active' => ['configuration/semesters*'],'can' => 'semesters.view',],
                ['text' => 'Grade Type','route' => 'grade-types.index','icon' => 'bi bi-award','active' => ['configuration/grade-types*'],'can' => 'gradetypes.view',],
                ['text' => 'Division Type','route' => 'division-types.index','icon' => 'bi bi-diagram-3','active' => ['configuration/division-types*'],'can' => 'divisiontypes.view',],
                ['text' => 'Subject Type','route' => 'subject-types.index','icon' => 'bi bi-bookmarks','active' => ['configuration/subject-types*'],'can' => 'subjecttypes.view',],
            ],
        ],
       [
        'text' => 'Grade',
        'route' => 'grades.index',
        'icon' => 'bi bi-journal-check',
        'active' => ['grades*'],
        'can' => 'grades.view',
        ],
        [
        'text' => 'Division',
        'route' => 'divisions.index',
        'icon' => 'bi bi-diagram-2',
        'active' => ['divisions*'],
        'can' => 'divisions.view',
        ],
        [
        'text' => 'Courses',
        'route' => 'courses.index',
        'icon' => 'bi bi-mortarboard',
        'active' => ['courses*'],
        'can' => 'courses.view',
        ],
        [
        'text' => 'Branches',
        'route' => 'branches.index',
        'icon' => 'bi bi-diagram-3-fill',
        'active' => ['branches*'],
        'can' => 'branches.view',
        ],
       [
        'text' => 'Course Session',
        'icon' => 'bi bi-calendar-check',
        'submenu' => [
        [
            'text' => 'Batch / Session',
            'route' => 'batches.index',
            'icon' => 'bi bi-calendar-range',
            'active' => ['batches*'],
            'can' => 'batches.view',
        ],
        [
            'text' => 'Session Grade',
            'route' => 'session-grades.index',
            'icon' => 'bi bi-award',
            'active' => ['coursesession/session-grades*'],
            'can' => 'sessiongrades.view',
        ],
        ],
        ],
        [
        'text' => 'Semester',
        'icon' => 'bi bi-pie-chart',
        'submenu' => [
        [
            'text' => 'Semester',
            'route' => 'course-semesters.index',
            'icon' => 'bi bi-circle',
            'active' => ['course-semesters*'],
            'can' => 'coursesemesters.view',
        ],
        [
            'text' => 'Semester Exam',
            'route' => 'semester-exams.index',
            'icon' => 'bi bi-circle',
            'active' => ['semester-exams*'],
            'can' => 'semesterexams.view',
        ],
        ],
        ],
        [
        'text' => 'Subject',
        'icon' => 'bi bi-book',
        'submenu' => [
        [
            'text' => 'Subject',
            'route' => 'subjects.index',
            'icon' => 'bi bi-circle',
            'active' => ['subjects*'],
            'can' => 'subjects.view',
        ],
        [
            'text' => 'Subject Order',
            'route' => 'subject-orders.index',
            'icon' => 'bi bi-circle',
            'active' => ['subject-orders*'],
            'can' => 'subjectorders.view',
        ],
        [
            'text' => 'Session Subject',
            'route' => 'session-subjects.index',
            'icon' => 'bi bi-circle',
            'active' => ['session-subjects*'],
            'can' => 'sessionsubjects.view',
        ],
        ],
        ],
        ['header' => 'STUDENT'],    
        [
        'text' => 'Student',
        'icon' => 'bi bi-people-fill',
        'submenu' => [
        [
            'text' => 'Student',
            'route' => 'students.index',
            'icon' => 'bi bi-circle',
            'active' => ['students*'],
            'can' => 'students.view',
        ],
        [
            'text' => 'Student Import Log',
            'route' => 'student-imports.index',
            'icon' => 'bi bi-circle',
            'active' => ['student-imports*'],
            'can' => 'studentimports.view',
        ],
        [
            'text' => 'Student List',
            'route' => 'student-lists.index',
            'icon' => 'bi bi-circle',
            'active' => ['student-lists*'],
            'can' => 'studentlists.view',
        ],
        [
            'text' => 'Optional Subject Student',
            'route' => 'optional-subject-students.index',
            'icon' => 'bi bi-circle',
            'active' => ['optional-subject-students*'],
            'can' => 'optionalsubjectstudents.view',
        ],
        ],
        ],
        ['header' => 'RESULT'],
        [
            'text' => 'Result',
            'route' => 'results.index',
            'icon' => 'bi bi-card-list',
            'active' => ['results*'],
            'can' => 'subjectresults.view',
        ],
        ['header' => 'ADMINISTRATION'],
        [
            'text' => 'Users',
            'url' => 'admin/users',
            'icon' => 'bi bi-people',
            'can' => 'manage-users',
        ],
        [
            'text' => 'Roles',
            'url' => 'admin/roles',
            'icon' => 'bi bi-shield-lock',
            'can' => 'manage-roles',
        ],
        ['header' => 'EXAMPLES'],
        [
            'text' => 'Auth',
            'icon' => 'bi bi-box-arrow-in-right',
            'submenu' => [
                [
                    'text' => 'Version 1',
                    'icon' => 'bi bi-box-arrow-in-right',
                    'submenu' => [
                        ['text' => 'Login', 'url' => 'login', 'icon' => 'bi bi-circle'],
                        ['text' => 'Register', 'url' => 'register', 'icon' => 'bi bi-circle'],
                    ],
                ],
                [
                    'text' => 'Version 2',
                    'icon' => 'bi bi-box-arrow-in-right',
                    'submenu' => [
                        ['text' => 'Login', 'url' => 'demo/auth/login-v2', 'icon' => 'bi bi-circle'],
                        ['text' => 'Register', 'url' => 'demo/auth/register-v2', 'icon' => 'bi bi-circle'],
                        ['text' => 'Lockscreen', 'url' => 'demo/auth/lockscreen', 'icon' => 'bi bi-circle'],
                    ],
                ],
            ],
        ],

        ['header' => 'MULTI LEVEL EXAMPLE'],
        ['text' => 'Level 1', 'url' => '#', 'icon' => 'bi bi-circle-fill'],
        [
            'text' => 'Level 1',
            'icon' => 'bi bi-circle-fill',
            'submenu' => [
                ['text' => 'Level 2', 'url' => '#', 'icon' => 'bi bi-circle'],
                [
                    'text' => 'Level 2',
                    'icon' => 'bi bi-circle',
                    'submenu' => [
                        ['text' => 'Level 3', 'url' => '#', 'icon' => 'bi bi-record-circle-fill'],
                        ['text' => 'Level 3', 'url' => '#', 'icon' => 'bi bi-record-circle-fill'],
                        ['text' => 'Level 3', 'url' => '#', 'icon' => 'bi bi-record-circle-fill'],
                    ],
                ],
                ['text' => 'Level 2', 'url' => '#', 'icon' => 'bi bi-circle'],
            ],
        ],
        ['text' => 'Level 1', 'url' => '#', 'icon' => 'bi bi-circle-fill'],

        ['header' => 'LABELS'],
        ['text' => 'Important', 'url' => '#', 'icon' => 'bi bi-circle', 'icon_color' => 'danger'],
        ['text' => 'Warning', 'url' => '#', 'icon' => 'bi bi-circle', 'icon_color' => 'warning'],
        ['text' => 'Informational', 'url' => '#', 'icon' => 'bi bi-circle', 'icon_color' => 'info'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu filters
    |--------------------------------------------------------------------------
    |
    | Filters transform each menu item before rendering. Add your own classes
    | here (must implement ColorlibHQ\AdminLte\Menu\Filters\FilterInterface).
    | The defaults handle gates, active state, hrefs, and search items.
    |
    */

    'filters' => [
        GateFilter::class,
        HrefFilter::class,
        ActiveFilter::class,
        SearchFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    |
    | Optional JavaScript libraries integrated with AdminLTE 4. Disable plugins
    | you don't use to avoid loading unnecessary assets.
    |
    */

    'plugins' => [
        'flatpickr' => [
            'enabled' => false,
            'css' => 'vendor/flatpickr/flatpickr.min.css',
            'js' => 'vendor/flatpickr/flatpickr.min.js',
        ],
        'tom_select' => [
            'enabled' => false,
            'css' => 'vendor/tom-select/tom-select.bootstrap5.min.css',
            'js' => 'vendor/tom-select/tom-select.complete.min.js',
        ],
        'tabulator' => [
            'enabled' => false,
            'css' => 'vendor/tabulator-tables/tabulator.min.css',
            'js' => 'vendor/tabulator-tables/tabulator.min.js',
        ],
        'quill' => [
            'enabled' => false,
            'css' => 'vendor/quill/quill.snow.css',
            'js' => 'vendor/quill/quill.min.js',
        ],
        'chartjs' => [
            'enabled' => false,
            // Chart.js (MIT), then the AdminLTE preset: theme, dark mode,
            // <x-adminlte-chart> rendering and the AdminLteCharts helpers.
            'js' => [
                'vendor/chartjs/chart.umd.min.js',
                'vendor/adminlte/js/charts.js',
            ],
        ],
        'jsvectormap' => [
            'enabled' => false,
            'css' => 'vendor/jsvectormap/jsvectormap.min.css',
            // The library first, then the world map data (registers the 'world' map).
            'js' => [
                'vendor/jsvectormap/jsvectormap.min.js',
                'vendor/jsvectormap/maps/world.js',
            ],
        ],
        'fullcalendar' => [
            'enabled' => false,
            'css' => 'vendor/fullcalendar/index.global.min.css',
            'js' => 'vendor/fullcalendar/index.global.min.js',
        ],
        'sortablejs' => [
            'enabled' => false,
            'js' => 'vendor/sortablejs/sortablejs.min.js',
        ],
    ],

];
