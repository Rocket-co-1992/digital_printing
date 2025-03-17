<?php
class EmailService {
    private $twig;
    
    public function __construct($twig) {
        $this->twig = $twig;
    }
    
    public function sendMaintenanceAlert($machine, $users) {
        $message = $this->twig->render('emails/maintenance_alert.twig', [
            'machine' => $machine,
            'maintenanceUrl' => SITE_URL . '/machines/maintenance/' . $machine['id']
        ]);
        
        foreach ($users as $user) {
            $headers = [
                'From: ' . EMAIL_FROM,
                'Content-Type: text/html; charset=UTF-8'
            ];
            
            mail(
                $user['email'],
                'Manutenção Necessária - ' . $machine['name'],
                $message,
                implode("\r\n", $headers)
            );
        }
    }

    public function sendJobScheduledNotification($job, $client) {
        $message = $this->twig->render('emails/job_scheduled.twig', [
            'job' => $job,
            'client' => $client,
            'trackingUrl' => SITE_URL . '/jobs/track/' . $job['id']
        ]);
        
        $headers = [
            'From: ' . EMAIL_FROM,
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        mail(
            $client['email'],
            'Trabalho Agendado - ' . $job['title'],
            $message,
            implode("\r\n", $headers)
        );
    }

    public function sendJobStatusUpdate($job, $client, $status) {
        $message = $this->twig->render('emails/job_status.twig', [
            'job' => $job,
            'client' => $client,
            'status' => $status,
            'trackingUrl' => SITE_URL . '/jobs/track/' . $job['id']
        ]);
        
        $headers = [
            'From: ' . EMAIL_FROM,
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        mail(
            $client['email'],
            'Atualização do Trabalho - ' . $job['title'],
            $message,
            implode("\r\n", $headers)
        );
    }

    public function sendCostEstimate($job, $client, $costs) {
        $message = $this->twig->render('emails/cost_estimate.twig', [
            'job' => $job,
            'costs' => $costs,
            'client' => $client,
            'approvalUrl' => SITE_URL . '/jobs/approve/' . $job['id']
        ]);
        
        $headers = [
            'From: ' . EMAIL_FROM,
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        mail(
            $client['email'],
            'Orçamento - ' . $job['title'],
            $message,
            implode("\r\n", $headers)
        );
    }

    public function sendWorkloadAlert($machine, $stats) {
        $message = $this->twig->render('emails/workload_alert.twig', [
            'machine' => $machine,
            'stats' => $stats,
            'recommendations' => $this->generateRecommendations($stats)
        ]);
        
        $this->sendToManagers($message, 'Alta Carga de Trabalho - ' . $machine['name']);
    }

    private function generateRecommendations($stats) {
        $recommendations = [];
        if ($stats['usage_percentage'] > 85) {
            $recommendations[] = 'Considerar redistribuição de trabalhos';
        }
        if ($stats['std_duration'] > 120) {
            $recommendations[] = 'Alta variação no tempo de trabalho - verificar eficiência';
        }
        return $recommendations;
    }
}
