@php
    $menuSections = [
        [
            
            'label' => 'Main Menu',
            'groups' => [
                [
                    'title' => 'Dashboard',
                    'icon' => 'bi-speedometer2',
                    'children' => [
                        /* [
                            'label' => 'Overview',
                            'route' => 'dashboard',
                            'active' => request()->routeIs('dashboard') || request()->routeIs('admin.index'),
                        ], */
                        [
                            'label'  => 'Analytics',
                            'route'  => 'analytics.index',
                            'active' => request()->routeIs('analytics.*'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'label' => 'Application',
            'groups' => [
                [
                    'title' => 'Action Matrix',
                    'icon' => 'bi-grid-3x3-gap',
                    'children' => [
                        [
                            'label' => 'Follow-up List',
                            'route' => 'action-matrix.index',
                            'active' => request()->routeIs('action-matrix.index'),
                        ],
                        [
                            'label'  => 'New Follow-up',
                            'route'  => 'action-matrix.create',
                            'active' => request()->routeIs('action-matrix.create'),
                            'roles'  => ['PKSF_CO', 'Admin', 'Super_Admin'],
                        ],
                    ],
                ],
                [
                    'title'     => 'Reports',
                    'icon'      => 'bi-file-earmark-pdf',
                    'pksf_only' => true,   // Reports menu visible to PKSF users only
                    'children'  => [
                        [
                            'label'  => 'Sample Reports',
                            'route'  => 'reports.report1',
                            'active' => request()->routeIs('reports.report1'),
                            'target' => '_blank',
                        ],
                        [
                            'label'  => 'Action Matrix Reports',
                            'route'  => 'reports.action-matrix-report',
                            'active' => request()->routeIs('reports.action-matrix-report*'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'label' => 'Settings',
            'groups' => [
                [
                    'title' => 'User Management',
                    'icon' => 'bi-people',
                    'roles' => ['Super Admin', 'Admin'],
                    'children' => [
                        [
                            'label' => 'Users',
                            'route' => 'users.index',
                            'active' => request()->routeIs('users.*'),
                        ],
                        [
                            'label' => 'Roles & Permissions',
                            'route' => 'roles.index',
                            'active' => request()->routeIs('roles.*'),
                        ],
                        [
                            'label' => 'Assign Roles',
                            'route' => 'user-roles.index',
                            'active' => request()->routeIs('user-roles.*'),
                        ],
                        [
                            'label' => 'Profile Settings',
                            'route' => 'profile.edit',
                            'active' => request()->routeIs('profile.*'),
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp

<style>
    .sidebar-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--sl-muted);
        margin-bottom: 0.75rem;
        padding-left: 0.5rem;
    }

    .nav-link-item {
        display: flex;
        align-items: center;
        padding: 0.6rem 0.75rem;
        border-radius: 8px;
        color: var(--sl-text);
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-bottom: 0.25rem;
    }

    .nav-link-item:hover {
        background-color: var(--sl-primary-soft);
        color: var(--sl-primary);
    }

    .nav-link-item.active {
        background-color: var(--sl-primary);
        color: white;
    }

    .nav-link-item i {
        font-size: 1.1rem;
        margin-right: 0.75rem;
        opacity: 0.8;
    }

    .sub-nav-link {
        display: block;
        padding: 0.45rem 0.75rem 0.45rem 2.6rem;
        font-size: 0.825rem;
        color: var(--sl-muted);
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .sub-nav-link:hover {
        color: var(--sl-primary);
        padding-left: 2.8rem;
    }

    .sub-nav-link.active {
        color: var(--sl-primary);
        font-weight: 600;
    }

    .rotate-icon {
        transition: transform 0.2s;
    }

    .btn-toggle[aria-expanded="true"] .rotate-icon {
        transform: rotate(90deg);
    }
</style>

<div class="sidebar-nav">
    @foreach ($menuSections as $sectionIndex => $section)
        <div class="mb-1">
            {{-- <div class="sidebar-label">{{ $section['label'] }}</div> --}}
            
            @foreach ($section['groups'] as $groupIndex => $group)
                @php
                    // Role-based group visibility
                    if (isset($group['roles']) && !auth()->user()->hasAnyRole($group['roles'])) {
                        continue;
                    }
                    // pksf_only groups: visible to PKSF users + Admin / Super_Admin; hidden from PO
                    if (!empty($group['pksf_only'])
                        && !auth()->user()->isPksf()
                        && !auth()->user()->hasAnyRole(['Admin', 'Super_Admin'])) {
                        continue;
                    }

                    $isOpen = collect($group['children'])->contains(fn ($child) => $child['active']);
                    $collapseId = 'collapse-' . $sectionIndex . '-' . $groupIndex;
                @endphp
                
                <div class="mb-1">
                    <a class="nav-link-item btn-toggle d-flex justify-content-between align-items-center" 
                       data-bs-toggle="collapse" 
                       href="#{{ $collapseId }}" 
                       role="button" 
                       aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center">
                            <i class="bi {{ $group['icon'] }}"></i>
                            <span>{{ $group['title'] }}</span>
                        </div>
                        <i class="bi bi-chevron-right small rotate-icon"></i>
                    </a>
                    
                    <div class="collapse {{ $isOpen ? 'show' : '' }}" id="{{ $collapseId }}">
                        <div class="d-grid gap-1 mt-1">
                            @foreach ($group['children'] as $child)
                                @php
                                    // pksf_only child items: visible to PKSF + Admin / Super_Admin; hidden from PO
                                    if (!empty($child['pksf_only'])
                                        && !auth()->user()->isPksf()
                                        && !auth()->user()->hasAnyRole(['Admin', 'Super_Admin'])) continue;
                                    // roles child items: visible only to users with one of the listed roles
                                    if (!empty($child['roles']) && !auth()->user()->hasAnyRole($child['roles'])) continue;
                                    $href = isset($child['route']) ? route($child['route']) : ($child['url'] ?? '#');
                                @endphp
                                <a href="{{ $href }}"
                                   class="sub-nav-link {{ $child['active'] ? 'active' : '' }}"
                                   @if(!empty($child['target'])) target="{{ $child['target'] }}" @endif>
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
