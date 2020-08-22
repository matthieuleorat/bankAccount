<?php

namespace App\Controller\Admin;

use App\Entity\Budget;
use App\Entity\Source;
use App\Entity\Statement;
use App\Entity\Transaction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Mon Budget');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setDateFormat('dd/MM/yyyy')
            ->setDateTimeFormat('dd/MM/yyyy HH:mm:ss')
            ->setTimeFormat('HH:mm');
    }

    public function configureMenuItems(): iterable
    {
        $submenu1 = [
            MenuItem::linkToCrud('transaction.menu.all', '', Transaction::class),
        ];

        $submenu2 = [
            MenuItem::linkToCrud('statement.all.label', '', Statement::class),
            MenuItem::linktoRoute('statement.import.label', '', 'import_new_statement'),
        ];

        yield MenuItem::linkToCrud('source.label', 'fas fa-folder-open', Source::class);
        yield MenuItem::subMenu('transaction.menu.label', 'fas fa-folder-open')->setSubItems($submenu1);
        yield MenuItem::subMenu('statement.label', 'fas fa-folder-open')->setSubItems($submenu2);
        yield MenuItem::linkToCrud('budget.label', 'fas fa-folder-open', Budget::class);
    }
}
