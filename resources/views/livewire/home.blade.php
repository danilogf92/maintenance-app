<div class="page-container">
    <div class="page-content">
        <header class="page-heading">
            <div>
                <p class="page-label">Inicio / Panel principal</p>
                <h1 class="page-title">Bienvenido, {{ auth()->user()->name }}</h1>
                <p class="page-help">Tu espacio de trabajo y configuración.</p>
            </div>
            <a class="page-button" href="{{ route('profile.edit') }}">Editar perfil <x-ui.icon name="arrow-right" /></a>
        </header>
        <div class="page-stats">
            <section class="page-card page-stat"><span class="page-icon"><x-ui.icon name="building" /></span>
                <div>
                    <p class="page-help">Institución</p>
                    <strong>{{ auth()->user()->institution?->name ?? 'Sin institución' }}</strong>
                </div>
            </section>
            <section class="page-card page-stat"><span class="page-icon"><x-ui.icon name="users" /></span>
                <div>
                    <p class="page-help">Roles</p>
                    <strong>{{ auth()->user()->getRoleNames()->join(', ') ?: 'Sin roles asignados' }}</strong>
                </div>
            </section>
            <section class="page-card page-stat"><span class="page-icon"><x-ui.icon name="user" /></span>
                <div>
                    <p class="page-help">Cuenta</p><span
                        class="page-state {{ auth()->user()->is_active ? '' : 'page-state-inactive' }}">{{ auth()->user()->is_active ? 'Activa' : 'Inactiva' }}</span>
                </div>
            </section>
        </div>
        <h2 class="page-section-title">Accesos rápidos</h2>
        <div class="page-shortcuts">
            @can('institutions.manage')
                <a class="page-link" href="{{ route('operations.institutions') }}"><span class="page-icon"><x-ui.icon
                            name="building" /></span><span><strong>Instituciones</strong><small class="page-help">Centros,
                            contactos y datos generales</small></span><span class="page-link-arrow"><x-ui.icon
                            name="arrow-right" /></span></a>
            @endcan
            @can('users.manage')
                <a class="page-link" href="{{ route('operations.users') }}"><span class="page-icon"><x-ui.icon
                            name="users" /></span><span><strong>Usuarios</strong><small class="page-help">Personas, roles
                            y acceso</small></span><span class="page-link-arrow"><x-ui.icon name="arrow-right" /></span></a>
            @endcan
            <a class="page-link" href="{{ route('profile.edit') }}"><span class="page-icon"><x-ui.icon
                        name="user" /></span><span><strong>Mi perfil</strong><small class="page-help">Datos
                        personales y apariencia</small></span><span class="page-link-arrow"><x-ui.icon
                        name="arrow-right" /></span></a>
            @if (auth()->user()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
                <a class="page-link" href="{{ url('/admin') }}"><span class="page-icon"><x-ui.icon
                            name="settings" /></span><span><strong>Administración</strong><small
                            class="page-help">Configuración de la plataforma</small></span><span
                        class="page-link-arrow"><x-ui.icon name="arrow-right" /></span></a>
            @endif
        </div>
    </div>
</div>
