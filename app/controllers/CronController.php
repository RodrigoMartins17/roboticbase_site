<?php
require_once __DIR__ . '/../core/Controller.php';

// Controller "técnico" para tarefas agendadas (cron) no alojamento online.
// O Vercel chama /cron/avisos automaticamente todos os dias (ver vercel.json)
// e este controller executa o mesmo script que uso no XAMPP com o Task Scheduler.
class CronController extends Controller
{
    public function avisos()
    {
        // O script já valida o acesso por chave; aqui passo-a por ele, porque
        // este endpoint só é chamado pelo agendador do próprio alojamento.
        $_GET['key'] = 'roboticaxl';
        require __DIR__ . '/../../public/cron_avisos.php';
    }
}
