<div class="offcanvas-lg offcanvas-start bg-dark text-light border-end border-secondary d-flex flex-column vh-100" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel" style="width: 280px; min-width: 280px;">

    <div class="offcanvas-header border-bottom border-secondary d-flex justify-content-center position-relative" style="height: 65px;">

        <a href="<?php echo BASE_URL; ?>dashboard.php" class="text-decoration-none">
            <img src="<?php echo BASE_URL; ?>assets/img/logo-marcelus.svg" alt="Marcelus Car" style="max-height: 40px; width: auto;" onerror="this.outerHTML='<h4 class=\'m-0 fw-bold text-light\'><i class=\'bi bi-car-front-fill text-primary me-2\'></i>Marcelus Car</h4>'">
        </a>

        <button type="button" class="btn-close btn-close-white d-lg-none position-absolute end-0 me-3" data-bs-dismiss="offcanvas" aria-label="Close" data-bs-target="#sidebarMenu"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0 flex-grow-1 overflow-y-auto">
        <ul class="nav nav-pills flex-column mb-auto p-3">

            <li class="nav-item mb-2">
                <a href="<?php echo BASE_URL; ?>dashboard.php" class="nav-link text-light <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active bg-primary' : ''; ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?php echo BASE_URL; ?>leads.php" class="nav-link text-light <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active bg-primary shadow-sm' : ''; ?>">
                    <i class="bi bi-funnel-fill me-2"></i> Funil de Leads
                </a>
            </li>

            <!-- 2. A Nova Funcionalidade Sugerida: Agenda de Retornos -->
            <li class="nav-item mb-2">
                <a href="<?php echo BASE_URL; ?>agenda.php" class="nav-link text-light <?php echo basename($_SERVER['PHP_SELF']) == 'agenda.php' ? 'active bg-primary' : ''; ?>">
                    <i class="bi bi-calendar-check-fill me-2"></i> Agenda de Retornos
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?php echo BASE_URL; ?>veiculos.php" class="nav-link text-light <?php echo basename($_SERVER['PHP_SELF']) == 'veiculos.php' ? 'active bg-primary' : ''; ?>">
                    <i class="bi bi-car-front me-2"></i> Veículos
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="#" class="nav-link text-light">
                    <i class="bi bi-gear-fill me-2"></i> Configurações
                </a>
            </li>

        </ul>
    </div>
</div>